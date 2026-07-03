-- =====================================================================
--  DIGITAL SMILE - Base de données (schema.sql)
--  Moteur : MySQL / MariaDB (XAMPP)
--  Encodage : utf8mb4 (support complet FR / AR / EN + emojis)
--  Architecture : 15 tables, conçue simple mais extensible.
--
--  COMMENT UTILISER :
--  1. Ouvrez phpMyAdmin (http://localhost/phpmyadmin)
--  2. Onglet "Importer" -> choisissez ce fichier -> Exécuter
--     (OU collez tout ce contenu dans l'onglet "SQL")
-- =====================================================================

-- On crée la base si elle n'existe pas, en utf8mb4 pour l'arabe.
CREATE DATABASE IF NOT EXISTS digital_smile
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE digital_smile;

-- Pour pouvoir ré-importer proprement, on supprime dans l'ordre inverse
-- des dépendances (les tables "enfants" d'abord).
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS invoices;
DROP TABLE IF EXISTS files;
DROP TABLE IF EXISTS tasks;
DROP TABLE IF EXISTS projects;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS service_categories;
DROP TABLE IF EXISTS suppliers;
DROP TABLE IF EXISTS employees;
DROP TABLE IF EXISTS clients;
DROP TABLE IF EXISTS departments;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;
SET FOREIGN_KEY_CHECKS = 1;


-- =====================================================================
--  GROUPE 1 : IDENTITÉ & SÉCURITÉ
-- =====================================================================

-- --- Table : roles -------------------------------------------------
-- Les 3 rôles du système. On sépare le rôle des utilisateurs (RBAC)
-- pour ne PAS coder "if email == admin" en dur nulle part.
CREATE TABLE roles (
    id          INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name        VARCHAR(20)  NOT NULL UNIQUE,     -- 'admin' | 'employee' | 'client'
    label_fr    VARCHAR(50)  NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --- Table : users -------------------------------------------------
-- UNE seule table pour tout le monde (admin, employé, client).
-- Le rôle décide de ce que la personne peut voir. C'est le principe DRY.
CREATE TABLE users (
    id             INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    role_id        INT UNSIGNED NOT NULL,
    full_name      VARCHAR(120) NOT NULL,
    email          VARCHAR(150) NOT NULL UNIQUE,
    password_hash  VARCHAR(255) NOT NULL,          -- JAMAIS le mot de passe en clair
    phone          VARCHAR(30)  DEFAULT NULL,
    lang           CHAR(2)      NOT NULL DEFAULT 'fr',  -- 'fr' | 'ar' | 'en'
    is_active      TINYINT(1)   NOT NULL DEFAULT 1,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at     TIMESTAMP NULL DEFAULT NULL,     -- Soft delete : on ne supprime jamais vraiment
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id),
    INDEX idx_users_email (email),
    INDEX idx_users_role (role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================================
--  GROUPE 2 : ORGANISATION INTERNE
-- =====================================================================

-- --- Table : departments -------------------------------------------
-- Les 12 départements de l'agence. Sert au routage automatique :
-- chaque service pointe vers un département responsable.
CREATE TABLE departments (
    id          INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name        VARCHAR(80) NOT NULL UNIQUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --- Table : clients -----------------------------------------------
-- Détails propres au client, liés à un user (rôle = client).
CREATE TABLE clients (
    id          INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id     INT UNSIGNED NOT NULL UNIQUE,
    company     VARCHAR(150) DEFAULT NULL,
    address     VARCHAR(255) DEFAULT NULL,
    city        VARCHAR(80)  DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_clients_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --- Table : employees ---------------------------------------------
-- Détails propres à l'employé, liés à un user (rôle = employee)
-- et à un département.
CREATE TABLE employees (
    id             INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id        INT UNSIGNED NOT NULL UNIQUE,
    department_id  INT UNSIGNED NOT NULL,
    specialty      VARCHAR(100) DEFAULT NULL,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_employees_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_employees_dept FOREIGN KEY (department_id) REFERENCES departments(id),
    INDEX idx_employees_dept (department_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --- Table : suppliers ---------------------------------------------
-- Fournisseurs (imprimeries, hébergeurs, etc.) tirés de vos fichiers.
CREATE TABLE suppliers (
    id          INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name        VARCHAR(150) NOT NULL,
    service     VARCHAR(150) DEFAULT NULL,
    phone       VARCHAR(30)  DEFAULT NULL,
    email       VARCHAR(150) DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at  TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================================
--  GROUPE 3 : CATALOGUE DE SERVICES
-- =====================================================================

-- --- Table : service_categories ------------------------------------
CREATE TABLE service_categories (
    id          INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name        VARCHAR(80) NOT NULL UNIQUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --- Table : services ----------------------------------------------
-- Chaque service appartient à une catégorie ET à un département.
-- Le department_id est la CLÉ du routage automatique.
CREATE TABLE services (
    id             INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    category_id    INT UNSIGNED NOT NULL,
    department_id  INT UNSIGNED NOT NULL,
    name           VARCHAR(150) NOT NULL,
    description    TEXT DEFAULT NULL,
    base_price     DECIMAL(12,2) NOT NULL DEFAULT 0.00,   -- prix en DZD
    is_active      TINYINT(1) NOT NULL DEFAULT 1,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_services_cat  FOREIGN KEY (category_id)   REFERENCES service_categories(id),
    CONSTRAINT fk_services_dept FOREIGN KEY (department_id) REFERENCES departments(id),
    INDEX idx_services_dept (department_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================================
--  GROUPE 4 : CYCLE DE VIE DE LA COMMANDE (le cœur du workflow)
-- =====================================================================

-- --- Table : orders ------------------------------------------------
-- La demande initiale du client. 'code' = ID lisible auto (DS-2026-0001).
-- 'status' pilote la mini machine à états simplifiée.
CREATE TABLE orders (
    id           INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    code         VARCHAR(20) NOT NULL UNIQUE,        -- ex : DS-2026-0001
    client_id    INT UNSIGNED NOT NULL,
    service_id   INT UNSIGNED NOT NULL,
    project_name VARCHAR(150) NOT NULL,
    brand_name   VARCHAR(150) DEFAULT NULL,
    description  TEXT DEFAULT NULL,
    colors       VARCHAR(255) DEFAULT NULL,
    style        VARCHAR(150) DEFAULT NULL,
    deadline     DATE DEFAULT NULL,
    budget       DECIMAL(12,2) DEFAULT NULL,
    priority     ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
    -- statut simplifié : le client demande -> admin valide -> travail -> livré
    status       ENUM('pending','approved','rejected','in_progress','delivered','completed','cancelled')
                 NOT NULL DEFAULT 'pending',
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_client  FOREIGN KEY (client_id)  REFERENCES clients(id),
    CONSTRAINT fk_orders_service FOREIGN KEY (service_id) REFERENCES services(id),
    INDEX idx_orders_status (status),
    INDEX idx_orders_client (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --- Table : projects ----------------------------------------------
-- Quand l'admin approuve une commande, elle devient un projet
-- assigné à un employé, avec un % de progression.
CREATE TABLE projects (
    id           INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    order_id     INT UNSIGNED NOT NULL UNIQUE,
    employee_id  INT UNSIGNED DEFAULT NULL,          -- l'employé assigné
    progress     TINYINT UNSIGNED NOT NULL DEFAULT 0, -- 0 à 100
    status       ENUM('assigned','in_progress','review','done') NOT NULL DEFAULT 'assigned',
    started_at   TIMESTAMP NULL DEFAULT NULL,
    finished_at  TIMESTAMP NULL DEFAULT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_projects_order    FOREIGN KEY (order_id)    REFERENCES orders(id),
    CONSTRAINT fk_projects_employee FOREIGN KEY (employee_id) REFERENCES employees(id),
    INDEX idx_projects_employee (employee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --- Table : tasks -------------------------------------------------
-- Un projet peut être découpé en petites tâches (optionnel mais utile).
CREATE TABLE tasks (
    id           INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    project_id   INT UNSIGNED NOT NULL,
    title        VARCHAR(150) NOT NULL,
    is_done      TINYINT(1) NOT NULL DEFAULT 0,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tasks_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    INDEX idx_tasks_project (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================================
--  GROUPE 5 : FICHIERS
-- =====================================================================

-- --- Table : files -------------------------------------------------
-- Sert dans les 2 sens : le client dépose ses références,
-- l'employé dépose le livrable final. 'kind' distingue les deux.
CREATE TABLE files (
    id           INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    project_id   INT UNSIGNED NOT NULL,
    uploaded_by  INT UNSIGNED NOT NULL,               -- users.id
    kind         ENUM('reference','deliverable') NOT NULL DEFAULT 'reference',
    original_name VARCHAR(255) NOT NULL,
    stored_path  VARCHAR(255) NOT NULL,               -- chemin dans /public/uploads
    size_bytes   INT UNSIGNED DEFAULT 0,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_files_project FOREIGN KEY (project_id)  REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_files_user    FOREIGN KEY (uploaded_by) REFERENCES users(id),
    INDEX idx_files_project (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================================
--  GROUPE 6 : FINANCES
-- =====================================================================

-- --- Table : invoices ----------------------------------------------
CREATE TABLE invoices (
    id           INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    code         VARCHAR(20) NOT NULL UNIQUE,         -- ex : FAC-2026-0001
    order_id     INT UNSIGNED NOT NULL,
    amount_ht    DECIMAL(12,2) NOT NULL DEFAULT 0.00, -- hors taxe
    tax_rate     DECIMAL(5,2)  NOT NULL DEFAULT 19.00,-- TVA % (Algérie)
    amount_ttc   DECIMAL(12,2) NOT NULL DEFAULT 0.00, -- toutes taxes comprises
    status       ENUM('unpaid','partial','paid') NOT NULL DEFAULT 'unpaid',
    issued_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_invoices_order FOREIGN KEY (order_id) REFERENCES orders(id),
    INDEX idx_invoices_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --- Table : payments ----------------------------------------------
CREATE TABLE payments (
    id           INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    invoice_id   INT UNSIGNED NOT NULL,
    amount       DECIMAL(12,2) NOT NULL,
    method       ENUM('cash','transfer','cheque','other') NOT NULL DEFAULT 'transfer',
    paid_at      DATE NOT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payments_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id),
    INDEX idx_payments_invoice (invoice_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================================
--  GROUPE 7 : COMMUNICATION & TRAÇABILITÉ
-- =====================================================================

-- --- Table : messages ----------------------------------------------
CREATE TABLE messages (
    id           INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    order_id     INT UNSIGNED DEFAULT NULL,
    sender_id    INT UNSIGNED NOT NULL,               -- users.id
    body         TEXT NOT NULL,
    is_read      TINYINT(1) NOT NULL DEFAULT 0,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_messages_sender FOREIGN KEY (sender_id) REFERENCES users(id),
    INDEX idx_messages_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --- Table : notifications -----------------------------------------
CREATE TABLE notifications (
    id           INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id      INT UNSIGNED NOT NULL,               -- destinataire
    title        VARCHAR(150) NOT NULL,
    body         VARCHAR(255) DEFAULT NULL,
    link         VARCHAR(255) DEFAULT NULL,           -- vers quelle page cliquer
    is_read      TINYINT(1) NOT NULL DEFAULT 0,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_notifications_user (user_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --- Table : activity_logs -----------------------------------------
-- Journal d'audit. Tourne en arrière-plan : QUI a fait QUOI et QUAND.
-- Indispensable pour la sécurité et la traçabilité.
CREATE TABLE activity_logs (
    id           BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id      INT UNSIGNED DEFAULT NULL,           -- qui (NULL si système)
    action       VARCHAR(100) NOT NULL,               -- ex : 'order.created'
    entity_type  VARCHAR(50)  DEFAULT NULL,           -- ex : 'order'
    entity_id    INT UNSIGNED DEFAULT NULL,           -- ex : 42
    details      VARCHAR(255) DEFAULT NULL,
    ip_address   VARCHAR(45)  DEFAULT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_logs_user (user_id),
    INDEX idx_logs_entity (entity_type, entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================================
--  DONNÉES INITIALES (seed) — tirées de vos fichiers réels
-- =====================================================================

-- Les 3 rôles
INSERT INTO roles (name, label_fr) VALUES
    ('admin',    'Administrateur'),
    ('employee', 'Employé'),
    ('client',   'Client');

-- Les 12 départements (validés par vous)
INSERT INTO departments (name) VALUES
    ('Logo Designer'),
    ('Brand Identity Designer'),
    ('UI/UX Designer'),
    ('Web Developer'),
    ('Mobile Developer'),
    ('Graphic Designer'),
    ('Content Writer'),
    ('Marketing Team'),
    ('Customer Support'),
    ('Accountant'),
    ('Project Manager'),
    ('Audiovisual Production');

-- Catégories de services (d'après services.docx)
INSERT INTO service_categories (name) VALUES
    ('Conception'),
    ('Impression'),
    ('Audiovisuel'),
    ('Web'),
    ('Marketing'),
    ('QR Codes'),
    ('Formation');

-- Le compte administrateur N'EST PAS créé ici.
-- Pourquoi ? Un mot de passe doit être haché par PHP (password_hash),
-- jamais écrit à la main dans du SQL.
-- => Ouvrez http://localhost/digital-smile/public/install.php UNE FOIS
--    pour créer le compte admin de façon sécurisée, puis supprimez ce fichier.
