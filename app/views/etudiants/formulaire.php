<?php ob_start(); ?>
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h1 class="h4 mb-4"><?= e($titre) ?></h1>

                <?php if (!empty($erreurs)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($erreurs as $erreur): ?>
                                <li><?= e($erreur) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?= BASE_URL ?>/index.php?action=enregistrer" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="id" value="<?= (int) $id ?>">

                    <div class="mb-3">
                        <label for="matricule" class="form-label">Matricule</label>
                        <input type="text" name="matricule" id="matricule" class="form-control"
                               maxlength="20" placeholder="Ex : E2026007" required
                               value="<?= e($valeurs["matricule"]) ?>">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="nom" class="form-label">Nom</label>
                            <input type="text" name="nom" id="nom" class="form-control"
                                   maxlength="50" required value="<?= e($valeurs["nom"]) ?>">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="prenom" class="form-label">Prénom</label>
                            <input type="text" name="prenom" id="prenom" class="form-control"
                                   maxlength="50" required value="<?= e($valeurs["prenom"]) ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="email" class="form-label">Adresse e-mail</label>
                            <input type="email" name="email" id="email" class="form-control"
                                   maxlength="120" value="<?= e($valeurs["email"]) ?>">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="tel" class="form-label">Téléphone</label>
                            <input type="text" name="tel" id="tel" class="form-control"
                                   maxlength="30" value="<?= e($valeurs["tel"]) ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <span class="form-label d-block">Sexe</span>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="sexe" id="sexe_m" value="Masculin"
                                       <?= $valeurs["sexe"] === "Masculin" ? "checked" : "" ?>>
                                <label class="form-check-label" for="sexe_m">Masculin</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="sexe" id="sexe_f" value="Feminin"
                                       <?= $valeurs["sexe"] === "Feminin" ? "checked" : "" ?>>
                                <label class="form-check-label" for="sexe_f">Féminin</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="classe_id" class="form-label">Classe</label>
                            <select name="classe_id" id="classe_id" class="form-select">
                                <option value="">Non affecté</option>
                                <?php foreach ($classes as $c): ?>
                                    <option value="<?= (int) $c["id"] ?>"
                                        <?= (string) $valeurs["classe_id"] === (string) $c["id"] ? "selected" : "" ?>>
                                        <?= e($c["nom"]) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="photo" class="form-label">Photo (JPG, PNG ou WebP, 2 Mo maximum)</label>

                        <?php if (!empty($valeurs["photo"])): ?>
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <img src="<?= BASE_URL ?>/uploads/<?= e($valeurs["photo"]) ?>" alt="Photo actuelle de l'étudiant"
                                     class="rounded border" style="width: 80px; height: 80px; object-fit: cover;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="retirer_photo" id="retirer_photo" value="1">
                                    <label class="form-check-label" for="retirer_photo">Retirer la photo</label>
                                </div>
                            </div>
                        <?php endif; ?>

                        <input type="file" name="photo" id="photo" class="form-control"
                               accept="image/jpeg,image/png,image/webp">
                    </div>

                    <div class="d-flex gap-2">
                        <a href="<?= BASE_URL ?>/index.php?action=liste" class="btn btn-secondary w-50">Annuler</a>
                        <button type="submit" class="btn btn-primary w-50">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$contenu = ob_get_clean();
require RACINE . "/app/views/layout.php";
