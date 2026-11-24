<?php
// Variables : $titre, $moi, $cours
ob_start();
$peut_supprimer = false;   // l'étudiant consulte seulement
$classe_id = (int) $moi["classe_id"];
?>
<h1 class="h3 mb-1">🗓️ Mon emploi du temps</h1>
<p class="text-muted"><?= e($moi["classe"] ?? "Classe non attribuée") ?></p>
<?php if ($moi["classe_id"] === null): ?>
    <div class="alert alert-info">Vous n'êtes pas encore affecté à une classe.</div>
<?php else: ?>
    <?php require RACINE . "/app/views/planning/_grille.php"; ?>
<?php endif; ?>
<?php
$contenu = ob_get_clean();
require RACINE . "/app/views/layout.php";
