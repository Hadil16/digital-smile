<?php
/**
 * app/Controllers/NotificationController.php
 * -----------------------------------------------------------------
 * Page des notifications, partagée par tous les rôles CONNECTÉS.
 * Ouvrir la page marque toutes les notifications comme lues.
 * -----------------------------------------------------------------
 */

class NotificationController
{
    /** Liste les notifications de l'utilisateur puis les marque lues. */
    public function index(): void
    {
        require_login();

        $model = new Notification();
        $uid   = (int) $_SESSION['user_id'];

        // On lit d'abord (pour afficher l'état "non lu"), puis on marque lu.
        $notifications = $model->forUser($uid, 30);
        $model->markAllRead($uid);

        require ROOT_PATH . '/app/Views/notifications.php';
    }
}
