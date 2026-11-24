<?php
$titre = "Page introuvable";
ob_start();
?>
<div class="text-center py-5">
    <h1 class="display-4">404</h1>
    <p class="lead">Cette page n'existe pas.</p>
    <a href="<?= BASE_URL ?>/index.php?action=accueil" class="btn btn-primary">Retour à l'accueil</a>
</div>
<?php
$contenu = ob_get_clean();
require RACINE . "/app/views/layout.php";