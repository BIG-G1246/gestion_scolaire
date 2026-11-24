<?php
// app/controllers/AdminController.php
// Pages du directeur uniquement : caisse, journal d'activité, comptes du personnel, classes.
// exiger_admin() en première ligne de CHAQUE méthode.

class AdminController
{
    private AdminModel $admin;
    private EtudiantModel $etudiants;

    public function __construct(PDO $pdo)
    {
        $this->admin     = new AdminModel($pdo);
        $this->etudiants = new EtudiantModel($pdo);
    }

    /* ---------- Caisse : l'argent qui entre et qui sort ---------- */

    /** action=caisse */
    public function caisse(array $erreurs = [], array $saisie = []): void
    {
        exiger_admin();
        $totaux     = $this->admin->totauxCaisse();
        $mouvements = $this->admin->mouvements();
        $impayes    = $this->admin->etudiantsSansPaiement();
        $etudiants  = $this->etudiants->tous();
        $saisie     = array_merge(["type" => "entree", "montant" => "", "motif" => "", "matricule" => "", "date_mvt" => date("Y-m-d")], $saisie);
        $titre      = "Caisse";
        require RACINE . "/app/views/admin/caisse.php";
    }

    /** action=caisse_enregistrer (POST) */
    public function caisseEnregistrer(): void
    {
        exiger_admin();
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            rediriger("caisse");
        }
        csrf_verifier();

        $saisie = [
            "type"      => post("type"),
            "montant"   => post("montant"),
            "motif"     => post("motif"),
            "matricule" => strtoupper(post("matricule")),
            "date_mvt"  => post("date_mvt"),
        ];
        $erreurs = [];

        if (!in_array($saisie["type"], ["entree", "sortie"], true)) {
            $erreurs[] = "Choisissez entrée ou sortie.";
        }
        // Les espaces sont acceptés dans le montant : "75 000" devient 75000.
        $montant = filter_var(str_replace(" ", "", $saisie["montant"]), FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
        if ($montant === false) {
            $erreurs[] = "Le montant doit être un nombre entier positif (en FCFA).";
        }
        if (mb_strlen($saisie["motif"]) < 3 || mb_strlen($saisie["motif"]) > 150) {
            $erreurs[] = "Le motif doit contenir entre 3 et 150 caractères.";
        }
        $date = DateTime::createFromFormat("Y-m-d", $saisie["date_mvt"]);
        if (!$date || $date->format("Y-m-d") !== $saisie["date_mvt"]) {
            $erreurs[] = "La date est invalide.";
        }
        $etudiant_id = null;
        if ($saisie["matricule"] !== "") {
            $etudiant = $this->etudiants->parMatricule($saisie["matricule"]);
            if ($etudiant === null) {
                $erreurs[] = "Aucun étudiant avec ce matricule.";
            } else {
                $etudiant_id = (int) $etudiant["id"];
            }
        }

        if (!empty($erreurs)) {
            $this->caisse($erreurs, $saisie);
            return;
        }

        $this->admin->ajouterMouvement([
            "type"        => $saisie["type"],
            "montant"     => $montant,
            "motif"       => $saisie["motif"],
            "etudiant_id" => $etudiant_id,
            "date_mvt"    => $saisie["date_mvt"],
            "cree_par"    => $_SESSION["utilisateur"],
        ]);
        journaliser(($saisie["type"] === "entree" ? "Entrée" : "Sortie") . " de caisse : " . fcfa($montant) . " (" . $saisie["motif"] . ")");
        flash("Opération enregistrée.");
        rediriger("caisse");
    }

    /* ---------- Journal d'activité ---------- */

    /** action=journal */
    public function journal(): void
    {
        exiger_admin();
        $lignes = $this->admin->journal(300);
        $titre  = "Journal d'activité";
        require RACINE . "/app/views/admin/journal.php";
    }

    /* ---------- Comptes du personnel ---------- */

    /** action=comptes */
    public function comptes(array $erreurs = [], array $saisie = []): void
    {
        exiger_admin();
        $membres = $this->admin->membres();
        $saisie  = array_merge(["login" => "", "role" => "membre"], $saisie);
        $titre   = "Comptes du personnel";
        require RACINE . "/app/views/admin/comptes.php";
    }

    /** action=compte_enregistrer (POST) */
    public function compteEnregistrer(): void
    {
        exiger_admin();
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            rediriger("comptes");
        }
        csrf_verifier();

        $saisie = ["login" => post("login"), "role" => post("role")];
        $mdp = is_string($_POST["mdp"] ?? null) ? $_POST["mdp"] : "";
        $erreurs = [];
        if (!preg_match('/^[a-zA-Z0-9._-]{3,50}$/', $saisie["login"])) {
            $erreurs[] = "Identifiant : 3 à 50 caractères (lettres, chiffres, point, tiret).";
        }
        if (!in_array($saisie["role"], ["membre", "admin"], true)) {
            $erreurs[] = "Rôle invalide.";
        }
        if (mb_strlen($mdp) < 10) {
            $erreurs[] = "Le mot de passe doit contenir au moins 10 caractères.";
        }

        if (empty($erreurs)) {
            try {
                $this->admin->ajouterMembre($saisie["login"], password_hash($mdp, PASSWORD_DEFAULT), $saisie["role"]);
                journaliser("Compte créé : " . $saisie["login"] . " (" . libelle_role($saisie["role"]) . ")");
                flash("Compte créé.");
                rediriger("comptes");
            } catch (PDOException $ex) {
                if (($ex->errorInfo[1] ?? 0) !== 1062) {
                    throw $ex;
                }
                $erreurs[] = "Cet identifiant existe déjà.";
            }
        }
        $this->comptes($erreurs, $saisie);
    }

    /** action=compte_supprimer (POST) */
    public function compteSupprimer(): void
    {
        exiger_admin();
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            rediriger("comptes");
        }
        csrf_verifier();

        $id = (int) post("id");
        $moi = array_filter($this->admin->membres(), fn($m) => $m["login"] === $_SESSION["utilisateur"]);
        if (!empty($moi) && (int) array_values($moi)[0]["id"] === $id) {
            flash("Vous ne pouvez pas supprimer votre propre compte.", "warning");
            rediriger("comptes");
        }
        $login = $this->admin->supprimerMembre($id);
        if ($login !== null) {
            journaliser("Compte supprimé : " . $login);
            flash("Compte supprimé.");
        }
        rediriger("comptes");
    }

    /* ---------- Classes ---------- */

    /** action=classes */
    public function classes(array $erreurs = [], string $nom = ""): void
    {
        exiger_admin();
        $classes = $this->admin->classesAvecEffectif();
        $titre   = "Classes";
        require RACINE . "/app/views/admin/classes.php";
    }

    /** action=classe_enregistrer (POST) */
    public function classeEnregistrer(): void
    {
        exiger_admin();
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            rediriger("classes");
        }
        csrf_verifier();

        $nom = post("nom");
        $erreurs = [];
        if (mb_strlen($nom) < 2 || mb_strlen($nom) > 50) {
            $erreurs[] = "Le nom doit contenir entre 2 et 50 caractères.";
        } else {
            try {
                $this->admin->ajouterClasse($nom);
                journaliser("Classe créée : " . $nom);
                flash("Classe créée.");
                rediriger("classes");
            } catch (PDOException $ex) {
                if (($ex->errorInfo[1] ?? 0) !== 1062) {
                    throw $ex;
                }
                $erreurs[] = "Cette classe existe déjà.";
            }
        }
        $this->classes($erreurs, $nom);
    }

    /** action=classe_supprimer (POST) */
    public function classeSupprimer(): void
    {
        exiger_admin();
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            rediriger("classes");
        }
        csrf_verifier();
        if ($this->admin->supprimerClasse((int) post("id"))) {
            journaliser("Classe supprimée (id " . (int) post("id") . ")");
            flash("Classe supprimée. Ses étudiants sont maintenant « non affectés ».");
        }
        rediriger("classes");
    }
}
