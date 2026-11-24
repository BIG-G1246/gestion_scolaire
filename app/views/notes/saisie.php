<?php
// app/views/notes/saisie.php
// Variables reçues du contrôleur : $titre, $etudiant, $grille, $saisies, $erreurs
ob_start();
?>
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <h1 class="h4 mb-0"><?= e($titre) ?></h1>
                    <span class="badge text-bg-light border"><?= e($etudiant["matricule"]) ?></span>
                </div>

                <?php if (!empty($erreurs)): ?>
                    <div class="alert alert-danger">
                        <strong>Rien n'a été enregistré.</strong> Corrigez :
                        <ul class="mb-0">
                            <?php foreach ($erreurs as $erreur): ?>
                                <li><?= e($erreur) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (empty($grille)): ?>
                    <div class="alert alert-info">
                        Aucune matière n'existe encore. Un administrateur doit d'abord créer les matières.
                    </div>
                <?php else: ?>
                    <p class="text-muted small">Notes sur 20. La virgule est acceptée (ex. 12,5). Laissez la case vide s'il n'y a pas de note.</p>

                    <form action="<?= BASE_URL ?>/index.php?action=notes_enregistrer" method="post">
                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="etudiant_id" value="<?= (int) $etudiant["id"] ?>">

                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Matière</th>
                                        <th class="text-center">Coef.</th>
                                        <th style="width: 9rem;">Note / 20</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($grille as $ligne): ?>
                                        <?php
                                        $mid = (int) $ligne["matiere_id"];
                                        // Après une erreur : la saisie de l'utilisateur. Sinon : la note de la base.
                                        $valeur = array_key_exists($mid, $saisies)
                                            ? $saisies[$mid]
                                            : ($ligne["valeur"] === null ? "" : str_replace(".", ",", rtrim(rtrim($ligne["valeur"], "0"), ".")));
                                        ?>
                                        <tr>
                                            <td><label for="note_<?= $mid ?>" class="fw-semibold"><?= e($ligne["matiere"]) ?></label></td>
                                            <td class="text-center"><span class="badge text-bg-primary"><?= (int) $ligne["coefficient"] ?></span></td>
                                            <td>
                                                <input type="text" inputmode="decimal" maxlength="5"
                                                       id="note_<?= $mid ?>" name="notes[<?= $mid ?>]"
                                                       value="<?= e($valeur) ?>"
                                                       class="form-control <?= isset($erreurs[$mid]) ? "is-invalid" : "" ?>"
                                                       placeholder="—">
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary">💾 Enregistrer les notes</button>
                            <a href="<?= BASE_URL ?>/index.php?action=bulletin&amp;id=<?= (int) $etudiant["id"] ?>" class="btn btn-outline-secondary">Voir le bulletin</a>
                            <a href="<?= BASE_URL ?>/index.php?action=liste" class="btn btn-link">Retour à la liste</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php
$contenu = ob_get_clean();
require RACINE . "/app/views/layout.php";
