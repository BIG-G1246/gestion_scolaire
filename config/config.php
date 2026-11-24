<?php
define("BASE_URL", "/gestion_scolaire/public");
define("RACINE", dirname(__DIR__));

$hote = "localhost";
$base = "gestion_scolaire";
$user = "root";
$mdp  = "";

try {
    $pdo = new PDO(
        "mysql:host=$hote;dbname=$base;charset=utf8mb4",
        $user,
        $mdp,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die("Base indisponible.");
}