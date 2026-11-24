<?php
// app/views/matieres/formulaire.php
// Variables reçues du contrôleur : $matiere (id, nom, coefficient), $erreurs
$modification = (int) $matiere['id'] > 0;
$titre = $modification ? 'Modifier une matière' : 'Ajouter une matière';
ob_start();
?>
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-4"><?= e($titre) ?></h1>

                <form method="post" action="<?= BASE_URL ?>/index.php?action=matiere_enregistrer">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="id" value="<?= (int) $matiere['id'] ?>">

                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom de la matière</label>
                        <input type="text" id="nom" name="nom" maxlength="50" required
                               class="form-control <?= isset($erreurs['nom']) ? 'is-invalid' : '' ?>"
                               value="<?= e($matiere['nom']) ?>">
                        <?php if (isset($erreurs['nom'])): ?>
                            <div class="invalid-feedback"><?= e($erreurs['nom']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <label for="coefficient" class="form-label">Coefficient (1 à 10)</label>
                        <input type="number" id="coefficient" name="coefficient" min="1" max="10" step="1" required
                               class="form-control <?= isset($erreurs['coefficient']) ? 'is-invalid' : '' ?>"
                               value="<?= e($matiere['coefficient']) ?>">
                        <?php if (isset($erreurs['coefficient'])): ?>
                            <div class="invalid-feedback"><?= e($erreurs['coefficient']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                        <a href="<?= BASE_URL ?>/index.php?action=matieres" class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$contenu = ob_get_clean();
require __DIR__ . '/../layout.php';
