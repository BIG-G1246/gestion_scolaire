<?php
// Variables : $titre, $classes, $erreurs, $nom
// Secrétaire et directeur voient la liste ; seul le directeur crée ou supprime une classe.
$total = array_sum(array_column($classes, "effectif"));
ob_start();
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">🏫 Classes</h1>
    <span class="text-muted"><?= count($classes) ?> classe<?= count($classes) > 1 ? "s" : "" ?> · <?= (int) $total ?> étudiant<?= $total > 1 ? "s" : "" ?> affecté<?= $total > 1 ? "s" : "" ?></span>
</div>

<div class="row g-4">
    <div class="col-12 <?= est_admin() ? "col-xl-8" : "" ?>">
        <?php if (empty($classes)): ?>
            <div class="alert alert-info">Aucune classe pour le moment.</div>
        <?php endif; ?>
        <!-- Une carte par classe : un clic ouvre la classe (effectif et liste des étudiants) -->
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3">
            <?php foreach ($classes as $c): ?>
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm carte-stat">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <h2 class="h5 fw-bold mb-1"><?= e($c["nom"]) ?></h2>
                                <?php if (est_admin()): ?>
                                    <form method="post" action="<?= BASE_URL ?>/index.php?action=classe_supprimer" class="m-0"
                                          onsubmit="return confirm('Supprimer cette classe ? Ses étudiants deviendront « non affectés ».');">
                                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $c["id"] ?>">
                                        <button class="btn btn-sm btn-link text-danger p-0 text-decoration-none" title="Supprimer la classe">✖</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                            <div class="valeur"><?= (int) $c["effectif"] ?></div>
                            <div class="text-muted small">étudiant<?= $c["effectif"] > 1 ? "s" : "" ?></div>
                        </div>
                        <div class="card-footer bg-white border-0 pt-0 d-flex gap-2">
                            <a class="btn btn-sm btn-primary" href="<?= BASE_URL ?>/index.php?action=classe&amp;id=<?= (int) $c["id"] ?>">Ouvrir</a>
                            <a class="btn btn-sm btn-outline-secondary" href="<?= BASE_URL ?>/index.php?action=emploi_du_temps&amp;classe=<?= (int) $c["id"] ?>">Emploi du temps</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (est_admin()): ?>
        <div class="col-12 col-xl-4">
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
    <?php endif; ?>
</div>
<?php
$contenu = ob_get_clean();
require RACINE . "/app/views/layout.php";
