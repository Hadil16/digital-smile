-- =====================================================================
--  database/seed_services.sql
--  Données réelles du catalogue Digital Smile : catégories + services.
--
--  IDEMPOTENT : ce script peut être ré-exécuté sans créer de doublon.
--    - Catégories : `service_categories.name` est UNIQUE → INSERT IGNORE
--      (une 2e exécution ignore simplement les lignes déjà présentes).
--    - Services : `services.name` n'est PAS unique → on insère seulement
--      si le nom n'existe pas encore (INSERT ... SELECT ... WHERE NOT EXISTS).
--
--  PAS DE PRIX INVENTÉ : `base_price` garde sa valeur par défaut (0.00),
--  à renseigner plus tard avec les vrais tarifs.
--
--  ⚠️ department_id PROVISOIRE (« À AFFINER ») : la table `services` exige
--  un département (NOT NULL + clé étrangère, clé du routage automatique).
--  Le vrai mapping service → département sera défini plus tard ; en
--  attendant on rattache chaque service au 1er département existant.
--  Pré-requis : la table `departments` doit déjà contenir au moins 1 ligne.
-- =====================================================================


-- --- 1) Les 7 catégories (idempotent via la contrainte UNIQUE) -------
INSERT IGNORE INTO service_categories (name) VALUES
    ('Création graphique'),
    ('Impression'),
    ('Web & Digital'),
    ('QR Codes'),
    ('Audiovisuel'),
    ('Marketing digital'),
    ('Formation');


-- --- 2) Les services réels (nom seulement, sans prix) ----------------
-- Chaque insert ne s'exécute que si le service n'existe pas déjà (par nom).
-- category_id : résolu par le nom de la catégorie (jamais d'ID en dur).
-- department_id : 1er département existant (provisoire, cf. en-tête).

INSERT INTO services (category_id, department_id, name)
SELECT (SELECT id FROM service_categories WHERE name = 'Création graphique'),
       (SELECT id FROM departments ORDER BY id LIMIT 1),
       'Logo & identité visuelle'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM services WHERE name = 'Logo & identité visuelle');

INSERT INTO services (category_id, department_id, name)
SELECT (SELECT id FROM service_categories WHERE name = 'Création graphique'),
       (SELECT id FROM departments ORDER BY id LIMIT 1),
       'Charte graphique'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM services WHERE name = 'Charte graphique');

INSERT INTO services (category_id, department_id, name)
SELECT (SELECT id FROM service_categories WHERE name = 'Impression'),
       (SELECT id FROM departments ORDER BY id LIMIT 1),
       'Cartes de visite'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM services WHERE name = 'Cartes de visite');

INSERT INTO services (category_id, department_id, name)
SELECT (SELECT id FROM service_categories WHERE name = 'Impression'),
       (SELECT id FROM departments ORDER BY id LIMIT 1),
       'Bâches grand format'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM services WHERE name = 'Bâches grand format');

INSERT INTO services (category_id, department_id, name)
SELECT (SELECT id FROM service_categories WHERE name = 'Impression'),
       (SELECT id FROM departments ORDER BY id LIMIT 1),
       'Catalogue'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM services WHERE name = 'Catalogue');

INSERT INTO services (category_id, department_id, name)
SELECT (SELECT id FROM service_categories WHERE name = 'Web & Digital'),
       (SELECT id FROM departments ORDER BY id LIMIT 1),
       'Site vitrine'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM services WHERE name = 'Site vitrine');

INSERT INTO services (category_id, department_id, name)
SELECT (SELECT id FROM service_categories WHERE name = 'Web & Digital'),
       (SELECT id FROM departments ORDER BY id LIMIT 1),
       'E-commerce'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM services WHERE name = 'E-commerce');

INSERT INTO services (category_id, department_id, name)
SELECT (SELECT id FROM service_categories WHERE name = 'QR Codes'),
       (SELECT id FROM departments ORDER BY id LIMIT 1),
       'QR Code dynamique (abonnement annuel)'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM services WHERE name = 'QR Code dynamique (abonnement annuel)');

INSERT INTO services (category_id, department_id, name)
SELECT (SELECT id FROM service_categories WHERE name = 'Audiovisuel'),
       (SELECT id FROM departments ORDER BY id LIMIT 1),
       'Shooting photo'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM services WHERE name = 'Shooting photo');

INSERT INTO services (category_id, department_id, name)
SELECT (SELECT id FROM service_categories WHERE name = 'Audiovisuel'),
       (SELECT id FROM departments ORDER BY id LIMIT 1),
       'Production vidéo & drone'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM services WHERE name = 'Production vidéo & drone');

INSERT INTO services (category_id, department_id, name)
SELECT (SELECT id FROM service_categories WHERE name = 'Marketing digital'),
       (SELECT id FROM departments ORDER BY id LIMIT 1),
       'Community management'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM services WHERE name = 'Community management');
