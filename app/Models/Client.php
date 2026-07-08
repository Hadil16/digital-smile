<?php
/**
 * app/Models/Client.php
 * -----------------------------------------------------------------
 * Clients de l'agence (fiche liée à un compte user de rôle 'client').
 * Lecture seule ici : sert aux statistiques du tableau de bord admin.
 * -----------------------------------------------------------------
 */

class Client extends Model
{
    protected string $table = 'clients';

    /** Nombre total de clients. */
    public function countTotal(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM clients")->fetchColumn();
    }
}
