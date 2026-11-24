<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion | Gestion Scolaire</title>
    <link href="<?= BASE_URL ?>/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --couleur-ecole: #0b3d91; }
        body { background: linear-gradient(135deg, var(--couleur-ecole), #1e6bd6); }
    </style>
</head>
<body class="min-vh-100 d-flex align-items-center justify-content-center p-3">

    <main class="card shadow-lg border-0 rounded-4 w-100" style="max-width: 420px;">
        <div class="card-body p-4 p-md-5">

            <div class="text-center mb-4">
                <div class="display-4 mb-2">🎓</div>
                <h1 class="h4 fw-bold mb-1" style="color: var(--couleur-ecole);">Connexion</h1>
                <p class="text-muted mb-0">Espace réservé au personnel</p>
            </div>

            <?php if ($expire): ?>
                <div class="alert alert-warning py-2 text-center">Votre session a expiré. Reconnectez-vous.</div>
            <?php elseif ($deconnecte): ?>
                <div class="alert alert-success py-2 text-center">Vous êtes déconnecté.</div>
            <?php endif; ?>

            <?php if ($erreur !== ""): ?>
                <div class="alert alert-danger py-2 text-center" role="alert"><?= e($erreur) ?></div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/index.php?action=connexion" method="post">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

                <div class="form-floating mb-3">
                    <input type="text" name="login" id="login" class="form-control"
                           placeholder="Identifiant" autocomplete="username" required maxlength="50"
                           value="<?= e($login) ?>" <?= $erreur === "" ? "autofocus" : "" ?>>
                    <label for="login">Identifiant</label>
                </div>

                <div class="form-floating mb-4">
                    <input type="password" name="mdp" id="mdp" class="form-control"
                           placeholder="Mot de passe" autocomplete="current-password" required
                           <?= $erreur !== "" ? "autofocus" : "" ?>>
                    <label for="mdp">Mot de passe</label>
                </div>

                <button type="submit" class="btn btn-lg w-100 text-white"
                        style="background-color: var(--couleur-ecole);">
                    Se connecter
                </button>
            </form>

        </div>
    </main>

</body>
</html>