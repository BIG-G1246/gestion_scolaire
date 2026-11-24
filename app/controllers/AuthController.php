<?php
class AuthController
{
    private MembreModel $membres;
    private EtudiantModel $etudiants;

    public function __construct(MembreModel $membres, EtudiantModel $etudiants)
    {
        $this->membres = $membres;
        $this->etudiants = $etudiants;
    }

    public function connexion(): void
    {
        if (est_connecte()) {
            rediriger("accueil");
        }

        $erreur = "";
        $login = "";
        $expire = isset($_GET["expire"]);
        $deconnecte = isset($_GET["deconnecte"]);

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            csrf_verifier();

            $login = post("login");
            $mdp = $_POST["mdp"] ?? "";
            $mdp = is_string($mdp) ? $mdp : "";

            // 1) Personnel (directeur ou secrétaire) : identifiant de la table membres
            $membre = $login !== "" ? $this->membres->parLogin($login) : null;
            if ($membre && password_verify($mdp, $membre["mdp_hash"])) {
                session_regenerate_id(true);
                $_SESSION["utilisateur"] = $membre["login"];
                $_SESSION["role"] = $membre["role"];
                $_SESSION["dernier"] = time();
                journaliser("Connexion réussie");
                flash("Bienvenue " . $membre["login"] . ".");
                rediriger("accueil");
            }

            // 2) Étudiant : son matricule, s'il a reçu un mot de passe (mdp_hash non NULL)
            $etudiant = (!$membre && $login !== "") ? $this->etudiants->parMatricule($login) : null;
            if ($etudiant && $etudiant["mdp_hash"] !== null && password_verify($mdp, $etudiant["mdp_hash"])) {
                session_regenerate_id(true);
                $_SESSION["utilisateur"] = $etudiant["matricule"];
                $_SESSION["nom_complet"] = $etudiant["prenom"] . " " . $etudiant["nom"];
                $_SESSION["etudiant_id"] = (int) $etudiant["id"];   // l'étudiant ne verra QUE cet id
                $_SESSION["role"] = "etudiant";
                $_SESSION["dernier"] = time();
                journaliser("Connexion réussie (espace étudiant)");
                flash("Bienvenue " . $etudiant["prenom"] . ".");
                rediriger("accueil");
            }

            journaliser("Échec de connexion avec l'identifiant « " . mb_substr($login, 0, 50) . " »", "visiteur", "-");
            $erreur = "Identifiants incorrects.";
        }

        require RACINE . "/app/views/connexion.php";
    }

    public function deconnexion(): void
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            rediriger("accueil");
        }
        csrf_verifier();

        journaliser("Déconnexion");
        $_SESSION = [];
        session_destroy();
        rediriger("connexion", ["deconnecte" => 1]);
    }
}