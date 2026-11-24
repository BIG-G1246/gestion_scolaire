<?php
function e(?string $texte): string
{
    return htmlspecialchars((string) $texte, ENT_QUOTES, "UTF-8");
}

function post(string $cle): string
{
    $valeur = $_POST[$cle] ?? "";
    return is_string($valeur) ? trim($valeur) : "";
}

function flash(string $message, string $type = "success"): void
{
    $_SESSION["flash"] = ["message" => $message, "type" => $type];
}

function rediriger(string $action, array $params = []): void
{
    $query = http_build_query(array_merge(["action" => $action], $params));
    header("Location: " . BASE_URL . "/index.php?" . $query);
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION["csrf"])) {
        $_SESSION["csrf"] = bin2hex(random_bytes(16));
    }
    return $_SESSION["csrf"];
}

function csrf_verifier(): void
{
    if (!hash_equals(csrf_token(), post("csrf"))) {
        http_response_code(403);
        exit("Requête refusée.");
    }
}

function est_connecte(): bool
{
    return isset($_SESSION["utilisateur"]);
}

function est_admin(): bool
{
    return ($_SESSION["role"] ?? "") === "admin";
}

function exiger_connexion(): void
{
    if (!est_connecte()) {
        rediriger("connexion");
    }
}

function exiger_admin(): void
{
    exiger_connexion();
    if (!est_admin()) {
        refuser();
    }
}

function refuser(): void
{
    http_response_code(403);
    require RACINE . "/app/views/403.php";
    exit;
}

function verifier_expiration(int $secondes = 1200): void
{
    if (isset($_SESSION["dernier"]) && time() - $_SESSION["dernier"] > $secondes) {
        $_SESSION = [];
        session_destroy();
        rediriger("connexion", ["expire" => 1]);
    }
    if (est_connecte()) {
        $_SESSION["dernier"] = time();
    }
}

function televerser_photo(?array $fichier): array
{
    $taille_max = 2 * 1024 * 1024;

    if ($fichier === null || ($fichier["error"] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ["nom" => null, "erreur" => null];
    }

    $code = $fichier["error"];

    if ($code === UPLOAD_ERR_INI_SIZE || $code === UPLOAD_ERR_FORM_SIZE) {
        return ["nom" => null, "erreur" => "La photo est trop lourde (2 Mo maximum)."];
    }
    if ($code !== UPLOAD_ERR_OK) {
        return ["nom" => null, "erreur" => "L'envoi de la photo a échoué. Réessayez."];
    }
    if ($fichier["size"] > $taille_max) {
        return ["nom" => null, "erreur" => "La photo est trop lourde (2 Mo maximum)."];
    }

    $type = mime_content_type($fichier["tmp_name"]) ?: "";
    $extensions = ["image/jpeg" => "jpg", "image/png" => "png", "image/webp" => "webp"];

    if (!isset($extensions[$type]) || getimagesize($fichier["tmp_name"]) === false) {
        return ["nom" => null, "erreur" => "Seules les images JPG, PNG ou WebP sont acceptées."];
    }

    $nom = "photo_" . bin2hex(random_bytes(8)) . "." . $extensions[$type];

    if (!move_uploaded_file($fichier["tmp_name"], RACINE . "/public/uploads/" . $nom)) {
        return ["nom" => null, "erreur" => "Impossible d'enregistrer la photo sur le serveur."];
    }

    return ["nom" => $nom, "erreur" => null];
}

function supprimer_fichier_photo(?string $nom): void
{
    if ($nom === null || !preg_match('/^photo_[a-f0-9]{16}\.(jpg|png|webp)$/', $nom)) {
        return;
    }

    $chemin = RACINE . "/public/uploads/" . $nom;
    if (is_file($chemin)) {
        unlink($chemin);
    }
}