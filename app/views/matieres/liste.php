<?php
// app/views/matieres/liste.php
// Variables reçues du contrôleur : $matieres
$titre = 'Matières';
$total_coefficients = array_sum(array_column($matieres, 'coefficient'));
ob_start();
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">Matières</h1>
    <a href="<?= BASE_URL ?>/index.php?action=matiere_formulaire" class="btn btn-primary">
        + Ajouter une matière
    </a>
</div>

<?php if (empty($matieres)): ?>
    <div class="alert alert-info">Aucune matière pour le moment.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle bg-white shadow-sm">
            <thead class="table-light">
                <tr>
                    <th>Matière</th>
                    <th class="text-center">Coefficient</th>
                    <th class="text-center">Notes saisies</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($matieres as $m): ?>
                    <tr>
                        <td class="fw-semibold"><?= e($m['nom']) ?></td>
                        <td class="text-center">
                            <span class="badge text-bg-primary"><?= e($m['coefficient']) ?></span>
                        </td>
                        <td class="text-center"><?= e($m['nb_notes']) ?></td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-outline-primary"
                               href="<?= BASE_URL ?>/index.php?action=matiere_formulaire&id=<?= (int) $m['id'] ?>">
                                Modifier
                            </a>
                            <?php if ((int) $m['nb_notes'] === 0): ?>
                                <form method="post" action="<?= BASE_URL ?>/index.php?action=matiere_supprimer"
                                      class="d-inline" onsubmit="return confirm('Supprimer cette matière ?');">
                                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                                </form>
                            <?php else: ?>
                                <button type="button" class="btn btn-sm btn-outline-secondary" disabled
                                        title="Des notes utilisent cette matière">Supprimer</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="table-light">
                    <th>Total des coefficients</th>
                    <th class="text-center"><?= (int) $total_coefficients ?></th>
                    <th colspan="2"></th>
                </tr>
            </tfoot>
        </table>
    </div>
<?php endif; ?>
<?php
$contenu = ob_get_clean();
require __DIR__ . '/../layout.php';
