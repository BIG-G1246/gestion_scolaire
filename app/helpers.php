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

function est_etudiant(): bool
{
    return ($_SESSION["role"] ?? "") === "etudiant";
}

/** Pages du personnel (secrétaire ou directeur) : un étudiant connecté reçoit un 403. */
function exiger_connexion(): void
{
    if (!est_connecte()) {
        rediriger("connexion");
    }
    if (est_etudiant()) {
        refuser();
    }
}

/** Pages de l'espace étudiant : réservées aux étudiants connectés. */
function exiger_etudiant(): void
{
    if (!est_connecte()) {
        rediriger("connexion");
    }
    if (!est_etudiant()) {
        refuser();
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

/**
 * Mention à partir d'une moyenne sur 20 (null = pas encore de note).
 * Renvoie le texte et une couleur Bootstrap pour le badge.
 */
function mention(?float $moyenne): array
{
    if ($moyenne === null) {
        return ["texte" => "Non classé", "couleur" => "secondary"];
    }
    if ($moyenne >= 16) {
        return ["texte" => "Très bien", "couleur" => "success"];
    }
    if ($moyenne >= 14) {
        return ["texte" => "Bien", "couleur" => "primary"];
    }
    if ($moyenne >= 12) {
        return ["texte" => "Assez bien", "couleur" => "info"];
    }
    if ($moyenne >= 10) {
        return ["texte" => "Passable", "couleur" => "warning"];
    }
    return ["texte" => "Insuffisant", "couleur" => "danger"];
}

/** Affiche une note ou une moyenne à la française : 12.50 devient "12,50". */
function note_fr(?float $valeur): string
{
    return $valeur === null ? "—" : number_format($valeur, 2, ",", " ");
}


/** Nom affiché du rôle (les valeurs en base restent admin / membre / etudiant). */
function libelle_role(string $role): string
{
    return ["admin" => "Directeur", "membre" => "Secrétaire", "etudiant" => "Étudiant"][$role] ?? $role;
}

/** Jours de la semaine utilisés par l'emploi du temps (1 = lundi). */
function jours(): array
{
    return [1 => "Lundi", 2 => "Mardi", 3 => "Mercredi", 4 => "Jeudi", 5 => "Vendredi", 6 => "Samedi"];
}

/** "07:30:00" devient "07h30". */
function heure_fr(?string $heure): string
{
    return $heure === null ? "" : str_replace(":", "h", substr($heure, 0, 5));
}

/** "2026-12-07" devient "07/12/2026". */
function date_fr(?string $date): string
{
    return $date === null ? "" : date("d/m/Y", strtotime($date));
}

/** Montant en FCFA : 75000 devient "75 000 FCFA". */
function fcfa(int|float $montant): string
{
    return number_format($montant, 0, ",", " ") . " FCFA";
}

/**
 * Trimestre demandé dans l'URL (?t=2), entre 1 et 3. Par défaut : le trimestre en cours
 * (septembre à décembre = 1, janvier à mars = 2, avril à juillet = 3).
 */
function trimestre_demande(): int
{
    $t = (int) ($_GET["t"] ?? 0);
    if ($t >= 1 && $t <= 3) {
        return $t;
    }
    $mois = (int) date("n");
    return $mois >= 9 ? 1 : ($mois <= 3 ? 2 : 3);
}

/**
 * Écrit une ligne dans le journal d'activité (lu par le directeur).
 * "global $pdo" : on réutilise la connexion créée dans config.php.
 */
function journaliser(string $action, ?string $utilisateur = null, ?string $role = null): void
{
    global $pdo;
    $req = $pdo->prepare(
        "INSERT INTO journal (utilisateur, role, action, ip) VALUES (:utilisateur, :role, :action, :ip)"
    );
    $req->execute([
        "utilisateur" => $utilisateur ?? ($_SESSION["utilisateur"] ?? "visiteur"),
        "role"        => $role ?? ($_SESSION["role"] ?? "-"),
        "action"      => mb_substr($action, 0, 255),
        "ip"          => $_SERVER["REMOTE_ADDR"] ?? null,
    ]);
}
