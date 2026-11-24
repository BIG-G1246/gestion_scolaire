<?php
// app/controllers/PlanningController.php
// Emploi du temps et compositions, gérés par le personnel (secrétaire et directeur).

class PlanningController
{
    private PlanningModel $planning;
    private ClasseModel $classes;
    private MatiereModel $matieres;

    public function __construct(PDO $pdo)
    {
        $this->planning = new PlanningModel($pdo);
        $this->classes  = new ClasseModel($pdo);
        $this->matieres = new MatiereModel($pdo);
    }

    /** action=emploi_du_temps&classe=1 */
    public function emploiDuTemps(array $erreurs = [], array $saisie = []): void
    {
        exiger_connexion();
        $classes  = $this->classes->tous();
        $matieres = $this->matieres->tous();
        $classe_id = (int) ($_GET["classe"] ?? ($saisie["classe_id"] ?? ($classes[0]["id"] ?? 0)));
        $classe   = $this->classes->parId($classe_id);
        $cours    = $classe ? $this->planning->coursClasse($classe_id) : [];
        $titre    = "Emploi du temps";
        require RACINE . "/app/views/planning/emploi_du_temps.php";
    }

    /** action=cours_enregistrer (POST) */
    public function coursEnregistrer(): void
    {
        exiger_connexion();
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            rediriger("emploi_du_temps");
        }
        csrf_verifier();

        $d = [
            "classe_id"   => (int) post("classe_id"),
            "matiere_id"  => (int) post("matiere_id"),
            "jour"        => (int) post("jour"),
            "heure_debut" => post("heure_debut"),
            "heure_fin"   => post("heure_fin"),
            "salle"       => post("salle") !== "" ? mb_substr(post("salle"), 0, 30) : null,
            "enseignant"  => post("enseignant") !== "" ? mb_substr(post("enseignant"), 0, 80) : null,
        ];
        $erreurs = $this->verifierCommun($d);
        if (!isset(jours()[$d["jour"]])) {
            $erreurs[] = "Le jour choisi est invalide.";
        }
        if (!empty($erreurs)) {
            $_GET["classe"] = $d["classe_id"];
            $this->emploiDuTemps($erreurs, $d);
            return;
        }

        $this->planning->ajouterCours($d);
        journaliser("Cours ajouté à l'emploi du temps (classe " . $d["classe_id"] . ")");
        flash("Cours ajouté.");
        rediriger("emploi_du_temps", ["classe" => $d["classe_id"]]);
    }

    /** action=cours_supprimer (POST) */
    public function coursSupprimer(): void
    {
        exiger_connexion();
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            rediriger("emploi_du_temps");
        }
        csrf_verifier();
        if ($this->planning->supprimerCours((int) post("id"))) {
            journaliser("Cours retiré de l'emploi du temps");
            flash("Cours supprimé.");
        }
        rediriger("emploi_du_temps", ["classe" => (int) post("classe_id")]);
    }

    /** action=compositions */
    public function compositions(array $erreurs = [], array $saisie = []): void
    {
        exiger_connexion();
        $classes      = $this->classes->tous();
        $matieres     = $this->matieres->tous();
        $compositions = $this->planning->compositions();
        $titre        = "Compositions";
        require RACINE . "/app/views/planning/compositions.php";
    }

    /** action=composition_enregistrer (POST) */
    public function compositionEnregistrer(): void
    {
        exiger_connexion();
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            rediriger("compositions");
        }
        csrf_verifier();

        $d = [
            "classe_id"   => (int) post("classe_id"),
            "matiere_id"  => (int) post("matiere_id"),
            "trimestre"   => (int) post("trimestre"),
            "date_compo"  => post("date_compo"),
            "heure_debut" => post("heure_debut"),
            "heure_fin"   => post("heure_fin"),
            "salle"       => post("salle") !== "" ? mb_substr(post("salle"), 0, 30) : null,
        ];
        $erreurs = $this->verifierCommun($d);
        if ($d["trimestre"] < 1 || $d["trimestre"] > 3) {
            $erreurs[] = "Le trimestre doit être 1, 2 ou 3.";
        }
        $date = DateTime::createFromFormat("Y-m-d", $d["date_compo"]);
        if (!$date || $date->format("Y-m-d") !== $d["date_compo"]) {
            $erreurs[] = "La date est invalide.";
        }
        if (!empty($erreurs)) {
            $this->compositions($erreurs, $d);
            return;
        }

        $this->planning->ajouterComposition($d);
        journaliser("Composition programmée le " . date_fr($d["date_compo"]));
        flash("Composition programmée.");
        rediriger("compositions");
    }

    /** action=composition_supprimer (POST) */
    public function compositionSupprimer(): void
    {
        exiger_connexion();
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            rediriger("compositions");
        }
        csrf_verifier();
        if ($this->planning->supprimerComposition((int) post("id"))) {
            journaliser("Composition supprimée");
            flash("Composition supprimée.");
        }
        rediriger("compositions");
    }

    /** Vérifications communes aux cours et aux compositions. */
    private function verifierCommun(array $d): array
    {
        $erreurs = [];
        if (!$this->classes->parId($d["classe_id"])) {
            $erreurs[] = "Choisissez une classe.";
        }
        if (!$this->matieres->trouver($d["matiere_id"])) {
            $erreurs[] = "Choisissez une matière.";
        }
        $format = '/^([01]\d|2[0-3]):[0-5]\d$/';   // HH:MM de 00:00 à 23:59
        if (!preg_match($format, $d["heure_debut"]) || !preg_match($format, $d["heure_fin"])) {
            $erreurs[] = "Les heures doivent être au format HH:MM.";
        } elseif ($d["heure_fin"] <= $d["heure_debut"]) {
            $erreurs[] = "L'heure de fin doit être après l'heure de début.";
        }
        return $erreurs;
    }
}
