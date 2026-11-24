<?php
// Variables : $titre, $classes, $matieres, $classe, $classe_id, $cours, $erreurs, $saisie
ob_start();
$peut_supprimer = true;
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">🗓️ Emploi du temps</h1>
    <form method="get" class="d-flex gap-2">
        <input type="hidden" name="action" value="emploi_du_temps">
        <select name="classe" class="form-select" onchange="this.form.submit()" aria-label="Classe">
            <?php foreach ($classes as $c): ?>
                <option value="<?= (int) $c["id"] ?>" <?= (int) $c["id"] === $classe_id ? "selected" : "" ?>><?= e($c["nom"]) ?></option>
            <?php endforeach; ?>
        </select>
        <noscript><button class="btn btn-primary">Voir</button></noscript>
    </form>
</div>

<?php if ($classe === null): ?>
    <div class="alert alert-info">Créez d'abord une classe.</div>
<?php else: ?>
    <?php require RACINE . "/app/views/planning/_grille.php"; ?>

    <div class="card shadow-sm border-0 mt-4">
        <div class="card-body">
            <h2 class="h5">Ajouter un cours en <?= e($classe["nom"]) ?></h2>
            <?php require RACINE . "/app/views/_erreurs.php"; ?>
            <form method="post" action="<?= BASE_URL ?>/index.php?action=cours_enregistrer" class="row g-2 align-items-end">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="classe_id" value="<?= (int) $classe_id ?>">
                <div class="col-6 col-md-2">
                    <label class="form-label small" for="jour">Jour</label>
                    <select name="jour" id="jour" class="form-select">
                        <?php foreach (jours() as $num => $nom): ?>
                            <option value="<?= $num ?>" <?= (int) ($saisie["jour"] ?? 0) === $num ? "selected" : "" ?>><?= e($nom) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small" for="matiere_id">Matière</label>
                    <select name="matiere_id" id="matiere_id" class="form-select">
                        <?php foreach ($matieres as $m): ?>
                            <option value="<?= (int) $m["id"] ?>" <?= (int) ($saisie["matiere_id"] ?? 0) === (int) $m["id"] ? "selected" : "" ?>><?= e($m["nom"]) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small" for="heure_debut">Début</label>
                    <input type="time" name="heure_debut" id="heure_debut" class="form-control" required value="<?= e($saisie["heure_debut"] ?? "07:30") ?>">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small" for="heure_fin">Fin</label>
                    <input type="time" name="heure_fin" id="heure_fin" class="form-control" required value="<?= e($saisie["heure_fin"] ?? "09:30") ?>">
                </div>
                <div class="col-6 col-md-1">
                    <label class="form-label small" for="salle">Salle</label>
                    <input type="text" name="salle" id="salle" class="form-control" maxlength="30" value="<?= e($saisie["salle"] ?? "") ?>">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small" for="enseignant">Enseignant</label>
                    <input type="text" name="enseignant" id="enseignant" class="form-control" maxlength="80" value="<?= e($saisie["enseignant"] ?? "") ?>">
                </div>
                <div class="col-12 col-md-1 d-grid">
                    <button class="btn btn-primary">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>
<?php
$contenu = ob_get_clean();
require RACINE . "/app/views/layout.php";
