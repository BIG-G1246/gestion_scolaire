<?php
if (PHP_SAPI !== "cli") {
    http_response_code(403);
    exit("Réservé à la ligne de commande.");
}

require __DIR__ . "/../config/config.php";

function demander(string $question): string
{
    echo $question;
    return rtrim((string) fgets(STDIN), "\r\n");
}

$login = trim(demander("Identifiant : "));
$mdp   = demander("Mot de passe (10 caractères minimum) : ");
$role  = trim(demander("Rôle (membre ou admin) : "));

if ($login === "" || mb_strlen($login) > 50) {
    exit("Identifiant invalide.\n");
}
if (mb_strlen($mdp) < 10) {
    exit("Mot de passe trop court.\n");
}
if (!in_array($role, ["membre", "admin"], true)) {
    exit("Rôle invalide.\n");
}

$req = $pdo->prepare("INSERT INTO membres (login, mdp_hash, role) VALUES (:login, :hash, :role)");

try {
    $req->execute([
        "login" => $login,
        "hash"  => password_hash($mdp, PASSWORD_DEFAULT),
        "role"  => $role,
    ]);
    echo "Compte créé.\n";
} catch (PDOException $e) {
    echo (($e->errorInfo[1] ?? 0) === 1062) ? "Cet identifiant existe déjà.\n" : "Erreur : " . $e->getMessage() . "\n";
}