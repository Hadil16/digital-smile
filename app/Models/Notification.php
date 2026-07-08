<?php
/**
 * app/Models/Notification.php
 * -----------------------------------------------------------------
 * Notifications internes (cloche + page /notifications).
 * Le "message" de l'application est stocké dans la colonne `title`
 * (le schéma a title + body + link). Une notification naît NON lue.
 *
 * Les helpers de résolution de destinataires (admins, client d'une
 * commande, user d'un employé) vivent ICI : les contrôleurs restent
 * sans SQL et se contentent d'appeler create().
 * -----------------------------------------------------------------
 */

class Notification extends Model
{
    protected string $table = 'notifications';

    /** Crée une notification (non lue) pour un utilisateur. Renvoie son id. */
    public function create(int $userId, string $message, string $link = ''): int
    {
        return $this->insert([
            'user_id' => $userId,
            'title'   => $message,
            'link'    => ($link !== '' ? $link : null),
        ]);
    }

    /** Dernières notifications d'un utilisateur (les plus récentes d'abord). */
    public function forUser(int $userId, int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, title, body, link, is_read, created_at
             FROM notifications
             WHERE user_id = :uid
             ORDER BY created_at DESC, id DESC
             LIMIT :lim"
        );
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', max(1, min(100, $limit)), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Nombre de notifications non lues (pour la pastille de la cloche). */
    public function unreadCount(int $userId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND is_read = 0"
        );
        $stmt->execute([':uid' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    /** Marque toutes les notifications d'un utilisateur comme lues. */
    public function markAllRead(int $userId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE notifications SET is_read = 1 WHERE user_id = :uid AND is_read = 0"
        );
        $stmt->execute([':uid' => $userId]);
        return true;
    }

    // ---- Résolution des destinataires (utilisée par les contrôleurs) --------

    /** Ids des utilisateurs administrateurs actifs. */
    public function adminUserIds(): array
    {
        $sql = "SELECT u.id
                FROM users u JOIN roles r ON r.id = u.role_id
                WHERE r.name = 'admin' AND u.is_active = 1 AND u.deleted_at IS NULL";
        return array_map('intval', $this->db->query($sql)->fetchAll(PDO::FETCH_COLUMN));
    }

    /** Numéro + id du user client d'une commande (['code', 'client_user_id']), ou null. */
    public function orderInfo(int $orderId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT o.code, u.id AS client_user_id
             FROM orders o
             JOIN clients c ON c.id = o.client_id
             JOIN users   u ON u.id = c.user_id
             WHERE o.id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $orderId]);
        return $stmt->fetch() ?: null;
    }

    /** Id du user d'un employé (par employees.id), ou null. */
    public function employeeUserId(int $employeeId): ?int
    {
        $stmt = $this->db->prepare("SELECT user_id FROM employees WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $employeeId]);
        $uid = $stmt->fetchColumn();
        return $uid !== false ? (int) $uid : null;
    }
}
