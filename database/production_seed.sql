-- =====================================================================
--  DIGITAL SMILE — production_seed.sql
--  Base PROPRE pour la MISE EN LIGNE (aucune donnée de test).
--  Moteur : MySQL / MariaDB.   Encodage : utf8mb4 (FR / AR / EN).
--
--  CE FICHIER CONTIENT :
--    • Toutes les tables, avec les migrations DÉJÀ intégrées :
--        - invoices.tva_rate       (TVA optionnelle)
--        - orders.invoiced         (drapeau « facturée »)
--        - table invoice_items     (factures groupées)
--        - employees.photo / experience_years / bio (profil employé)
--    • Les données de RÉFÉRENCE dont l'appli a besoin pour fonctionner
--        (rôles, départements, catégories, services).
--    • UN SEUL compte administrateur (à mot de passe temporaire).
--  IL NE CONTIENT AUCUNE fausse commande / facture / employé / client.
--
--  IMPORT (phpMyAdmin de l'hébergeur) :
--    1. Créez d'abord une base MySQL vide dans le panneau de l'hébergeur.
--    2. Ouvrez phpMyAdmin → SÉLECTIONNEZ cette base (colonne de gauche).
--    3. Onglet « Importer » → choisissez ce fichier → « Exécuter ».
--    (Ce fichier ne crée PAS la base et ne SUPPRIME aucune table : il
--     s'importe dans la base vide que vous avez sélectionnée.)
--
--  ⚠️ COMPTE ADMIN PAR DÉFAUT :
--       email        : admin@digitalsmile.dz
--       mot de passe : DigitalSmile2026!
--     → Connectez-vous PUIS changez-le aussitôt via « Sécurité »
--       (/compte/mot-de-passe). Ce mot de passe temporaire est public
--       (il est dans ce fichier) : il DOIT être changé en premier.
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;


-- =====================================================================
--  GROUPE 1 : IDENTITÉ & SÉCURITÉ
-- =====================================================================

CREATE TABLE IF NOT EXISTS roles (
    id          INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name        VARCHAR(20)  NOT NULL UNIQUE,     -- 'admin' | 'employee' | 'client'
    label_fr    VARCHAR(50)  NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
    id             INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    role_id        INT UNSIGNED NOT NULL,
    full_name      VARCHAR(120) NOT NULL,
    email          VARCHAR(150) NOT NULL UNIQUE,
    password_hash  VARCHAR(255) NOT NULL,          -- JAMAIS en clair (BCRYPT)
    phone          VARCHAR(30)  DEFAULT NULL,
    lang           CHAR(2)      NOT NULL DEFAULT 'fr',
    is_active      TINYINT(1)   NOT NULL DEFAULT 1,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at     TIMESTAMP NULL DEFAULT NULL,     -- soft delete
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id),
    INDEX idx_users_email (email),
    INDEX idx_users_role (role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================================
--  GROUPE 2 : ORGANISATION INTERNE
-- =====================================================================

CREATE TABLE IF NOT EXISTS departments (
    id          INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name        VARCHAR(80) NOT NULL UNIQUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS clients (
    id          INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id     INT UNSIGNED NOT NULL UNIQUE,
    company     VARCHAR(150) DEFAULT NULL,
    address     VARCHAR(255) DEFAULT NULL,
    city        VARCHAR(80)  DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_clients_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- employees : colonnes de profil (photo / experience_years / bio) intégrées.
CREATE TABLE IF NOT EXISTS employees (
    id             INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id        INT UNSIGNED NOT NULL UNIQUE,
    department_id  INT UNSIGNED NOT NULL,
    specialty      VARCHAR(100) DEFAULT NULL,
    photo          VARCHAR(255) DEFAULT NULL,
    experience_years TINYINT    DEFAULT NULL,
    bio            VARCHAR(500) DEFAULT NULL,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_employees_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_employees_dept FOREIGN KEY (department_id) REFERENCES departments(id),
    INDEX idx_employees_dept (department_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS suppliers (
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

CREATE TABLE IF NOT EXISTS service_categories (
    id          INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name        VARCHAR(80) NOT NULL UNIQUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS services (
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
--  GROUPE 4 : CYCLE DE VIE DE LA COMMANDE
-- =====================================================================

-- orders : drapeau `invoiced` (facturée) intégré.
CREATE TABLE IF NOT EXISTS orders (
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
    status       ENUM('pending','approved','rejected','in_progress','delivered','completed','cancelled')
                 NOT NULL DEFAULT 'pending',
    invoiced     TINYINT(1) NOT NULL DEFAULT 0,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_client  FOREIGN KEY (client_id)  REFERENCES clients(id),
    CONSTRAINT fk_orders_service FOREIGN KEY (service_id) REFERENCES services(id),
    INDEX idx_orders_status (status),
    INDEX idx_orders_client (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS projects (
    id           INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    order_id     INT UNSIGNED NOT NULL UNIQUE,
    employee_id  INT UNSIGNED DEFAULT NULL,
    progress     TINYINT UNSIGNED NOT NULL DEFAULT 0,
    status       ENUM('assigned','in_progress','review','done') NOT NULL DEFAULT 'assigned',
    started_at   TIMESTAMP NULL DEFAULT NULL,
    finished_at  TIMESTAMP NULL DEFAULT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_projects_order    FOREIGN KEY (order_id)    REFERENCES orders(id),
    CONSTRAINT fk_projects_employee FOREIGN KEY (employee_id) REFERENCES employees(id),
    INDEX idx_projects_employee (employee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tasks (
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

CREATE TABLE IF NOT EXISTS files (
    id           INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    project_id   INT UNSIGNED NOT NULL,
    uploaded_by  INT UNSIGNED NOT NULL,
    kind         ENUM('reference','deliverable') NOT NULL DEFAULT 'reference',
    original_name VARCHAR(255) NOT NULL,
    stored_path  VARCHAR(255) NOT NULL,
    size_bytes   INT UNSIGNED DEFAULT 0,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_files_project FOREIGN KEY (project_id)  REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_files_user    FOREIGN KEY (uploaded_by) REFERENCES users(id),
    INDEX idx_files_project (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================================
--  GROUPE 6 : FINANCES
-- =====================================================================

-- invoices : `tva_rate` (taux réellement appliqué) intégré.
CREATE TABLE IF NOT EXISTS invoices (
    id           INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    code         VARCHAR(20) NOT NULL UNIQUE,         -- ex : FAC-2026-0001
    order_id     INT UNSIGNED NOT NULL,
    amount_ht    DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    tax_rate     DECIMAL(5,2)  NOT NULL DEFAULT 19.00,
    amount_ttc   DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    tva_rate     DECIMAL(5,2)  NOT NULL DEFAULT 19.00, -- 0.00 = sans TVA
    status       ENUM('unpaid','partial','paid') NOT NULL DEFAULT 'unpaid',
    issued_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_invoices_order FOREIGN KEY (order_id) REFERENCES orders(id),
    INDEX idx_invoices_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- invoice_items : lignes d'une facture (une facture peut regrouper
-- plusieurs commandes ; une facture simple = une seule ligne).
CREATE TABLE IF NOT EXISTS invoice_items (
    id          INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    invoice_id  INT UNSIGNED NOT NULL,
    order_id    INT UNSIGNED NOT NULL,
    label       VARCHAR(255) NOT NULL,
    amount_ht   DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_invoice_items_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    CONSTRAINT fk_invoice_items_order   FOREIGN KEY (order_id)   REFERENCES orders(id),
    INDEX idx_invoice_items_invoice (invoice_id),
    INDEX idx_invoice_items_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payments (
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

CREATE TABLE IF NOT EXISTS messages (
    id           INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    order_id     INT UNSIGNED DEFAULT NULL,
    sender_id    INT UNSIGNED NOT NULL,
    body         TEXT NOT NULL,
    is_read      TINYINT(1) NOT NULL DEFAULT 0,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_messages_sender FOREIGN KEY (sender_id) REFERENCES users(id),
    INDEX idx_messages_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notifications (
    id           INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id      INT UNSIGNED NOT NULL,
    title        VARCHAR(150) NOT NULL,
    body         VARCHAR(255) DEFAULT NULL,
    link         VARCHAR(255) DEFAULT NULL,
    is_read      TINYINT(1) NOT NULL DEFAULT 0,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_notifications_user (user_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS activity_logs (
    id           BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id      INT UNSIGNED DEFAULT NULL,
    action       VARCHAR(100) NOT NULL,
    entity_type  VARCHAR(50)  DEFAULT NULL,
    entity_id    INT UNSIGNED DEFAULT NULL,
    details      VARCHAR(255) DEFAULT NULL,
    ip_address   VARCHAR(45)  DEFAULT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_logs_user (user_id),
    INDEX idx_logs_entity (entity_type, entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;


-- =====================================================================
--  DONNÉES DE RÉFÉRENCE (nécessaires au fonctionnement) — idempotentes
-- =====================================================================

-- Les 3 rôles.
INSERT IGNORE INTO roles (name, label_fr) VALUES
    ('admin',    'Administrateur'),
    ('employee', 'Employé'),
    ('client',   'Client');

-- Les 12 départements réels (clé du routage automatique).
INSERT IGNORE INTO departments (name) VALUES
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

-- Les 7 catégories de services.
INSERT IGNORE INTO service_categories (name) VALUES
    ('Création graphique'),
    ('Impression'),
    ('Web & Digital'),
    ('QR Codes'),
    ('Audiovisuel'),
    ('Marketing digital'),
    ('Formation');

-- Les services réels (nom seulement, prix à renseigner plus tard = 0.00).
-- category_id résolu par NOM ; department_id = 1er département (provisoire).
INSERT INTO services (category_id, department_id, name)
SELECT c.id, d.id, x.name
FROM (SELECT 'Création graphique' AS cat, 'Logo & identité visuelle' AS name UNION ALL
      SELECT 'Création graphique', 'Charte graphique'                       UNION ALL
      SELECT 'Impression',         'Cartes de visite'                       UNION ALL
      SELECT 'Impression',         'Bâches grand format'                    UNION ALL
      SELECT 'Impression',         'Catalogue'                              UNION ALL
      SELECT 'Web & Digital',      'Site vitrine'                           UNION ALL
      SELECT 'Web & Digital',      'E-commerce'                             UNION ALL
      SELECT 'QR Codes',           'QR Code dynamique (abonnement annuel)'  UNION ALL
      SELECT 'Audiovisuel',        'Shooting photo'                         UNION ALL
      SELECT 'Audiovisuel',        'Production vidéo & drone'               UNION ALL
      SELECT 'Marketing digital',  'Community management') AS x
JOIN service_categories c ON c.name = x.cat
JOIN (SELECT id FROM departments ORDER BY id LIMIT 1) d
WHERE NOT EXISTS (SELECT 1 FROM services s WHERE s.name = x.name);


-- =====================================================================
--  COMPTE ADMINISTRATEUR UNIQUE (mot de passe TEMPORAIRE)
--    email        : admin@digitalsmile.dz
--    mot de passe : DigitalSmile2026!   → À CHANGER dès la 1re connexion
--  Le hash ci-dessous est un BCRYPT ($2y$) de ce mot de passe.
--  role_id résolu par NOM (jamais d'ID en dur). Idempotent (par email).
-- =====================================================================
INSERT INTO users (role_id, full_name, email, password_hash)
SELECT r.id, 'Administrateur Digital Smile', 'admin@digitalsmile.dz',
       '$2y$10$i0EYcgWtweYWptzKlGrnmuYFPkExiJGvNcEZlSLnIyaVByIvm7KqC'
FROM roles r
WHERE r.name = 'admin'
  AND NOT EXISTS (SELECT 1 FROM users u WHERE u.email = 'admin@digitalsmile.dz');
