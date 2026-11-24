<?php
// app/views/etudiants/acces.php
// Variables reçues du contrôleur : $titre, $etudiant, $erreurs
ob_start();
?>
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h1 class="h4 mb-1"><?= e($titre) ?></h1>
                <p class="text-muted"><?= e($etudiant["prenom"] . " " . $etudiant["nom"]) ?> se connectera avec son matricule
                    <strong><?= e($etudiant["matricule"]) ?></strong> et le mot de passe ci-dessous.</p>

                <p>
                    État actuel :
                    <?php if ($etudiant["mdp_hash"] !== null): ?>
                        <span class="badge text-bg-success">Accès actif</span>
                    <?php else: ?>
                        <span class="badge text-bg-secondary">Pas d'accès</span>
                    <?php endif; ?>
                </p>

                <?php if (!empty($erreurs)): ?>
                    <div class="alert alert-danger"><ul class="mb-0">
                        <?php foreach ($erreurs as $erreur): ?><li><?= e($erreur) ?></li><?php endforeach; ?>
                    </ul></div>
                <?php endif; ?>

                <form action="<?= BASE_URL ?>/index.php?action=acces_enregistrer" method="post" class="mb-3">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="id" value="<?= (int) $etudiant["id"] ?>">
                    <div class="mb-3">
                        <label for="mdp" class="form-label">Nouveau mot de passe (8 caractères minimum)</label>
                        <input type="password" name="mdp" id="mdp" class="form-control" required minlength="8" autocomplete="new-password">
                    </div>
                    <div class="mb-3">
                        <label for="confirmation" class="form-label">Confirmer le mot de passe</label>
                        <input type="password" name="confirmation" id="confirmation" class="form-control" required minlength="8" autocomplete="new-password">
                    </div>
                    <button type="submit" class="btn btn-primary">Enregistrer le mot de passe</button>
                    <a href="<?= BASE_URL ?>/index.php?action=liste" class="btn btn-link">Annuler</a>
                </form>

                <?php if ($etudiant["mdp_hash"] !== null): ?>
                    <form action="<?= BASE_URL ?>/index.php?action=acces_enregistrer" method="post"
                          onsubmit="return confirm('Retirer l\'accès de cet étudiant ?');">
                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="id" value="<?= (int) $etudiant["id"] ?>">
                        <input type="hidden" name="retirer" value="1">
                        <button type="submit" class="btn btn-sm btn-outline-danger">Retirer l'accès</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php
$contenu = ob_get_clean();
require RACINE . "/app/views/layout.php";
