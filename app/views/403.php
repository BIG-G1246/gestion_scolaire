<?php
$titre = "Accès refusé";
ob_start();
?>
<div class="text-center py-5">
    <h1 class="display-4">403</h1>
    <p class="lead">Vous n'avez pas les droits nécessaires pour cette action.</p>
    <a href="<?= BASE_URL ?>/index.php?action=accueil" class="btn btn-primary">Retour à l'accueil</a>
</div>
<?php
$contenu = ob_get_clean();
require RACINE . "/app/views/layout.php";