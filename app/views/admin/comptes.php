<?php
// Variables : $titre, $membres, $erreurs, $saisie
ob_start();
?>
<h1 class="h3 mb-3">🔐 Comptes du personnel</h1>
<div class="row g-4">
    <div class="col-12 col-lg-7">
        <div class="table-responsive">
            <table class="table align-middle bg-white shadow-sm">
                <thead class="table-light"><tr><th>Identifiant</th><th>Rôle</th><th>Créé le</th><th></th></tr></thead>
                <tbody>
                    <?php foreach ($membres as $m): ?>
                        <tr>
                            <td class="fw-semibold"><?= e($m["login"]) ?></td>
                            <td><span class="badge <?= $m["role"] === "admin" ? "text-bg-primary" : "text-bg-secondary" ?>"><?= e(libelle_role($m["role"])) ?></span></td>
                            <td class="small text-muted"><?= e(date("d/m/Y", strtotime($m["cree_le"]))) ?></td>
                            <td class="text-end">
                                <?php if ($m["login"] !== $_SESSION["utilisateur"]): ?>
                                    <form method="post" action="<?= BASE_URL ?>/index.php?action=compte_supprimer" class="d-inline"
                                          onsubmit="return confirm('Supprimer ce compte ?');">
                                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $m["id"] ?>">
                                        <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                                    </form>
                                <?php else: ?>
                                    <span class="small text-muted">(vous)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="small text-muted">Les mots de passe des étudiants se donnent depuis la liste des étudiants (bouton 🔑).</p>
    </div>
    <div class="col-12 col-lg-5">
        <div class="card shadow-sm border-0"><div class="card-body">
            <h2 class="h5">Créer un compte</h2>
            <?php require RACINE . "/app/views/_erreurs.php"; ?>
            <form method="post" action="<?= BASE_URL ?>/index.php?action=compte_enregistrer">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <div class="mb-2"><label class="form-label small" for="login">Identifiant</label>
                    <input type="text" name="login" id="login" class="form-control" required maxlength="50" value="<?= e($saisie["login"]) ?>" autocomplete="off"></div>
                <div class="mb-2"><label class="form-label small" for="mdp">Mot de passe (10 caractères minimum)</label>
                    <input type="password" name="mdp" id="mdp" class="form-control" required minlength="10" autocomplete="new-password"></div>
                <div class="mb-3"><label class="form-label small" for="role">Rôle</label>
                    <select name="role" id="role" class="form-select">
                        <option value="membre" <?= $saisie["role"] === "membre" ? "selected" : "" ?>>Secrétaire</option>
                        <option value="admin" <?= $saisie["role"] === "admin" ? "selected" : "" ?>>Directeur</option>
                    </select></div>
                <button class="btn btn-primary w-100">Créer le compte</button>
            </form>
        </div></div>
    </div>
</div>
<?php
$contenu = ob_get_clean();
require RACINE . "/app/views/layout.php";
