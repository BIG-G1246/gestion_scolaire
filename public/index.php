<?php
ini_set("display_errors", "1");
error_reporting(E_ALL);

session_name("GESTION_SCOLAIRE");
session_set_cookie_params([
    "lifetime" => 0,
    "path"     => "/",
    "httponly" => true,
    "samesite" => "Lax",
]);
session_start();

require __DIR__ . "/../config/config.php";
require RACINE . "/app/helpers.php";
require RACINE . "/app/models/EtudiantModel.php";
require RACINE . "/app/models/ClasseModel.php";
require RACINE . "/app/models/MembreModel.php";
require RACINE . "/app/controllers/EtudiantController.php";
require RACINE . "/app/controllers/AuthController.php";
require_once RACINE . '/app/models/MatiereModel.php';
require_once RACINE . '/app/controllers/MatiereController.php';
require_once RACINE . '/app/models/NoteModel.php';
require_once RACINE . '/app/controllers/NoteController.php';

verifier_expiration();

$controleur = new EtudiantController(new EtudiantModel($pdo), new ClasseModel($pdo));
$auth = new AuthController(new MembreModel($pdo));

$action = $_GET["action"] ?? "liste";

switch ($action) {
    case "connexion":
        $auth->connexion();
        break;
    case "deconnexion":
        $auth->deconnexion();
        break;
    case "liste":
        $controleur->liste();
        break;
    case "formulaire":
        $controleur->formulaire();
        break;
    case "modifier":
        $controleur->modifier();
        break;
    case "enregistrer":
        $controleur->enregistrer();
        break;
    case "supprimer":
        $controleur->supprimer();
        break;
    case 'matieres':
        (new MatiereController($pdo))->liste();
        break;
    case 'matiere_formulaire':
        (new MatiereController($pdo))->formulaire();
        break;
    case 'matiere_enregistrer':
        (new MatiereController($pdo))->enregistrer();
        break;
    case 'matiere_supprimer':
        (new MatiereController($pdo))->supprimer();
        break;
    case "notes":
        (new NoteController($pdo))->saisie();
        break;
    case "notes_enregistrer":
        (new NoteController($pdo))->enregistrer();
        break;
    case "bulletin":
        (new NoteController($pdo))->bulletin();
        break;
    default:
        http_response_code(404);
        require RACINE . "/app/views/404.php";
}