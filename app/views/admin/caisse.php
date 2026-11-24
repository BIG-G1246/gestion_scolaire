<?php
// Variables : $titre, $totaux, $mouvements, $impayes, $etudiants, $erreurs, $saisie
ob_start();
$solde = (int) $totaux["entrees"] - (int) $totaux["sorties"];
?>
<h1 class="h3 mb-3">💰 Caisse de l'établissement</h1>

<div class="row g-3 mb-4">
    <div class="col-12 col-md-4"><div class="card carte-stat shadow-sm" style="border-left-color:#198754;"><div class="card-body">
        <div class="text-muted small">Total des entrées</div><div class="valeur text-success" style="font-size:1.5rem;"><?= e(fcfa((int) $totaux["entrees"])) ?></div>
        <small class="text-muted">Ce mois : <?= e(fcfa((int) $totaux["entrees_mois"])) ?></small>
    </div></div></div>
    <div class="col-12 col-md-4"><div class="card carte-stat shadow-sm" style="border-left-color:#dc3545;"><div class="card-body">
        <div class="text-muted small">Total des sorties</div><div class="valeur text-danger" style="font-size:1.5rem;"><?= e(fcfa((int) $totaux["sorties"])) ?></div>
        <small class="text-muted">Ce mois : <?= e(fcfa((int) $totaux["sorties_mois"])) ?></small>
    </div></div></div>
    <div class="col-12 col-md-4"><div class="card carte-stat shadow-sm"><div class="card-body">
        <div class="text-muted small">Solde</div><div class="valeur" style="font-size:1.5rem;"><?= e(fcfa($solde)) ?></div>
        <small class="text-muted">Entrées − sorties</small>
    </div></div></div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h2 class="h5">Nouvelle opération</h2>
                <?php require RACINE . "/app/views/_erreurs.php"; ?>
                <form method="post" action="<?= BASE_URL ?>/index.php?action=caisse_enregistrer">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <div class="btn-group w-100 mb-3" role="group" aria-label="Type">
                        <input type="radio" class="btn-check" name="type" id="entree" value="entree" <?= $saisie["type"] === "entree" ? "checked" : "" ?>>
                        <label class="btn btn-outline-success" for="entree">⬇️ Entrée</label>
                        <input type="radio" class="btn-check" name="type" id="sortie" value="sortie" <?= $saisie["type"] === "sortie" ? "checked" : "" ?>>
                        <label class="btn btn-outline-danger" for="sortie">⬆️ Sortie</label>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small" for="montant">Montant (FCFA)</label>
                        <input type="text" inputmode="numeric" name="montant" id="montant" class="form-control" required value="<?= e($saisie["montant"]) ?>" placeholder="75 000">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small" for="motif">Motif</label>
                        <input type="text" name="motif" id="motif" class="form-control" required maxlength="150" value="<?= e($saisie["motif"]) ?>" placeholder="Frais de scolarité 2e trimestre">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small" for="matricule">Matricule de l'étudiant (si paiement de scolarité)</label>
                        <input type="text" name="matricule" id="matricule" class="form-control" list="liste_matricules" value="<?= e($saisie["matricule"]) ?>">
                        <datalist id="liste_matricules">
                            <?php foreach ($etudiants as $et): ?><option value="<?= e($et["matricule"]) ?>"><?= e($et["prenom"] . " " . $et["nom"]) ?></option><?php endforeach; ?>
                        </datalist>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small" for="date_mvt">Date</label>
                        <input type="date" name="date_mvt" id="date_mvt" class="form-control" required value="<?= e($saisie["date_mvt"]) ?>">
                    </div>
                    <button class="btn btn-primary w-100">Enregistrer</button>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-semibold">⏳ Aucun paiement reçu (<?= count($impayes) ?>)</div>
            <ul class="list-group list-group-flush small">
                <?php foreach ($impayes as $i): ?>
                    <li class="list-group-item d-flex justify-content-between"><span><?= e($i["prenom"] . " " . $i["nom"]) ?></span><span class="text-muted"><?= e($i["classe"] ?? "—") ?></span></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="table-responsive">
            <table class="table table-hover align-middle bg-white shadow-sm">
                <thead class="table-light"><tr><th>Date</th><th>Motif</th><th>Étudiant</th><th class="text-end">Montant</th><th>Par</th></tr></thead>
                <tbody>
                    <?php if (empty($mouvements)): ?><tr><td colspan="5" class="text-center text-muted py-4">Aucune opération.</td></tr><?php endif; ?>
                    <?php foreach ($mouvements as $mv): ?>
                        <tr>
                            <td class="text-nowrap"><?= e(date_fr($mv["date_mvt"])) ?></td>
                            <td><?= e($mv["motif"]) ?></td>
                            <td class="small"><?= $mv["matricule"] !== null ? e($mv["prenom"] . " " . $mv["nom"]) : "<span class=\"text-muted\">—</span>" ?></td>
                            <td class="text-end text-nowrap fw-semibold <?= $mv["type"] === "entree" ? "text-success" : "text-danger" ?>">
                                <?= $mv["type"] === "entree" ? "+" : "−" ?> <?= e(fcfa((int) $mv["montant"])) ?>
                            </td>
                            <td class="small text-muted"><?= e($mv["cree_par"]) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
$contenu = ob_get_clean();
require RACINE . "/app/views/layout.php";
