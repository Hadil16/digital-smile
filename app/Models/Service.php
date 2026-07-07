<?php
/**
 * app/Models/Service.php
 * -----------------------------------------------------------------
 * Catalogue des services. Lecture seule ici : sert à alimenter le
 * menu déroulant du formulaire de demande.
 * -----------------------------------------------------------------
 */

class Service extends Model
{
    protected string $table = 'services';

    /**
     * Renvoie les services actifs (id, name), triés par nom.
     * NB : la table `services` n'a pas de colonne deleted_at ; le
     * « non supprimé » se traduit ici par le drapeau is_active = 1.
     */
    public function allActive(): array
    {
        $stmt = $this->db->query(
            "SELECT id, name FROM services WHERE is_active = 1 ORDER BY name ASC"
        );
        return $stmt->fetchAll();
    }
}
