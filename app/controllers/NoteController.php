<?php
// app/controllers/NoteController.php
// Saisie des notes d'un étudiant par trimestre (grille de toutes les matières) et bulletin.
// Accès : personnel connecté (secrétaire ou directeur). Le bulletin sert aussi à l'espace étudiant.

class NoteController
{
    private NoteModel $notes;
    private EtudiantModel $etudiants;
    private PDO $pdo;   // pour la transaction

    public function __construct(PDO $pdo)
    {
        $this->notes     = new NoteModel($pdo);
        $this->etudiants = new EtudiantModel($pdo);
        $this->pdo       = $pdo;
    }

    /** action=notes&id=3&t=2 : affiche la grille de saisie du trimestre */
    public function saisie(): void
    {
        exiger_connexion();
        $etudiant  = $this->etudiantOu404((int) ($_GET["id"] ?? 0));
        $trimestre = trimestre_demande();

        $grille  = $this->notes->grille((int) $etudiant["id"], $trimestre);
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

        $etudiant  = $this->etudiantOu404((int) post("etudiant_id"));
        $trimestre = (int) post("trimestre");
        if ($trimestre < 1 || $trimestre > 3) {
            $trimestre = 1;
        }
        $grille = $this->notes->grille((int) $etudiant["id"], $trimestre);

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
                    $this->notes->supprimer((int) $etudiant["id"], $mid, $trimestre);
                } else {
                    $this->notes->enregistrer((int) $etudiant["id"], $mid, $trimestre, $valeur);
                }
            }
            $this->pdo->commit();
        } catch (PDOException $ex) {
            $this->pdo->rollBack();
            throw $ex;
        }

        journaliser("Notes du trimestre " . $trimestre . " enregistrées : " . $etudiant["matricule"]);
        flash("Notes du trimestre " . $trimestre . " enregistrées.");
        rediriger("bulletin", ["id" => (int) $etudiant["id"], "t" => $trimestre]);   // Post/Redirect/Get
    }

    /** action=bulletin&id=3&t=1 : bulletin imprimable (personnel) */
    public function bulletin(): void
    {
        exiger_connexion();
        $this->afficherBulletin((int) ($_GET["id"] ?? 0), trimestre_demande());
    }

    /**
     * Prépare et affiche le bulletin d'un étudiant pour un trimestre.
     * Public : l'espace étudiant l'appelle avec l'id de la SESSION (jamais celui de l'URL).
     */
    public function afficherBulletin(int $id, int $trimestre): void
    {
        $etudiant = $this->etudiantsAvecClasseOu404($id);
        $classe_id = $etudiant["classe_id"] !== null ? (int) $etudiant["classe_id"] : null;

        $grille   = $this->notes->grille($id, $trimestre);
        $resultat = $this->notes->rang($id, $classe_id, $trimestre);
        $moyenne  = $resultat["moyenne"];
        $rang     = $resultat["rang"];
        $effectif = $resultat["effectif"];
        $mention  = mention($moyenne);

        $titre = "Bulletin T" . $trimestre . " " . $etudiant["matricule"] . " " . $etudiant["nom"];
        require RACINE . "/app/views/notes/bulletin.php";
    }

    /** L'étudiant demandé, ou une page 404 (et on s'arrête). */
    private function etudiantOu404(int $id): array
    {
        $etudiant = $id > 0 ? $this->etudiants->parId($id) : null;
        if ($etudiant === null) {
            $this->pageIntrouvable();
        }
        return $etudiant;
    }

    private function etudiantsAvecClasseOu404(int $id): array
    {
        $etudiant = $id > 0 ? $this->etudiants->avecClasse($id) : null;
        if ($etudiant === null) {
            $this->pageIntrouvable();
        }
        return $etudiant;
    }

    private function pageIntrouvable(): never
    {
        http_response_code(404);
        require RACINE . "/app/views/404.php";
        exit;
    }
}
