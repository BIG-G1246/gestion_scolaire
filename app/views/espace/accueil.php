<?php
// app/views/espace/accueil.php : tableau de bord de l'étudiant (seulement ce qui le concerne).
// Variables : $titre, $moi, $resultats (1..3 => moyenne, rang, effectif), $cours_du_jour, $compositions, $matieres
ob_start();
$t_courant = trimestre_demande();
?>
<div class="card border-0 shadow-sm mb-4 text-white" style="background: linear-gradient(135deg, #0b3d91, #1e6bd6);">
    <div class="card-body p-4 d-flex flex-wrap align-items-center gap-3">
        <?php if (!empty($moi["photo"])): ?>
            <img src="<?= BASE_URL ?>/uploads/<?= e($moi["photo"]) ?>" alt="" class="rounded-circle border border-3 border-white" style="width:72px;height:72px;object-fit:cover;">
        <?php else: ?>
            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-white fw-bold fs-4" style="width:72px;height:72px;color:#0b3d91;">
                <?= e(mb_strtoupper(mb_substr($moi["prenom"], 0, 1) . mb_substr($moi["nom"], 0, 1))) ?>
            </span>
        <?php endif; ?>
        <div>
            <h1 class="h3 mb-1">Bonjour <?= e($moi["prenom"]) ?> 👋</h1>
            <div class="opacity-75"><?= e($moi["matricule"]) ?> · <?= e($moi["classe"] ?? "Classe non attribuée") ?></div>
        </div>
    </div>
</div>

<!-- Résultats par trimestre -->
<div class="row g-3 mb-4">
    <?php for ($t = 1; $t <= 3; $t++): ?>
        <?php $r = $resultats[$t]; $men = mention($r["moyenne"]); ?>
        <div class="col-12 col-md-4">
            <div class="card carte-stat shadow-sm h-100 <?= $t === $t_courant ? "border-primary" : "" ?>"><div class="card-body">
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Trimestre <?= $t ?><?= $t === $t_courant ? " (en cours)" : "" ?></span>
                    <span class="badge text-bg-<?= e($men["couleur"]) ?>"><?= e($men["texte"]) ?></span>
                </div>
                <div class="valeur mt-1"><?= note_fr($r["moyenne"]) ?> <small class="fs-6 text-muted">/ 20</small></div>
                <div class="small">
                    <?php if ($r["rang"] !== null): ?>
                        🏅 Rang : <strong><?= (int) $r["rang"] ?><?= $r["rang"] === 1 ? "er" : "e" ?></strong> sur <?= (int) $r["effectif"] ?>
                    <?php else: ?>
                        <span class="text-muted">Pas encore de notes</span>
                    <?php endif; ?>
                </div>
                <?php if ($r["moyenne"] !== null): ?>
                    <a class="btn btn-sm btn-outline-primary mt-2" href="<?= BASE_URL ?>/index.php?action=mon_bulletin&amp;t=<?= $t ?>">📄 Voir le bulletin</a>
                <?php endif; ?>
            </div></div>
        </div>
    <?php endfor; ?>
</div>

<div class="row g-4">
    <!-- Cours du jour -->
    <div class="col-12 col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between">
                <span>📅 Aujourd'hui (<?= e(jours()[(int) date("N")] ?? "Dimanche") ?>)</span>
                <a class="small" href="<?= BASE_URL ?>/index.php?action=mon_emploi_du_temps">Semaine →</a>
            </div>
            <ul class="list-group list-group-flush">
                <?php foreach ($cours_du_jour as $c): ?>
                    <li class="list-group-item">
                        <div class="small text-primary fw-semibold"><?= e(heure_fr($c["heure_debut"])) ?> – <?= e(heure_fr($c["heure_fin"])) ?></div>
                        <div class="fw-semibold"><?= e($c["matiere"]) ?></div>
                        <small class="text-muted"><?= e(trim(($c["salle"] ?? "") . " · " . ($c["enseignant"] ?? ""), " ·")) ?></small>
                    </li>
                <?php endforeach; ?>
                <?php if (empty($cours_du_jour)): ?><li class="list-group-item text-muted">Pas de cours aujourd'hui.</li><?php endif; ?>
            </ul>
        </div>
    </div>

    <!-- Compositions à venir -->
    <div class="col-12 col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white fw-semibold">📝 Mes prochaines compositions</div>
            <ul class="list-group list-group-flush">
                <?php foreach ($compositions as $cp): ?>
                    <?php $jours_restants = (int) floor((strtotime($cp["date_compo"]) - strtotime(date("Y-m-d"))) / 86400); ?>
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-semibold"><?= e($cp["matiere"]) ?></div>
                            <small class="text-muted"><?= e(date_fr($cp["date_compo"])) ?> · <?= e(heure_fr($cp["heure_debut"])) ?> – <?= e(heure_fr($cp["heure_fin"])) ?><?= $cp["salle"] !== null ? " · " . e($cp["salle"]) : "" ?></small>
                        </div>
                        <span class="badge <?= $jours_restants <= 3 ? "text-bg-danger" : "text-bg-light border" ?>">
                            <?= $jours_restants === 0 ? "aujourd'hui" : "J-" . $jours_restants ?>
                        </span>
                    </li>
                <?php endforeach; ?>
                <?php if (empty($compositions)): ?><li class="list-group-item text-muted">Aucune composition programmée.</li><?php endif; ?>
            </ul>
        </div>
    </div>

    <!-- Matières -->
    <div class="col-12 col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white fw-semibold">📚 Mes matières</div>
            <ul class="list-group list-group-flush">
                <?php foreach ($matieres as $m): ?>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><?= e($m["nom"]) ?></span>
                        <span class="badge text-bg-primary">coef. <?= (int) $m["coefficient"] ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
<?php
$contenu = ob_get_clean();
require RACINE . "/app/views/layout.php";
