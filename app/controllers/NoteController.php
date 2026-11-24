<?php
// app/controllers/NoteController.php
// Saisie des notes d'un étudiant (grille de toutes les matières) et bulletin.
// Accès : tout utilisateur connecté (membre ou admin), comme l'ajout et la modification d'étudiants.

class NoteController
{
    private NoteModel $notes;
    private EtudiantModel $etudiants;
    private ClasseModel $classes;
    private PDO $pdo;   // pour la transaction

    public function __construct(PDO $pdo)
    {
        $this->notes     = new NoteModel($pdo);
        $this->etudiants = new EtudiantModel($pdo);
        $this->classes   = new ClasseModel($pdo);
        $this->pdo       = $pdo;
    }

    /** action=notes&id=3 : affiche la grille de saisie */
    public function saisie(): void
    {
        exiger_connexion();
        $etudiant = $this->etudiantOu404((int) ($_GET["id"] ?? 0));

        $grille  = $this->notes->grille((int) $etudiant["id"]);
        $saisies = [];   // vide : la vue affiche les notes de la base
        $erreurs = [];
        $titre   = "Notes de " . $etudiant["prenom"] . " " . $etudiant["nom"];
        require RACINE . "/app/views/notes/saisie.php";
    }

    /** action=notes_enregistrer (POST) : valide toute la grille, puis enregistre */
    public function enregistrer(): void
    {
        exiger_connexion();
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            rediriger("liste");
        }
        csrf_verifier();

        $etudiant = $this->etudiantOu404((int) post("etudiant_id"));
        $grille   = $this->notes->grille((int) $etudiant["id"]);

        // $_POST["notes"] est un tableau [matiere_id => "12,5"]. On ne lit que les matières
        // qui existent vraiment (un id inventé dans le navigateur est ignoré).
        $envoyees = is_array($_POST["notes"] ?? null) ? $_POST["notes"] : [];
        $saisies  = [];
        $valeurs  = [];
        $erreurs  = [];

        foreach ($grille as $ligne) {
            $mid  = (int) $ligne["matiere_id"];
            $brut = $envoyees[$mid] ?? "";
            $brut = is_string($brut) ? trim($brut) : "";
            $saisies[$mid] = $brut;

            if ($brut === "") {
                $valeurs[$mid] = null;   // case vide : pas de note (on supprime l'ancienne)
                continue;
            }

            // On accepte la virgule française : "12,5" devient "12.5".
            $texte = str_replace(",", ".", $brut);
            if (!preg_match('/^\d{1,2}(\.\d{1,2})?$/', $texte) || (float) $texte > 20) {
                $erreurs[$mid] = $ligne["matiere"] . " : la note doit être un nombre entre 0 et 20 (ex. 12,5).";
                continue;
            }
            $valeurs[$mid] = (float) $texte;
        }

        if (!empty($erreurs)) {
            // Rien n'est enregistré : on réaffiche la grille avec les saisies et les erreurs.
            $titre = "Notes de " . $etudiant["prenom"] . " " . $etudiant["nom"];
            require RACINE . "/app/views/notes/saisie.php";
            return;
        }

        // Transaction : toutes les notes sont enregistrées, ou aucune en cas de problème.
        $this->pdo->beginTransaction();
        try {
            foreach ($valeurs as $mid => $valeur) {
                if ($valeur === null) {
                    $this->notes->supprimer((int) $etudiant["id"], $mid);
                } else {
                    $this->notes->enregistrer((int) $etudiant["id"], $mid, $valeur);
                }
            }
            $this->pdo->commit();
        } catch (PDOException $ex) {
            $this->pdo->rollBack();
            throw $ex;
        }

        flash("Notes enregistrées.");
        rediriger("bulletin", ["id" => (int) $etudiant["id"]]);   // Post/Redirect/Get
    }

    /** action=bulletin&id=3 : bulletin imprimable */
    public function bulletin(): void
    {
        exiger_connexion();
        $etudiant = $this->etudiantOu404((int) ($_GET["id"] ?? 0));

        $grille  = $this->notes->grille((int) $etudiant["id"]);
        $moyenne = $this->notes->moyenne((int) $etudiant["id"]);
        $mention = mention($moyenne);

        $classe  = null;
        $rang    = null;
        $effectif = 0;
        if ($etudiant["classe_id"] !== null) {
            $classe = $this->classes->parId((int) $etudiant["classe_id"]);
            $classement = $this->notes->moyennesClasse((int) $etudiant["classe_id"]);
            $effectif = count($classement);
            // Rang = 1 + nombre d'élèves qui ont une moyenne strictement meilleure (gère les ex æquo).
            if ($moyenne !== null) {
                $rang = 1;
                foreach ($classement as $c) {
                    if ((float) $c["moyenne"] > $moyenne) {
                        $rang++;
                    }
                }
            }
        }

        $titre = "Bulletin de " . $etudiant["prenom"] . " " . $etudiant["nom"];
        require RACINE . "/app/views/notes/bulletin.php";
    }

    /** L'étudiant demandé, ou une page 404 (et on s'arrête). */
    private function etudiantOu404(int $id): array
    {
        $etudiant = $id > 0 ? $this->etudiants->parId($id) : null;
        if ($etudiant === null) {
            http_response_code(404);
            require RACINE . "/app/views/404.php";
            exit;
        }
        return $etudiant;
    }
}
