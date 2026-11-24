<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titre) ?> | Gestion Scolaire</title>
    <link href="<?= BASE_URL ?>/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background-color: #0b3d91;">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>/index.php?action=liste">🎓 Gestion Scolaire</a>
            <?php if (est_admin()): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/index.php?action=matieres">Matières</a>
                </li>
            <?php endif; ?>

            <?php if (est_connecte()): ?>
                <ul class="navbar-nav me-auto flex-row">
                    <li class="nav-item">
                        <a class="nav-link active" href="<?= BASE_URL ?>/index.php?action=liste">👥 Étudiants</a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-2">
                    <span class="text-white small">
                        👤 <?= e($_SESSION["utilisateur"]) ?>
                        <span class="badge text-bg-light"><?= e($_SESSION["role"]) ?></span>
                    </span>
                    <form action="<?= BASE_URL ?>/index.php?action=deconnexion" method="post" class="m-0">
                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                        <button type="submit" class="btn btn-sm btn-outline-light">Se déconnecter</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </nav>

    <main class="container my-4">
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