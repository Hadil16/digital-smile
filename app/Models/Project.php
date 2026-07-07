<?php
/**
 * app/Models/Project.php
 * -----------------------------------------------------------------
 * Projets : une commande approuvée devient un projet assigné à un
 * employé, avec un % de progression. Ce modèle sert l'espace employé.
 *
 * Rappel schéma : `projects.status` = assigned|in_progress|review|done
 * (PAS 'delivered'). L'état "livré" appartient à la COMMANDE
 * (`orders.status`), c'est lui qu'on fait avancer ici.
 * -----------------------------------------------------------------
 */

class Project extends Model
{
    protected string $table = 'projects';

    /**
     * Projets assignés à un employé (les plus récents d'abord), avec les
     * infos utiles : n° de commande, service, client, nom du projet,
     * statut (de la commande), progression, échéance.
     */
    public function forEmployee(int $employeeId): array
    {
        $sql = "SELECT p.id, p.progress,
                       o.id AS order_id, o.code AS order_number, o.status,
                       o.project_name, o.deadline,
                       s.name AS service_name, u.full_name AS client_name
                FROM projects p
                JOIN orders   o ON o.id = p.order_id
                JOIN services s ON s.id = o.service_id
                JOIN clients  c ON c.id = o.client_id
                JOIN users    u ON u.id = c.user_id
                WHERE p.employee_id = :emp
                ORDER BY p.created_at DESC, p.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':emp' => $employeeId]);
        return $stmt->fetchAll();
    }

    /**
     * Renvoie un projet SEULEMENT s'il appartient à cet employé (sinon null).
     * Sert de contrôle de propriété avant toute action (progression, livrable).
     * NB : 2e paramètre optionnel pour rester compatible avec Model::find().
     */
    public function find(int $projectId, int $employeeId = 0): ?array
    {
        $sql = "SELECT p.*, o.code AS order_code
                FROM projects p
                JOIN orders o ON o.id = p.order_id
                WHERE p.id = :pid AND p.employee_id = :emp
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':pid' => $projectId, ':emp' => $employeeId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Met à jour la progression d'un projet (après contrôle de propriété).
     * Borne 0..100. À 100 % : la commande passe 'delivered' et le projet
     * 'done' ; sinon 'in_progress' des deux côtés. Le tout en transaction.
     */
    public function updateProgress(int $projectId, int $employeeId, int $progress): bool
    {
        $progress = max(0, min(100, $progress)); // bornage 0..100

        $proj = $this->find($projectId, $employeeId);
        if ($proj === null) {
            return false; // pas à cet employé (ou inexistant)
        }

        $done = ($progress >= 100);

        $this->db->beginTransaction();
        try {
            // Projet : progression + statut interne cohérent.
            $this->db->prepare("UPDATE projects SET progress = :p, status = :st WHERE id = :id")
                     ->execute([':p' => $progress, ':st' => ($done ? 'done' : 'in_progress'), ':id' => $projectId]);

            // Commande : 'delivered' à 100 %, sinon 'in_progress'.
            $this->db->prepare("UPDATE orders SET status = :st WHERE id = :oid")
                     ->execute([':st' => ($done ? 'delivered' : 'in_progress'), ':oid' => (int) $proj['order_id']]);

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Enregistre le livrable en base (après contrôle de propriété) et fait
     * passer la commande à 'delivered' + le projet à 'done' (100 %).
     * Le fichier lui-même est déjà déplacé par le contrôleur (I/O HTTP).
     */
    public function recordDeliverable(
        int $projectId, int $employeeId, int $uploadedBy,
        string $originalName, string $storedPath, int $sizeBytes
    ): bool {
        $proj = $this->find($projectId, $employeeId);
        if ($proj === null) {
            return false;
        }

        $this->db->beginTransaction();
        try {
            $this->db->prepare(
                "INSERT INTO files (project_id, uploaded_by, kind, original_name, stored_path, size_bytes)
                 VALUES (:pid, :uid, 'deliverable', :orig, :path, :size)"
            )->execute([
                ':pid'  => $projectId,
                ':uid'  => $uploadedBy,
                ':orig' => $originalName,
                ':path' => $storedPath,
                ':size' => $sizeBytes,
            ]);

            $this->db->prepare("UPDATE orders SET status = 'delivered' WHERE id = :oid")
                     ->execute([':oid' => (int) $proj['order_id']]);
            $this->db->prepare("UPDATE projects SET status = 'done', progress = 100 WHERE id = :pid")
                     ->execute([':pid' => $projectId]);

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /** Renvoie l'id de la fiche employé d'un utilisateur, ou null si absent. */
    public function employeeIdForUser(int $userId): ?int
    {
        $stmt = $this->db->prepare("SELECT id FROM employees WHERE user_id = :uid LIMIT 1");
        $stmt->execute([':uid' => $userId]);
        $id = $stmt->fetchColumn();
        return $id !== false ? (int) $id : null;
    }
}
