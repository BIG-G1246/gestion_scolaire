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

// Modèles
require RACINE . "/app/models/EtudiantModel.php";
require RACINE . "/app/models/ClasseModel.php";
require RACINE . "/app/models/MembreModel.php";
require RACINE . "/app/models/MatiereModel.php";
require RACINE . "/app/models/NoteModel.php";
require RACINE . "/app/models/PlanningModel.php";
require RACINE . "/app/models/StatModel.php";
require RACINE . "/app/models/AdminModel.php";

// Contrôleurs
require RACINE . "/app/controllers/EtudiantController.php";
require RACINE . "/app/controllers/AuthController.php";
require RACINE . "/app/controllers/MatiereController.php";
require RACINE . "/app/controllers/NoteController.php";
require RACINE . "/app/controllers/TableauBordController.php";
require RACINE . "/app/controllers/PlanningController.php";
require RACINE . "/app/controllers/AdminController.php";
require RACINE . "/app/controllers/EspaceController.php";

verifier_expiration();

$controleur = new EtudiantController(new EtudiantModel($pdo), new ClasseModel($pdo));
$auth = new AuthController(new MembreModel($pdo), new EtudiantModel($pdo));

$action = $_GET["action"] ?? "accueil";

switch ($action) {
    // Page d'arrivée : chacun est envoyé vers SON espace
    case "accueil":
        if (!est_connecte()) {
            rediriger("connexion");
        }
        rediriger(est_etudiant() ? "mon_espace" : "tableau_bord");
        break;

    // Connexion
    case "connexion":
        $auth->connexion();
        break;
    case "deconnexion":
        $auth->deconnexion();
        break;

    // Personnel : étudiants
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
    case "acces":
        $controleur->acces();
        break;
    case "acces_enregistrer":
        $controleur->accesEnregistrer();
        break;

    // Personnel : tableau de bord, notes, emploi du temps, compositions
    case "tableau_bord":
        (new TableauBordController($pdo))->index();
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
    case "emploi_du_temps":
        (new PlanningController($pdo))->emploiDuTemps();
        break;
    case "cours_enregistrer":
        (new PlanningController($pdo))->coursEnregistrer();
        break;
    case "cours_supprimer":
        (new PlanningController($pdo))->coursSupprimer();
        break;
    case "compositions":
        (new PlanningController($pdo))->compositions();
        break;
    case "composition_enregistrer":
        (new PlanningController($pdo))->compositionEnregistrer();
        break;
    case "composition_supprimer":
        (new PlanningController($pdo))->compositionSupprimer();
        break;

    // Directeur uniquement
    case "matieres":
        (new MatiereController($pdo))->liste();
        break;
    case "matiere_formulaire":
        (new MatiereController($pdo))->formulaire();
        break;
    case "matiere_enregistrer":
        (new MatiereController($pdo))->enregistrer();
        break;
    case "matiere_supprimer":
        (new MatiereController($pdo))->supprimer();
        break;
    case "classes":
        (new AdminController($pdo))->classes();
        break;
    case "classe_enregistrer":
        (new AdminController($pdo))->classeEnregistrer();
        break;
    case "classe_supprimer":
        (new AdminController($pdo))->classeSupprimer();
        break;
    case "caisse":
        (new AdminController($pdo))->caisse();
        break;
    case "caisse_enregistrer":
        (new AdminController($pdo))->caisseEnregistrer();
        break;
    case "journal":
        (new AdminController($pdo))->journal();
        break;
    case "comptes":
        (new AdminController($pdo))->comptes();
        break;
    case "compte_enregistrer":
        (new AdminController($pdo))->compteEnregistrer();
        break;
    case "compte_supprimer":
        (new AdminController($pdo))->compteSupprimer();
        break;

    // Espace étudiant
    case "mon_espace":
        (new EspaceController($pdo))->accueil();
        break;
    case "mon_emploi_du_temps":
        (new EspaceController($pdo))->emploiDuTemps();
        break;
    case "mon_bulletin":
        (new EspaceController($pdo))->bulletin();
        break;

    default:
        http_response_code(404);
        require RACINE . "/app/views/404.php";
}
