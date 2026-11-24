<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titre) ?> | Gestion Scolaire</title>
    <link href="<?= BASE_URL ?>/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --ecole: #0b3d91; }
        .navbar-ecole { background-color: var(--ecole); }
        .carte-stat { border: 0; border-left: 4px solid var(--ecole); }
        .carte-stat .valeur { font-size: 1.8rem; font-weight: 700; color: var(--ecole); line-height: 1.1; }
        .barre { height: .6rem; }
        @media (min-width: 992px) { .navbar-ecole .nav-link { white-space: nowrap; font-size: .92rem; padding-left: .45rem !important; padding-right: .45rem !important; } }
    </style>
</head>
<body class="bg-light">
<?php
// Menu selon le rôle. Chaque entrée : action => libellé. L'entrée active est soulignée.
$action_courante = $_GET["action"] ?? "accueil";
$menu = [];
$menu_direction = [];
if (est_etudiant()) {
    $menu = [
        "mon_espace"          => "🏠 Mon espace",
        "mon_emploi_du_temps" => "🗓️ Emploi du temps",
        "mon_bulletin"        => "📄 Mes bulletins",
    ];
} elseif (est_connecte()) {
    $menu = [
        "tableau_bord"    => "📊 Tableau de bord",
        "liste"           => "👥 Étudiants",
        "emploi_du_temps" => "🗓️ Emploi du temps",
        "compositions"    => "📝 Compositions",
    ];
    if (est_admin()) {
        // Menu déroulant "Direction" : pages réservées au directeur
        $menu_direction = [
            "classes"  => "🏫 Classes",
            "matieres" => "📚 Matières",
            "caisse"   => "💰 Caisse",
            "journal"  => "🕵️ Journal d'activité",
            "comptes"  => "🔐 Comptes du personnel",
        ];
    }
}
?>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-ecole shadow-sm">
        <div class="container-fluid px-3 px-lg-4">
            <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>/index.php?action=accueil">🎓 Gestion Scolaire</a>

            <?php if (est_connecte()): ?>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu"
                        aria-controls="menu" aria-expanded="false" aria-label="Ouvrir le menu">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="menu">
                    <ul class="navbar-nav me-auto">
                        <?php foreach ($menu as $cle => $libelle): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= $cle === $action_courante ? "active fw-semibold" : "" ?>"
                                   href="<?= BASE_URL ?>/index.php?action=<?= e($cle) ?>"><?= e($libelle) ?></a>
                            </li>
                        <?php endforeach; ?>
                        <?php if (!empty($menu_direction)): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle <?= isset($menu_direction[$action_courante]) ? "active fw-semibold" : "" ?>"
                                   href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">🧭 Direction</a>
                                <ul class="dropdown-menu">
                                    <?php foreach ($menu_direction as $cle => $libelle): ?>
                                        <li><a class="dropdown-item <?= $cle === $action_courante ? "active" : "" ?>"
                                               href="<?= BASE_URL ?>/index.php?action=<?= e($cle) ?>"><?= e($libelle) ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        <?php endif; ?>
                    </ul>

                    <div class="d-flex align-items-center gap-2 py-2 py-lg-0">
                        <span class="text-white small text-nowrap">
                            👤 <?= e($_SESSION["nom_complet"] ?? $_SESSION["utilisateur"]) ?>
                            <span class="badge text-bg-light"><?= e(libelle_role($_SESSION["role"])) ?></span>
                        </span>
                        <form action="<?= BASE_URL ?>/index.php?action=deconnexion" method="post" class="m-0">
                            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                            <button type="submit" class="btn btn-sm btn-outline-light">Se déconnecter</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </nav>

    <main class="container-fluid px-3 px-lg-4 my-4" style="max-width: 1320px;">
        <?php if (isset($_SESSION["flash"])): ?>
            <div class="alert alert-<?= e($_SESSION["flash"]["type"]) ?> alert-dismissible fade show" role="alert">
                <?= e($_SESSION["flash"]["message"]) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
            <?php unset($_SESSION["flash"]); ?>
        <?php endif; ?>

        <?= $contenu ?>
    </main>

    <footer class="text-center text-muted py-4 mt-5 border-top">
        <small>© <?= date("Y") ?> Gestion Scolaire</small>
    </footer>

    <script src="<?= BASE_URL ?>/js/bootstrap.bundle.min.js"></script>
</body>
</html>
