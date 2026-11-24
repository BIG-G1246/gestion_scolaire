<?php
class EtudiantController
{
    private EtudiantModel $modele;
    private ClasseModel $classes;

    public function __construct(EtudiantModel $modele, ClasseModel $classes)
    {
        $this->modele = $modele;
        $this->classes = $classes;
    }

    public function liste(): void
    {
        exiger_connexion();
        
        $titre = "Liste des étudiants";
        $etudiants = $this->modele->tous();
        require RACINE . "/app/views/etudiants/liste.php";
    }

    public function formulaire(array $erreurs = [], array $valeurs = [], int $id = 0): void
    {
        exiger_connexion();

        $titre = $id > 0 ? "Modifier l'étudiant" : "Nouvel étudiant";
        $classes = $this->classes->tous();
        $valeurs = array_merge([
            "matricule" => "", "nom" => "", "prenom" => "",
            "email" => "", "tel" => "", "sexe" => "", "photo" => "", "classe_id" => "",
        ], $valeurs);
        require RACINE . "/app/views/etudiants/formulaire.php";
    }

    public function modifier(): void
    {
        exiger_connexion();

        $id = (int) ($_GET["id"] ?? 0);
        $etudiant = $this->modele->parId($id);

        if (!$etudiant) {
            $this->introuvable();
            return;
        }
        $this->formulaire([], $etudiant, $id);
    }

    public function enregistrer(): void
    {
        exiger_connexion();

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            rediriger("liste");
        }

        // Formulaire plus gros que post_max_size : PHP vide alors $_POST et $_FILES
        if (empty($_POST) && (int) ($_SERVER["CONTENT_LENGTH"] ?? 0) > 0) {
            flash("Le fichier envoyé dépasse la taille autorisée par le serveur.", "danger");
            rediriger("formulaire");
        }

        csrf_verifier();

        $id = (int) post("id");
        $existant = $id > 0 ? $this->modele->parId($id) : null;
        if ($id > 0 && !$existant) {
            $this->introuvable();
            return;
        }
        $ancienne = $existant["photo"] ?? null;

        $matricule = strtoupper(post("matricule"));
        $nom       = post("nom");
        $prenom    = post("prenom");
        $email     = post("email");
        $tel       = post("tel");
        $sexe      = post("sexe");
        $classeId  = (int) post("classe_id");

        $erreurs = [];
        if (!preg_match('/^[A-Z0-9]{3,20}$/', $matricule)) {
            $erreurs[] = "Le matricule est obligatoire : 3 à 20 lettres ou chiffres, sans espace.";
        }
        if ($nom === "" || mb_strlen($nom) > 50) {
            $erreurs[] = "Le nom est obligatoire (50 caractères au maximum).";
        }
        if ($prenom === "" || mb_strlen($prenom) > 50) {
            $erreurs[] = "Le prénom est obligatoire (50 caractères au maximum).";
        }
        if ($email !== "" && (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 120)) {
            $erreurs[] = "L'adresse e-mail est invalide.";
        }
        if (mb_strlen($tel) > 30) {
            $erreurs[] = "Le téléphone est trop long (30 caractères au maximum).";
        }
        if ($sexe !== "" && !in_array($sexe, ["Masculin", "Feminin"], true)) {
            $erreurs[] = "Le sexe choisi est invalide.";
        }
        if ($classeId > 0 && !$this->classes->parId($classeId)) {
            $erreurs[] = "La classe choisie n'existe pas.";
        }

        // La photo : vérifiée et déplacée par la fonction du helper
        $envoi = televerser_photo($_FILES["photo"] ?? null);
        if ($envoi["erreur"] !== null) {
            $erreurs[] = $envoi["erreur"];
        }
        $nouvelle = $envoi["nom"];

        if ($nouvelle !== null) {
            $photo = $nouvelle;
        } elseif (post("retirer_photo") === "1") {
            $photo = null;
        } else {
            $photo = $ancienne;
        }

        $donnees = [
            "matricule" => $matricule,
            "nom"       => $nom,
            "prenom"    => $prenom,
            "email"     => $email !== "" ? $email : null,
            "tel"       => $tel !== "" ? $tel : null,
            "sexe"      => $sexe !== "" ? $sexe : null,
            "photo"     => $photo,
            "classe_id" => $classeId > 0 ? $classeId : null,
        ];

        if (!empty($erreurs)) {
            supprimer_fichier_photo($nouvelle);
            $this->formulaire($erreurs, array_merge($donnees, ["photo" => $ancienne]), $id);
            return;
        }

        try {
            if ($id > 0) {
                $this->modele->mettreAJour($id, $donnees);
                journaliser("Étudiant modifié : " . $matricule);
                flash("Étudiant modifié.");
            } else {
                $this->modele->ajouter($donnees);
                journaliser("Étudiant inscrit : " . $matricule . " (" . $prenom . " " . $nom . ")");
                flash("Étudiant ajouté.");
            }
        } catch (PDOException $ex) {
            supprimer_fichier_photo($nouvelle);
            if (($ex->errorInfo[1] ?? 0) === 1062) {
                $this->formulaire(["Ce matricule existe déjà."], array_merge($donnees, ["photo" => $ancienne]), $id);
                return;
            }
            throw $ex;
        }

        // L'enregistrement a réussi : l'ancienne photo ne sert plus
        if ($ancienne !== null && $ancienne !== $photo) {
            supprimer_fichier_photo($ancienne);
        }

        rediriger("liste");
    }

    public function supprimer(): void
    {
        exiger_admin();

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            rediriger("liste");
        }
        csrf_verifier();

        $id = (int) post("id");
        $etudiant = $id > 0 ? $this->modele->parId($id) : null;

        if ($etudiant && $this->modele->supprimer($id)) {
            supprimer_fichier_photo($etudiant["photo"]);
            journaliser("Étudiant supprimé : " . $etudiant["matricule"]);
            flash("Étudiant supprimé.");
        } else {
            flash("Aucun étudiant n'a été supprimé.", "warning");
        }

        rediriger("liste");
    }

    /** action=acces&id=3 : page pour donner un mot de passe à l'étudiant (espace étudiant) */
    public function acces(array $erreurs = []): void
    {
        exiger_connexion();
        $id = (int) ($_GET["id"] ?? post("id"));
        $etudiant = $this->modele->parId($id);
        if (!$etudiant) {
            $this->introuvable();
            return;
        }
        $titre = "Accès à l'espace étudiant";
        require RACINE . "/app/views/etudiants/acces.php";
    }

    /** action=acces_enregistrer (POST) : définit ou retire le mot de passe */
    public function accesEnregistrer(): void
    {
        exiger_connexion();
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            rediriger("liste");
        }
        csrf_verifier();

        $id = (int) post("id");
        $etudiant = $this->modele->parId($id);
        if (!$etudiant) {
            $this->introuvable();
            return;
        }

        if (post("retirer") === "1") {
            $this->modele->definirMotDePasse($id, null);
            journaliser("Accès étudiant retiré : " . $etudiant["matricule"]);
            flash("L'étudiant ne peut plus se connecter.", "warning");
            rediriger("liste");
        }

        // Pas de post() ici : on ne retire pas les espaces d'un mot de passe.
        $mdp = is_string($_POST["mdp"] ?? null) ? $_POST["mdp"] : "";
        $confirmation = is_string($_POST["confirmation"] ?? null) ? $_POST["confirmation"] : "";

        $erreurs = [];
        if (mb_strlen($mdp) < 8) {
            $erreurs[] = "Le mot de passe doit contenir au moins 8 caractères.";
        }
        if ($mdp !== $confirmation) {
            $erreurs[] = "Les deux mots de passe ne sont pas identiques.";
        }
        if (!empty($erreurs)) {
            $_GET["id"] = $id;
            $this->acces($erreurs);
            return;
        }

        $this->modele->definirMotDePasse($id, password_hash($mdp, PASSWORD_DEFAULT));
        journaliser("Mot de passe étudiant défini : " . $etudiant["matricule"]);
        flash("Mot de passe enregistré. L'étudiant se connecte avec le matricule " . $etudiant["matricule"] . ".");
        rediriger("liste");
    }

    private function introuvable(): void
    {
        http_response_code(404);
        require RACINE . "/app/views/404.php";
    }
}