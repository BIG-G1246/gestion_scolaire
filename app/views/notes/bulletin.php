<?php
// app/views/notes/bulletin.php
// Variables reçues du contrôleur : $titre, $etudiant, $classe, $grille, $moyenne, $mention, $rang, $effectif
ob_start();
?>
<style>
    /* À l'impression : on cache le menu, les boutons, le pied de page et le message flash. */
    @media print {
        nav, footer, .no-print, .alert { display: none !important; }
        body { background: #fff !important; }
        .card { box-shadow: none !important; border: 1px solid #000 !important; }
    }
</style>

<div class="d-flex flex-wrap justify-content-end gap-2 mb-3 no-print">
    <a href="<?= BASE_URL ?>/index.php?action=notes&amp;id=<?= (int) $etudiant["id"] ?>" class="btn btn-outline-primary">✏️ Saisir les notes</a>
    <button type="button" class="btn btn-primary" onclick="window.print()">🖨️ Imprimer</button>
    <a href="<?= BASE_URL ?>/index.php?action=liste" class="btn btn-link">Retour à la liste</a>
</div>

<div class="card shadow-sm border-0 mx-auto" style="max-width: 800px;">
    <div class="card-body p-4">
        <div class="text-center border-bottom pb-3 mb-3">
            <div class="fw-bold" style="color: #0b3d91;">🎓 Gestion Scolaire</div>
            <h1 class="h4 mb-0">Bulletin de notes</h1>
            <small class="text-muted">Année scolaire <?= date("n") >= 9 ? date("Y") . "-" . (date("Y") + 1) : (date("Y") - 1) . "-" . date("Y") ?></small>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-sm-6"><span class="text-muted">Nom :</span> <strong><?= e($etudiant["nom"] . " " . $etudiant["prenom"]) ?></strong></div>
            <div class="col-sm-6"><span class="text-muted">Matricule :</span> <strong><?= e($etudiant["matricule"]) ?></strong></div>
            <div class="col-sm-6"><span class="text-muted">Classe :</span> <strong><?= $classe !== null ? e($classe["nom"]) : "Non affecté" ?></strong></div>
            <?php if ($rang !== null): ?>
                <div class="col-sm-6"><span class="text-muted">Rang :</span> <strong><?= (int) $rang ?><?= $rang === 1 ? "er" : "e" ?> sur <?= (int) $effectif ?></strong></div>
            <?php endif; ?>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-3">
                <thead class="table-light">
                    <tr>
                        <th>Matière</th>
                        <th class="text-center">Coef.</th>
                        <th class="text-center">Note / 20</th>
                        <th class="text-center">Note × coef.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $total_coef = 0; $total_points = 0.0; ?>
                    <?php foreach ($grille as $ligne): ?>
                        <?php
                        $note = $ligne["valeur"] === null ? null : (float) $ligne["valeur"];
                        if ($note !== null) {
                            $total_coef   += (int) $ligne["coefficient"];
                            $total_points += $note * (int) $ligne["coefficient"];
                        }
                        ?>
                        <tr>
                            <td><?= e($ligne["matiere"]) ?></td>
                            <td class="text-center"><?= (int) $ligne["coefficient"] ?></td>
                            <td class="text-center <?= $note !== null && $note < 10 ? "text-danger fw-semibold" : "" ?>"><?= note_fr($note) ?></td>
                            <td class="text-center"><?= $note === null ? "—" : note_fr($note * (int) $ligne["coefficient"]) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th>Total (matières notées)</th>
                        <th class="text-center"><?= (int) $total_coef ?></th>
                        <th></th>
                        <th class="text-center"><?= note_fr($total_points) ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 p-3 rounded" style="background: #eef3fb;">
            <div>
                <div class="text-muted small">Moyenne générale</div>
                <div class="fs-3 fw-bold" style="color: #0b3d91;"><?= note_fr($moyenne) ?> <small class="fs-6 text-muted">/ 20</small></div>
            </div>
            <div class="text-end">
                <div class="text-muted small">Mention</div>
                <span class="badge fs-6 text-bg-<?= e($mention["couleur"]) ?>"><?= e($mention["texte"]) ?></span>
            </div>
        </div>

        <p class="text-muted small mt-3 mb-0">Édité le <?= date("d/m/Y") ?>. Moyenne = somme (note × coefficient) ÷ somme des coefficients des matières notées.</p>
    </div>
</div>
<?php
$contenu = ob_get_clean();
require RACINE . "/app/views/layout.php";
