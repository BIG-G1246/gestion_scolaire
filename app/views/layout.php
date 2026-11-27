<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titre) ?> | Gestion Scolaire</title>
    <link href="<?= BASE_URL ?>/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --ecole: #0b3d91; --ecole-fonce: #082c69; --largeur-menu: 250px; }
        .carte-stat { border: 0; border-left: 4px solid var(--ecole); }
        .carte-stat .valeur { font-size: 1.8rem; font-weight: 700; color: var(--ecole); line-height: 1.1; }
        .barre { height: .6rem; }

        /* ----- Menu latéral (sidebar) ----- */
        .menu-lateral { background: linear-gradient(180deg, var(--ecole), var(--ecole-fonce)); color: #fff; }
        .menu-lateral .titre-section { font-size: .7rem; letter-spacing: .08em; text-transform: uppercase; color: rgba(255,255,255,.55); padding: 1rem 1rem .35rem; }
        .menu-lateral .nav-link { color: rgba(255,255,255,.85); border-radius: .5rem; padding: .55rem .85rem; margin: .1rem .5rem; display: flex; gap: .6rem; align-items: center; }
        .menu-lateral .nav-link:hover { background: rgba(255,255,255,.12); color: #fff; }
        .menu-lateral .nav-link.active { background: #fff; color: var(--ecole); font-weight: 600; }
        .menu-lateral .icone { width: 1.4rem; text-align: center; }
        .barre-haut { background: var(--ecole); }

        /* Sur grand écran (992 px et plus), le menu est fixé à gauche et le contenu est décalé */
        @media (min-width: 992px) {
            .menu-lateral { position: fixed; top: 0; bottom: 0; left: 0; width: var(--largeur-menu); overflow-y: auto; }
            .avec-menu { margin-left: var(--largeur-menu); }
        }
        @media print { .menu-lateral, .barre-haut { display: none !important; } .avec-menu { margin-left: 0 !important; } }
    </style>
</head>
<body class="bg-light">
<?php
// Menu selon le rôle. Chaque section : titre => [action => [icône, libellé]].
$action_courante = $_GET["action"] ?? "accueil";
// Pages "enfants" : elles allument l'entrée de menu de leur parent
$parents = ["classe" => "classes", "formulaire" => "liste", "modifier" => "liste", "notes" => "liste", "bulletin" => "liste", "acces" => "liste", "matiere_formulaire" => "matieres"];
$action_menu = $parents[$action_courante] ?? $action_courante;

$sections = [];
if (est_etudiant()) {
    $sections["Mon espace"] = [
        "mon_espace"          => ["🏠", "Accueil"],
        "mon_emploi_du_temps" => ["🗓️", "Emploi du temps"],
        "mon_bulletin"        => ["📄", "Mes bulletins"],
    ];
} elseif (est_connecte()) {
    $sections["Général"] = [
        "tableau_bord" => ["📊", "Tableau de bord"],
    ];
    $sections["Scolarité"] = [
        "liste"           => ["👥", "Étudiants"],
        "classes"         => ["🏫", "Classes"],
        "emploi_du_temps" => ["🗓️", "Emploi du temps"],
        "compositions"    => ["📝", "Compositions"],
    ];
    if (est_admin()) {
        $sections["Direction"] = [
            "matieres" => ["📚", "Matières"],
            "caisse"   => ["💰", "Caisse"],
            "journal"  => ["🕵️", "Journal d'activité"],
            "comptes"  => ["🔐", "Comptes du personnel"],
        ];
    }
}
?>
<?php if (est_connecte()): ?>
    <!-- Barre du haut : visible seulement sur téléphone et tablette, avec le bouton ☰ qui ouvre le menu -->
    <nav class="navbar navbar-dark barre-haut d-lg-none shadow-sm">
        <div class="container-fluid">
            <button class="btn btn-outline-light" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuLateral"
                    aria-controls="menuLateral" aria-label="Ouvrir le menu">☰</button>
            <a class="navbar-brand fw-bold mx-auto" href="<?= BASE_URL ?>/index.php?action=accueil">🎓 Gestion Scolaire</a>
        </div>
    </nav>

    <!-- Menu latéral : fixe à gauche sur grand écran, panneau coulissant (offcanvas) sur petit écran -->
    <aside class="offcanvas-lg offcanvas-start menu-lateral d-flex flex-column" tabindex="-1" id="menuLateral" aria-label="Menu principal">
        <div class="d-flex align-items-center justify-content-between px-3 py-3 border-bottom border-light border-opacity-25">
            <a class="text-white text-decoration-none fw-bold fs-5" href="<?= BASE_URL ?>/index.php?action=accueil">🎓 Gestion Scolaire</a>
            <button type="button" class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#menuLateral" aria-label="Fermer"></button>
        </div>

        <nav class="flex-grow-1 pb-3">
            <?php foreach ($sections as $titre_section => $liens): ?>
                <div class="titre-section"><?= e($titre_section) ?></div>
                <ul class="nav flex-column">
                    <?php foreach ($liens as $cle => [$icone, $libelle]): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $cle === $action_menu ? "active" : "" ?>"
                               <?= $cle === $action_menu ? 'aria-current="page"' : "" ?>
                               href="<?= BASE_URL ?>/index.php?action=<?= e($cle) ?>">
                                <span class="icone"><?= $icone ?></span><?= e($libelle) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endforeach; ?>
        </nav>

        <!-- Utilisateur connecté et déconnexion, en bas du menu -->
        <div class="p-3 border-top border-light border-opacity-25">
            <div class="small mb-2">
                👤 <?= e($_SESSION["nom_complet"] ?? $_SESSION["utilisateur"]) ?>
                <span class="badge text-bg-light ms-1"><?= e(libelle_role($_SESSION["role"])) ?></span>
            </div>
            <form action="<?= BASE_URL ?>/index.php?action=deconnexion" method="post" class="m-0">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <button type="submit" class="btn btn-sm btn-outline-light w-100">Se déconnecter</button>
            </form>
        </div>
    </aside>
<?php endif; ?>

    <div class="<?= est_connecte() ? "avec-menu" : "" ?> d-flex flex-column min-vh-100">
        <main class="container-fluid px-3 px-lg-4 my-4 flex-grow-1" style="max-width: 1320px;">
            <?php if (isset($_SESSION["flash"])): ?>
                <div class="alert alert-<?= e($_SESSION["flash"]["type"]) ?> alert-dismissible fade show" role="alert">
                    <?= e($_SESSION["flash"]["message"]) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                </div>
                <?php unset($_SESSION["flash"]); ?>
            <?php endif; ?>

            <?= $contenu ?>
        </main>

        <footer class="text-center text-muted py-3 border-top">
            <small>© <?= date("Y") ?> Gestion Scolaire</small>
        </footer>
    </div>

    <script src="<?= BASE_URL ?>/js/bootstrap.bundle.min.js"></script>
</body>
</html>
