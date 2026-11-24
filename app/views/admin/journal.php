<?php
// Variables : $titre, $lignes
ob_start();
?>
<h1 class="h3 mb-1">🕵️ Journal d'activité</h1>
<p class="text-muted">Les 300 dernières actions : connexions, échecs de connexion, inscriptions, notes, caisse, comptes.</p>
<div class="table-responsive">
    <table class="table table-sm table-hover align-middle bg-white shadow-sm">
        <thead class="table-light"><tr><th>Date</th><th>Utilisateur</th><th>Rôle</th><th>Action</th><th>Adresse IP</th></tr></thead>
        <tbody>
            <?php if (empty($lignes)): ?><tr><td colspan="5" class="text-center text-muted py-4">Aucune activité.</td></tr><?php endif; ?>
            <?php foreach ($lignes as $l): ?>
                <tr class="<?= str_starts_with($l["action"], "Échec") ? "table-danger" : "" ?>">
                    <td class="text-nowrap"><?= e(date("d/m/Y H:i", strtotime($l["cree_le"]))) ?></td>
                    <td class="fw-semibold"><?= e($l["utilisateur"]) ?></td>
                    <td><?= e(libelle_role($l["role"])) ?></td>
                    <td><?= e($l["action"]) ?></td>
                    <td class="text-muted small"><?= e($l["ip"] ?? "") ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
$contenu = ob_get_clean();
require RACINE . "/app/views/layout.php";
