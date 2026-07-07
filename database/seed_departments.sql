-- =====================================================================
--  database/seed_departments.sql
--  Les 12 départements réels de Digital Smile (clé du routage auto).
--
--  IDEMPOTENT : `departments.name` est UNIQUE → INSERT IGNORE.
--  Une 2e exécution ignore simplement les lignes déjà présentes.
--  À exécuter AVANT seed_services.sql (les services référencent un département).
-- =====================================================================

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
