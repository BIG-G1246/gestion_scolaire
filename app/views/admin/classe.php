<?php
// Variables : $titre, $classe (id, nom, effectif), $etudiants, $filles, $garcons
ob_start();
?>
<nav aria-label="fil d'Ariane" class="mb-2">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php?action=classes">Classes</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= e($classe["nom"]) ?></li>
    </ol>
</nav>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">🏫 <?= e($titre) ?></h1>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/index.php?action=emploi_du_temps&amp;classe=<?= (int) $classe["id"] ?>">🗓️ Emploi du temps</a>
        <button type="button" class="btn btn-outline-primary" onclick="window.print()">🖨️ Imprimer la liste</button>
    </div>
</div>

<!-- Effectif de la classe -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-4">
        <div class="card carte-stat shadow-sm"><div class="card-body">
            <div class="text-muted small">Effectif</div>
            <div class="valeur"><?= (int) $classe["effectif"] ?></div>
        </div></div>
    </div>
    <div class="col-6 col-sm-4">
        <div class="card carte-stat shadow-sm"><div class="card-body">
            <div class="text-muted small">Garçons</div>
            <div class="valeur"><?= (int) $garcons ?></div>
        </div></div>
    </div>
    <div class="col-6 col-sm-4">
        <div class="card carte-stat shadow-sm"><div class="card-body">
            <div class="text-muted small">Filles</div>
            <div class="valeur"><?= (int) $filles ?></div>
        </div></div>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover align-middle bg-white shadow-sm">
        <thead class="table-primary">
            <tr><th>N°</th><th>Matricule</th><th>Nom</th><th>Prénom</th><th>Sexe</th><th>Téléphone</th><th class="text-end d-print-none">Actions</th></tr>
        </thead>
        <tbody>
        <?php if (empty($etudiants)): ?>
            <tr><td colspan="7" class="text-center text-muted py-4">Aucun étudiant dans cette classe.</td></tr>
        <?php endif; ?>
        <?php foreach ($etudiants as $i => $etu): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= e($etu["matricule"]) ?></td>
                <td class="fw-semibold"><?= e($etu["nom"]) ?></td>
                <td><?= e($etu["prenom"]) ?></td>
                <td><?= $etu["sexe"] === "Feminin" ? "F" : ($etu["sexe"] === "Masculin" ? "M" : "—") ?></td>
                <td><?= e($etu["tel"] ?? "—") ?></td>
                <td class="text-end text-nowrap d-print-none">
                    <a href="<?= BASE_URL ?>/index.php?action=notes&amp;id=<?= (int) $etu["id"] ?>" class="btn btn-sm btn-outline-primary">Notes</a>
                    <a href="<?= BASE_URL ?>/index.php?action=bulletin&amp;id=<?= (int) $etu["id"] ?>" class="btn btn-sm btn-outline-success">Bulletin</a>
                    <a href="<?= BASE_URL ?>/index.php?action=modifier&amp;id=<?= (int) $etu["id"] ?>" class="btn btn-sm btn-outline-secondary">Modifier</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
$contenu = ob_get_clean();
require RACINE . "/app/views/layout.php";
