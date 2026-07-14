-- =====================================================================
--  MIGRATION — Profil employé (photo, expérience, biographie)
--  Base : digital_smile   ·   Moteur : MariaDB (XAMPP)
--
--  COMMENT L'EXÉCUTER (une seule fois) :
--    1. Ouvrez phpMyAdmin → http://localhost/phpmyadmin
--    2. Sélectionnez la base « digital_smile »
--    3. Onglet « SQL » → collez ce fichier → « Exécuter »
--
--  Idempotent : réexécutable sans erreur grâce à « IF NOT EXISTS »
--  (supporté par MariaDB, la base fournie avec XAMPP).
--  Tant que cette migration n'est pas lancée, l'application fonctionne
--  quand même : la photo retombe sur les initiales et les champs
--  expérience/bio restent simplement masqués (dégradation propre).
-- =====================================================================

USE digital_smile;

ALTER TABLE employees
    ADD COLUMN IF NOT EXISTS photo            VARCHAR(255) NULL AFTER specialty,
    ADD COLUMN IF NOT EXISTS experience_years TINYINT      NULL AFTER photo,
    ADD COLUMN IF NOT EXISTS bio              VARCHAR(500) NULL AFTER experience_years;
