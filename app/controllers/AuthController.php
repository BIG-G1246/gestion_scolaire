<?php
class AuthController
{
    private MembreModel $membres;

    public function __construct(MembreModel $membres)
    {
        $this->membres = $membres;
    }

    public function connexion(): void
    {
        if (est_connecte()) {
            rediriger("liste");
        }

        $erreur = "";
        $login = "";
        $expire = isset($_GET["expire"]);
        $deconnecte = isset($_GET["deconnecte"]);

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            csrf_verifier();

            $login = post("login");
            $mdp = $_POST["mdp"] ?? "";
            $membre = $login !== "" ? $this->membres->parLogin($login) : null;

            if ($membre && is_string($mdp) && password_verify($mdp, $membre["mdp_hash"])) {
                session_regenerate_id(true);
                $_SESSION["utilisateur"] = $membre["login"];
                $_SESSION["role"] = $membre["role"];
                $_SESSION["dernier"] = time();
                flash("Bienvenue " . $membre["login"] . ".");
                rediriger("liste");
            }

            $erreur = "Identifiants incorrects.";
        }

        require RACINE . "/app/views/connexion.php";
    }

    public function deconnexion(): void
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            rediriger("liste");
        }
        csrf_verifier();

        $_SESSION = [];
        session_destroy();
        rediriger("connexion", ["deconnecte" => 1]);
    }
}