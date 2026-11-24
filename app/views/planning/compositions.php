<?php
// Variables : $titre, $classes, $matieres, $compositions, $erreurs, $saisie
ob_start();
?>
<h1 class="h3 mb-3">📝 Compositions</h1>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <h2 class="h5">Programmer une composition</h2>
        <?php require RACINE . "/app/views/_erreurs.php"; ?>
        <form method="post" action="<?= BASE_URL ?>/index.php?action=composition_enregistrer" class="row g-2 align-items-end">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <div class="col-6 col-md-2">
                <label class="form-label small" for="classe_id">Classe</label>
                <select name="classe_id" id="classe_id" class="form-select">
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= (int) $c["id"] ?>" <?= (int) ($saisie["classe_id"] ?? 0) === (int) $c["id"] ? "selected" : "" ?>><?= e($c["nom"]) ?></option>
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
            <div class="col-4 col-md-1">
                <label class="form-label small" for="trimestre">Trim.</label>
                <select name="trimestre" id="trimestre" class="form-select">
                    <?php for ($t = 1; $t <= 3; $t++): ?>
                        <option value="<?= $t ?>" <?= (int) ($saisie["trimestre"] ?? trimestre_demande()) === $t ? "selected" : "" ?>><?= $t ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-8 col-md-2">
                <label class="form-label small" for="date_compo">Date</label>
                <input type="date" name="date_compo" id="date_compo" class="form-control" required value="<?= e($saisie["date_compo"] ?? "") ?>">
            </div>
            <div class="col-6 col-md-1">
                <label class="form-label small" for="heure_debut">Début</label>
                <input type="time" name="heure_debut" id="heure_debut" class="form-control" required value="<?= e($saisie["heure_debut"] ?? "08:00") ?>">
            </div>
            <div class="col-6 col-md-1">
                <label class="form-label small" for="heure_fin">Fin</label>
                <input type="time" name="heure_fin" id="heure_fin" class="form-control" required value="<?= e($saisie["heure_fin"] ?? "10:00") ?>">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small" for="salle">Salle</label>
                <input type="text" name="salle" id="salle" class="form-control" maxlength="30" value="<?= e($saisie["salle"] ?? "") ?>">
            </div>
            <div class="col-6 col-md-1 d-grid">
                <button class="btn btn-primary">Ajouter</button>
            </div>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover align-middle bg-white shadow-sm">
        <thead class="table-light">
            <tr><th>Date</th><th>Heure</th><th>Classe</th><th>Matière</th><th class="text-center">Trim.</th><th>Salle</th><th></th></tr>
        </thead>
        <tbody>
            <?php if (empty($compositions)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">Aucune composition programmée.</td></tr>
            <?php endif; ?>
            <?php foreach ($compositions as $cp): ?>
                <?php $passee = $cp["date_compo"] < date("Y-m-d"); ?>
                <tr class="<?= $passee ? "text-muted" : "" ?>">
                    <td class="fw-semibold"><?= e(date_fr($cp["date_compo"])) ?><?= $passee ? " <span class=\"badge text-bg-secondary\">passée</span>" : "" ?></td>
                    <td><?= e(heure_fr($cp["heure_debut"])) ?> – <?= e(heure_fr($cp["heure_fin"])) ?></td>
                    <td><span class="badge text-bg-primary"><?= e($cp["classe"]) ?></span></td>
                    <td><?= e($cp["matiere"]) ?></td>
                    <td class="text-center"><?= (int) $cp["trimestre"] ?></td>
                    <td><?= e($cp["salle"] ?? "") ?></td>
                    <td class="text-end">
                        <form method="post" action="<?= BASE_URL ?>/index.php?action=composition_supprimer" class="d-inline"
                              onsubmit="return confirm('Supprimer cette composition ?');">
                            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="id" value="<?= (int) $cp["id"] ?>">
                            <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
$contenu = ob_get_clean();
require RACINE . "/app/views/layout.php";
