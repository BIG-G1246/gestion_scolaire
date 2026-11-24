<?php ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0"><?= e($titre) ?></h1>
    <a href="<?= BASE_URL ?>/index.php?action=formulaire" class="btn btn-success">➕ Ajouter</a>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover align-middle bg-white">
        <thead class="table-primary">
            <tr>
                <th>N°</th>
                <th>Photo</th>
                <th>Matricule</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Classe</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($etudiants)): ?>
            <tr>
                <td colspan="7" class="text-center text-muted py-4">Aucun étudiant inscrit pour le moment.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($etudiants as $i => $etu): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td>
                    <?php if (!empty($etu["photo"])): ?>
                        <img src="<?= BASE_URL ?>/uploads/<?= e($etu["photo"]) ?>"
                             alt="Photo de <?= e($etu["prenom"] . " " . $etu["nom"]) ?>"
                             class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                    <?php else: ?>
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-secondary-subtle text-secondary fw-bold"
                              style="width: 40px; height: 40px;">
                            <?= e(mb_strtoupper(mb_substr($etu["prenom"], 0, 1) . mb_substr($etu["nom"], 0, 1))) ?>
                        </span>
                    <?php endif; ?>
                </td>
                <td><?= e($etu["matricule"]) ?></td>
                <td><?= e($etu["nom"]) ?></td>
                <td><?= e($etu["prenom"]) ?></td>
                <td>
                    <?php if ($etu["classe"] !== null): ?>
                        <span class="badge text-bg-primary"><?= e($etu["classe"]) ?></span>
                    <?php else: ?>
                        <span class="badge text-bg-secondary">Non affecté</span>
                    <?php endif; ?>
                </td>
                <td class="text-end text-nowrap">
                    <a href="<?= BASE_URL ?>/index.php?action=notes&amp;id=<?= (int) $etu["id"] ?>"
                       class="btn btn-sm btn-outline-primary">Notes</a>
                    <a href="<?= BASE_URL ?>/index.php?action=bulletin&amp;id=<?= (int) $etu["id"] ?>"
                       class="btn btn-sm btn-outline-success">Bulletin</a>
                    <a href="<?= BASE_URL ?>/index.php?action=acces&amp;id=<?= (int) $etu["id"] ?>"
                       class="btn btn-sm <?= $etu["a_acces"] ? "btn-outline-dark" : "btn-outline-warning" ?>"
                       title="<?= $etu["a_acces"] ? "L'étudiant a un mot de passe" : "Pas encore d'accès à l'espace étudiant" ?>">🔑</a>
                    <a href="<?= BASE_URL ?>/index.php?action=modifier&amp;id=<?= (int) $etu["id"] ?>"
                       class="btn btn-sm btn-outline-secondary">Modifier</a>

                    <?php if (est_admin()): ?>
                        <?php $question = "Supprimer " . $etu["prenom"] . " " . $etu["nom"] . " ? Ses notes seront aussi supprimées."; ?>
                        <form action="<?= BASE_URL ?>/index.php?action=supprimer" method="post" class="d-inline"
                              onsubmit="return confirm(<?= e(json_encode($question)) ?>);">
                            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="id" value="<?= (int) $etu["id"] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php
$contenu = ob_get_clean();
require RACINE . "/app/views/layout.php";
