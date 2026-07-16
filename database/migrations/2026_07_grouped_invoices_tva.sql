-- =====================================================================
--  MIGRATION — TVA optionnelle + factures groupées + drapeau "facturée"
--  Base : digital_smile   ·   Moteur : MariaDB (XAMPP)
--
--  COMMENT L'EXÉCUTER (une seule fois) :
--    1. Ouvrez phpMyAdmin → http://localhost/phpmyadmin
--    2. Sélectionnez la base « digital_smile »
--    3. Onglet « SQL » → collez ce fichier → « Exécuter »
--
--  Idempotent : réexécutable sans erreur (IF NOT EXISTS — MariaDB / XAMPP).
--  Tant que cette migration n'est pas lancée, l'application fonctionne :
--  la génération classique (1 commande = 1 facture) continue via la colonne
--  existante `tax_rate`, et la facturation groupée est simplement refusée
--  avec un message clair (dégradation propre).
-- =====================================================================

USE digital_smile;

-- 1. Taux de TVA RÉELLEMENT appliqué à la facture (0.00 = sans TVA).
--    (double de `tax_rate` déjà présent, conservé synchronisé par le modèle.)
ALTER TABLE invoices
    ADD COLUMN IF NOT EXISTS tva_rate DECIMAL(5,2) NOT NULL DEFAULT 19.00 AFTER amount_ttc;

-- 2. Lignes de facture : une facture peut regrouper PLUSIEURS commandes.
--    Une facture classique = une seule ligne (un invoice_item).
CREATE TABLE IF NOT EXISTS invoice_items (
    id          INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    invoice_id  INT UNSIGNED NOT NULL,
    order_id    INT UNSIGNED NOT NULL,
    label       VARCHAR(255) NOT NULL,                 -- ex : "Logo — Refonte identité"
    amount_ht   DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_invoice_items_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    CONSTRAINT fk_invoice_items_order   FOREIGN KEY (order_id)   REFERENCES orders(id),
    INDEX idx_invoice_items_invoice (invoice_id),
    INDEX idx_invoice_items_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Drapeau "facturée" sur la commande : dès qu'une commande entre dans une
--    facture (simple ou groupée), elle ne réapparaît plus dans « à facturer ».
ALTER TABLE orders
    ADD COLUMN IF NOT EXISTS invoiced TINYINT(1) NOT NULL DEFAULT 0 AFTER status;

-- 4. Cohérence : marquer déjà "facturées" les commandes qui ont déjà une facture.
UPDATE orders o
    JOIN invoices i ON i.order_id = o.id
    SET o.invoiced = 1;
