<?php
// Variables : $titre, $classes, $erreurs, $nom
ob_start();
?>
<h1 class="h3 mb-3">🏫 Classes</h1>
<div class="row g-4">
    <div class="col-12 col-lg-7">
        <div class="table-responsive">
            <table class="table align-middle bg-white shadow-sm">
                <thead class="table-light"><tr><th>Classe</th><th class="text-center">Étudiants</th><th></th></tr></thead>
                <tbody>
                    <?php foreach ($classes as $c): ?>
                        <tr>
                            <td class="fw-semibold"><?= e($c["nom"]) ?></td>
                            <td class="text-center"><span class="badge text-bg-primary"><?= (int) $c["effectif"] ?></span></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-secondary" href="<?= BASE_URL ?>/index.php?action=emploi_du_temps&amp;classe=<?= (int) $c["id"] ?>">Emploi du temps</a>
                                <form method="post" action="<?= BASE_URL ?>/index.php?action=classe_supprimer" class="d-inline"
                                      onsubmit="return confirm('Supprimer cette classe ? Ses étudiants deviendront « non affectés ».');">
                                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $c["id"] ?>">
                                    <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-12 col-lg-5">
        <div class="card shadow-sm border-0"><div class="card-body">
            <h2 class="h5">Nouvelle classe</h2>
            <?php require RACINE . "/app/views/_erreurs.php"; ?>
            <form method="post" action="<?= BASE_URL ?>/index.php?action=classe_enregistrer" class="d-flex gap-2">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="text" name="nom" class="form-control" required maxlength="50" placeholder="ex. 5e B" value="<?= e($nom) ?>" aria-label="Nom de la classe">
                <button class="btn btn-primary">Créer</button>
            </form>
        </div></div>
    </div>
</div>
<?php
$contenu = ob_get_clean();
require RACINE . "/app/views/layout.php";
