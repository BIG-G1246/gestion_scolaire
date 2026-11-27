<?php ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0"><?= e($titre) ?></h1>
    <a href="<?= BASE_URL ?>/index.php?action=formulaire" class="btn btn-success">➕ Ajouter</a>
</div>

<!-- Barre de recherche.
     1) Sans JavaScript : le formulaire envoie ?q=... au serveur, qui filtre en SQL (EtudiantModel::rechercher).
     2) Avec JavaScript : le tableau se filtre en direct pendant la frappe, sans recharger la page (script en bas). -->
<form method="get" action="<?= BASE_URL ?>/index.php" class="card border-0 shadow-sm mb-3" role="search">
    <div class="card-body d-flex flex-wrap gap-2 align-items-center">
        <input type="hidden" name="action" value="liste">
        <div class="input-group" style="flex: 1 1 220px; width: auto;">
            <span class="input-group-text bg-white">🔍</span>
            <input type="search" name="q" id="recherche" class="form-control" maxlength="50" autocomplete="off"
                   value="<?= e($q) ?>" placeholder="Nom, prénom, matricule ou classe (ex. 3e, 6eA)"
                   aria-label="Rechercher un étudiant">
        </div>
        <button type="submit" class="btn btn-primary">Rechercher</button>
        <?php if ($q !== ""): ?>
            <a href="<?= BASE_URL ?>/index.php?action=liste" class="btn btn-outline-secondary">Effacer</a>
        <?php endif; ?>
        <span class="text-muted small ms-auto" id="compteur" aria-live="polite">
            <?= count($etudiants) ?> étudiant<?= count($etudiants) > 1 ? "s" : "" ?>
        </span>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-striped table-hover align-middle bg-white" id="tableEtudiants">
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
                <td colspan="7" class="text-center text-muted py-4">
                    <?= $q === "" ? "Aucun étudiant inscrit pour le moment." : "Aucun étudiant ne correspond à « " . e($q) . " »." ?>
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($etudiants as $i => $etu): ?>
            <?php
            // Texte utilisé par le filtre en direct : nom, prénom (dans les deux sens), matricule, classe sans espace
            $cherche = mb_strtolower($etu["nom"] . " " . $etu["prenom"] . " " . $etu["prenom"] . " " . $etu["nom"] . " " . $etu["matricule"]);
            $classe_cherche = mb_strtolower(str_replace(" ", "", $etu["classe"] ?? ""));
            ?>
            <tr data-texte="<?= e($cherche) ?>" data-classe="<?= e($classe_cherche) ?>">
                <td class="numero"><?= $i + 1 ?></td>
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
        <tr id="aucunResultat" class="d-none">
            <td colspan="7" class="text-center text-muted py-4">Aucun étudiant ne correspond à cette recherche.</td>
        </tr>
        </tbody>
    </table>
</div>

<script>
// Filtre en direct : à chaque touche, on cache les lignes qui ne contiennent pas le texte tapé.
(function () {
    const champ = document.getElementById("recherche");
    const lignes = document.querySelectorAll("#tableEtudiants tbody tr[data-texte]");
    const compteur = document.getElementById("compteur");
    const aucun = document.getElementById("aucunResultat");

    // "Élodie" devient "elodie" : on compare sans majuscules ni accents
    const normaliser = (t) => t.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");

    champ.addEventListener("input", function () {
        const texte = normaliser(champ.value.trim());
        const sansEspace = texte.replace(/\s+/g, "");
        let visibles = 0;

        lignes.forEach(function (ligne) {
            const ok = texte === ""
                || normaliser(ligne.dataset.texte).includes(texte)
                || (sansEspace !== "" && normaliser(ligne.dataset.classe).includes(sansEspace));
            ligne.classList.toggle("d-none", !ok);
            if (ok) {
                visibles++;
                ligne.querySelector(".numero").textContent = visibles; // renumérote 1, 2, 3...
            }
        });

        compteur.textContent = visibles + " étudiant" + (visibles > 1 ? "s" : "");
        aucun.classList.toggle("d-none", visibles > 0 || lignes.length === 0);
    });
})();
</script>
<?php
$contenu = ob_get_clean();
require RACINE . "/app/views/layout.php";
