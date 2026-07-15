<?php
/**
 * app/Views/client/new-order.php
 * -----------------------------------------------------------------
 * Formulaire de nouvelle demande de projet (client), coquille A2.
 * Variables fournies par ClientController : $services, $error, $old.
 * Les champs (noms), l'action, la validation et le CSRF sont inchangés.
 * -----------------------------------------------------------------
 */
require ROOT_PATH . '/app/Views/partials/header.php';

// Coquille client commune (sidebar + entête).
$clientActive = 'nouvelle';
$pageTitle    = 'Nouvelle demande';
$pageSubtitle = 'Décrivez votre besoin, nous nous occupons du reste';
require ROOT_PATH . '/app/Views/partials/client-sidebar.php';
?>
        <?php if (!empty($error)): ?>
            <p class="adm-error" role="alert"><?= e($error) ?></p>
        <?php endif; ?>

        <div class="adm-card adm-formcard">
            <form method="post" action="<?= e(BASE_URL) ?>/client/nouvelle-demande" novalidate>
                <?= csrf_field() ?>

                <div class="adm-field">
                    <label class="adm-label" for="service_id">Service souhaité</label>
                    <select class="adm-input" id="service_id" name="service_id" required>
                        <option value="">— Choisir un service —</option>
                        <?php foreach ($services as $s): ?>
                            <option value="<?= (int) $s['id'] ?>"
                                <?= ((string) $s['id'] === $old['service_id']) ? 'selected' : '' ?>>
                                <?= e($s['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="adm-field">
                    <label class="adm-label" for="description">Description de votre besoin</label>
                    <textarea class="adm-textarea" id="description" name="description" rows="5"
                              required><?= e($old['description']) ?></textarea>
                </div>

                <div class="adm-field">
                    <label class="adm-label" for="budget">Budget indicatif (DZD)</label>
                    <input class="adm-input" type="number" id="budget" name="budget"
                           min="0" step="100" value="<?= e($old['budget']) ?>">
                    <p class="cli-hint">Optionnel — laissez vide si vous ne savez pas encore.</p>
                </div>

                <div class="adm-field">
                    <label class="adm-label" for="deadline">Échéance souhaitée</label>
                    <input class="adm-input" type="date" id="deadline" name="deadline"
                           required value="<?= e($old['deadline']) ?>">
                </div>

                <button class="adm-btn adm-btn--primary adm-btn--lg" type="submit">Envoyer la demande</button>
            </form>
        </div>
    </main>
</div>

<style>
    .cli-hint { font-size: 12px; color: var(--color-muted); margin: 8px 0 0; }
</style>

<?php require ROOT_PATH . '/app/Views/partials/footer.php'; ?>
