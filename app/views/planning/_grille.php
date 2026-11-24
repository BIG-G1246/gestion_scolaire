<?php
// app/views/planning/_grille.php : morceau de vue réutilisé (personnel et espace étudiant).
// Variables attendues : $cours (liste des cours d'une classe), $peut_supprimer (bool), $classe_id (int)
$par_jour = [];
foreach ($cours as $c) {
    $par_jour[(int) $c["jour"]][] = $c;
}
$aujourdhui = (int) date("N");
?>
<div class="row g-3">
    <?php foreach (jours() as $num => $nom_jour): ?>
        <div class="col-12 col-md-6 col-xl-2">
            <div class="card h-100 shadow-sm <?= $num === $aujourdhui ? "border-primary border-2" : "border-0" ?>">
                <div class="card-header fw-semibold <?= $num === $aujourdhui ? "text-bg-primary" : "bg-white" ?>">
                    <?= e($nom_jour) ?><?= $num === $aujourdhui ? " · aujourd'hui" : "" ?>
                </div>
                <div class="card-body p-2">
                    <?php if (empty($par_jour[$num])): ?>
                        <p class="text-muted small text-center my-3">Pas de cours</p>
                    <?php else: ?>
                        <?php foreach ($par_jour[$num] as $c): ?>
                            <div class="border rounded p-2 mb-2 bg-light">
                                <div class="small text-primary fw-semibold"><?= e(heure_fr($c["heure_debut"])) ?> – <?= e(heure_fr($c["heure_fin"])) ?></div>
                                <div class="fw-semibold"><?= e($c["matiere"]) ?></div>
                                <div class="small text-muted">
                                    <?= e($c["salle"] ?? "") ?><?= $c["salle"] !== null && $c["enseignant"] !== null ? " · " : "" ?><?= e($c["enseignant"] ?? "") ?>
                                </div>
                                <?php if ($peut_supprimer): ?>
                                    <form method="post" action="<?= BASE_URL ?>/index.php?action=cours_supprimer" class="mt-1"
                                          onsubmit="return confirm('Retirer ce cours ?');">
                                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $c["id"] ?>">
                                        <input type="hidden" name="classe_id" value="<?= (int) $classe_id ?>">
                                        <button class="btn btn-link btn-sm text-danger p-0">Retirer</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
