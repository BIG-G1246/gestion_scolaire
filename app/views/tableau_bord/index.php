<?php
// app/views/tableau_bord/index.php
// Partie commune : secrétaire ET directeur. Partie "Pilotage" : directeur seulement (est_admin()).
ob_start();

$total   = (int) $effectifs["total"];
$filles  = (int) $effectifs["filles"];
$garcons = (int) $effectifs["garcons"];
$pct     = fn(int|float $part, int|float $tout): int => $tout > 0 ? (int) round($part * 100 / $tout) : 0;
$classes_notes = (int) $resultats["classes"];
$admis   = (int) $resultats["admis"];
$moy_etab = $resultats["moyenne"] !== null ? (float) $resultats["moyenne"] : null;
$max_mois = max(array_merge([1], array_column($par_mois, "nombre")));
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h3 mb-0"><?= e($titre) ?></h1>
        <small class="text-muted">Situation au <?= date("d/m/Y") ?> · résultats du trimestre <?= (int) $trimestre ?></small>
    </div>
    <ul class="nav nav-pills">
        <?php for ($t = 1; $t <= 3; $t++): ?>
            <li class="nav-item">
                <a class="nav-link <?= $t === $trimestre ? "active" : "" ?>" href="<?= BASE_URL ?>/index.php?action=tableau_bord&amp;t=<?= $t ?>">Trimestre <?= $t ?></a>
            </li>
        <?php endfor; ?>
    </ul>
</div>

<!-- Chiffres clés -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg">
        <div class="card carte-stat shadow-sm h-100"><div class="card-body">
            <div class="text-muted small">Étudiants inscrits</div>
            <div class="valeur"><?= $total ?></div>
            <div class="small text-muted"><?= (int) $effectifs["sans_classe"] ?> sans classe</div>
        </div></div>
    </div>
    <div class="col-6 col-lg">
        <div class="card carte-stat shadow-sm h-100"><div class="card-body">
            <div class="text-muted small">Filles / Garçons</div>
            <div class="valeur"><?= $filles ?> <span class="text-muted fs-5">/</span> <?= $garcons ?></div>
            <div class="progress barre mt-2" role="progressbar" aria-label="Part des filles">
                <div class="progress-bar" style="width: <?= $pct($filles, $total) ?>%; background-color: #d63384;"></div>
                <div class="progress-bar" style="width: <?= $pct($garcons, $total) ?>%;"></div>
            </div>
            <div class="small text-muted mt-1"><?= $pct($filles, $total) ?> % de filles</div>
        </div></div>
    </div>
    <div class="col-6 col-lg">
        <div class="card carte-stat shadow-sm h-100"><div class="card-body">
            <div class="text-muted small">Moyenne de l'établissement</div>
            <div class="valeur"><?= note_fr($moy_etab) ?></div>
            <span class="badge text-bg-<?= e(mention($moy_etab)["couleur"]) ?>"><?= e(mention($moy_etab)["texte"]) ?></span>
        </div></div>
    </div>
    <div class="col-6 col-lg">
        <div class="card carte-stat shadow-sm h-100"><div class="card-body">
            <div class="text-muted small">Taux de réussite (≥ 10)</div>
            <div class="valeur"><?= $pct($admis, $classes_notes) ?> %</div>
            <div class="small text-muted"><?= $admis ?> admis sur <?= $classes_notes ?> notés</div>
        </div></div>
    </div>
    <?php if (est_admin()): ?>
        <?php $solde = (int) $caisse["entrees"] - (int) $caisse["sorties"]; ?>
        <div class="col-12 col-lg">
            <div class="card carte-stat shadow-sm h-100" style="border-left-color: <?= $solde >= 0 ? "#198754" : "#dc3545" ?>;"><div class="card-body">
                <div class="text-muted small">Solde de la caisse</div>
                <div class="valeur" style="color: <?= $solde >= 0 ? "#198754" : "#dc3545" ?>; font-size:1.5rem;"><?= e(fcfa($solde)) ?></div>
                <a class="small" href="<?= BASE_URL ?>/index.php?action=caisse">Voir la caisse →</a>
            </div></div>
        </div>
    <?php endif; ?>
</div>

<div class="row g-4">
    <!-- Statistiques par classe -->
    <div class="col-12 col-xl-7">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white fw-semibold">🏫 Statistiques par classe</div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Classe</th><th class="text-center">Effectif</th><th class="text-center">F / G</th><th class="text-center">Moyenne</th><th style="min-width:9rem;">Réussite</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($par_classe as $c): ?>
                            <?php $taux = $pct((int) $c["admis"], (int) $c["classes"]); $m = $c["moyenne"] !== null ? (float) $c["moyenne"] : null; ?>
                            <tr>
                                <td class="fw-semibold"><?= e($c["nom"]) ?></td>
                                <td class="text-center"><?= (int) $c["effectif"] ?></td>
                                <td class="text-center"><?= (int) $c["filles"] ?> / <?= (int) $c["garcons"] ?></td>
                                <td class="text-center"><span class="badge text-bg-<?= e(mention($m)["couleur"]) ?>"><?= note_fr($m) ?></span></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress barre flex-grow-1"><div class="progress-bar bg-success" style="width: <?= $taux ?>%"></div></div>
                                        <small><?= $taux ?> %</small>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Dernières inscriptions -->
    <div class="col-12 col-xl-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between">
                <span>🆕 Dernières inscriptions</span>
                <a class="small" href="<?= BASE_URL ?>/index.php?action=liste">Tous les étudiants →</a>
            </div>
            <ul class="list-group list-group-flush">
                <?php foreach ($inscriptions as $i): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold"><?= e($i["prenom"] . " " . $i["nom"]) ?></div>
                            <small class="text-muted"><?= e($i["matricule"]) ?> · <?= e($i["classe"] ?? "Non affecté") ?></small>
                        </div>
                        <span class="badge text-bg-light border"><?= e(date("d/m/Y", strtotime($i["inscrit_le"]))) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- Prochaines compositions -->
    <div class="col-12 col-xl-7">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between">
                <span>📝 Prochaines compositions</span>
                <a class="small" href="<?= BASE_URL ?>/index.php?action=compositions">Gérer →</a>
            </div>
            <?php if (empty($compositions)): ?>
                <div class="card-body text-muted">Aucune composition à venir.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <tbody>
                            <?php foreach ($compositions as $cp): ?>
                                <tr>
                                    <td class="ps-3 fw-semibold"><?= e(date_fr($cp["date_compo"])) ?></td>
                                    <td><?= e(heure_fr($cp["heure_debut"])) ?> – <?= e(heure_fr($cp["heure_fin"])) ?></td>
                                    <td><span class="badge text-bg-primary"><?= e($cp["classe"]) ?></span></td>
                                    <td><?= e($cp["matiere"]) ?></td>
                                    <td class="text-muted"><?= e($cp["salle"] ?? "") ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Inscriptions par mois -->
    <div class="col-12 col-xl-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white fw-semibold">📈 Inscriptions par mois</div>
            <div class="card-body">
                <?php if (empty($par_mois)): ?>
                    <p class="text-muted mb-0">Aucune inscription sur les 12 derniers mois.</p>
                <?php endif; ?>
                <?php foreach ($par_mois as $pm): ?>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <small class="text-muted" style="width:4.5rem;"><?= e(date("m/Y", strtotime($pm["mois"] . "-01"))) ?></small>
                        <div class="progress flex-grow-1" style="height: 1.1rem;">
                            <div class="progress-bar" style="width: <?= $pct((int) $pm["nombre"], $max_mois) ?>%; background-color: var(--ecole);"><?= (int) $pm["nombre"] ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <p class="small text-muted mb-0 mt-3">🔑 <?= (int) $acces ?> étudiant(s) sur <?= $total ?> ont un accès à l'espace étudiant.</p>
            </div>
        </div>
    </div>
</div>

<?php if (est_admin()): ?>
    <h2 class="h4 mt-5 mb-3">🧭 Pilotage de l'établissement <small class="text-muted fs-6">(directeur)</small></h2>
    <div class="row g-4">
        <!-- Caisse -->
        <div class="col-12 col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-semibold">💰 Caisse du mois</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between"><span>Entrées</span><strong class="text-success">+ <?= e(fcfa((int) $caisse["entrees_mois"])) ?></strong></div>
                    <div class="d-flex justify-content-between"><span>Sorties</span><strong class="text-danger">− <?= e(fcfa((int) $caisse["sorties_mois"])) ?></strong></div>
                    <hr>
                    <div class="d-flex justify-content-between small text-muted"><span>Total encaissé</span><span><?= e(fcfa((int) $caisse["entrees"])) ?></span></div>
                    <div class="d-flex justify-content-between small text-muted"><span>Total dépensé</span><span><?= e(fcfa((int) $caisse["sorties"])) ?></span></div>
                    <div class="mt-3 small">
                        <span class="badge text-bg-warning"><?= count($impayes) ?></span> étudiant(s) sans aucun paiement
                    </div>
                </div>
            </div>
        </div>

        <!-- Meilleurs élèves -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-semibold">🏆 Meilleurs élèves (T<?= (int) $trimestre ?>)</div>
                <ol class="list-group list-group-flush list-group-numbered">
                    <?php foreach ($meilleurs as $l): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="ms-2 me-auto">
                                <a href="<?= BASE_URL ?>/index.php?action=bulletin&amp;id=<?= (int) $l["id"] ?>&amp;t=<?= (int) $trimestre ?>" class="fw-semibold text-decoration-none"><?= e($l["prenom"] . " " . $l["nom"]) ?></a>
                                <div class="small text-muted"><?= e($l["classe"] ?? "Non affecté") ?></div>
                            </div>
                            <span class="badge text-bg-success"><?= note_fr((float) $l["moyenne"]) ?></span>
                        </li>
                    <?php endforeach; ?>
                    <?php if (empty($meilleurs)): ?><li class="list-group-item text-muted">Pas encore de notes.</li><?php endif; ?>
                </ol>
            </div>
        </div>

        <!-- Élèves en difficulté -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-semibold">⚠️ Élèves en difficulté (&lt; 10)</div>
                <ul class="list-group list-group-flush">
                    <?php foreach ($difficulte as $l): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div>
                                <a href="<?= BASE_URL ?>/index.php?action=bulletin&amp;id=<?= (int) $l["id"] ?>&amp;t=<?= (int) $trimestre ?>" class="fw-semibold text-decoration-none"><?= e($l["prenom"] . " " . $l["nom"]) ?></a>
                                <div class="small text-muted"><?= e($l["classe"] ?? "Non affecté") ?></div>
                            </div>
                            <span class="badge text-bg-danger"><?= note_fr((float) $l["moyenne"]) ?></span>
                        </li>
                    <?php endforeach; ?>
                    <?php if (empty($difficulte)): ?><li class="list-group-item text-muted">Aucun élève sous la moyenne. 🎉</li><?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- Résultats par matière -->
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-semibold">📚 Résultats par matière</div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light"><tr><th class="ps-3">Matière</th><th class="text-center">Coef.</th><th class="text-center">Moyenne</th><th>Réussite</th></tr></thead>
                        <tbody>
                            <?php foreach ($par_matiere as $m): ?>
                                <?php $taux = $pct((int) $m["admis"], (int) $m["nb_notes"]); $mm = $m["moyenne"] !== null ? (float) $m["moyenne"] : null; ?>
                                <tr>
                                    <td class="ps-3"><?= e($m["nom"]) ?></td>
                                    <td class="text-center"><?= (int) $m["coefficient"] ?></td>
                                    <td class="text-center"><span class="badge text-bg-<?= e(mention($mm)["couleur"]) ?>"><?= note_fr($mm) ?></span></td>
                                    <td style="min-width:8rem;"><div class="d-flex align-items-center gap-2"><div class="progress barre flex-grow-1"><div class="progress-bar bg-success" style="width: <?= $taux ?>%"></div></div><small><?= $taux ?> %</small></div></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sécurité et journal -->
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-semibold d-flex justify-content-between">
                    <span>🕵️ Activité récente</span>
                    <a class="small" href="<?= BASE_URL ?>/index.php?action=journal">Tout le journal →</a>
                </div>
                <div class="px-3 pt-3">
                    <span class="badge text-bg-success"><?= (int) $connexions["reussies"] ?> connexions</span>
                    <span class="badge <?= (int) $connexions["echecs"] > 0 ? "text-bg-danger" : "text-bg-secondary" ?>"><?= (int) $connexions["echecs"] ?> échecs de connexion</span>
                    <small class="text-muted">sur 7 jours</small>
                </div>
                <ul class="list-group list-group-flush small mt-2">
                    <?php foreach ($journal as $j): ?>
                        <li class="list-group-item">
                            <span class="text-muted"><?= e(date("d/m H:i", strtotime($j["cree_le"]))) ?></span>
                            · <strong><?= e($j["utilisateur"]) ?></strong> : <?= e($j["action"]) ?>
                        </li>
                    <?php endforeach; ?>
                    <?php if (empty($journal)): ?><li class="list-group-item text-muted">Aucune activité.</li><?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php
$contenu = ob_get_clean();
require RACINE . "/app/views/layout.php";
