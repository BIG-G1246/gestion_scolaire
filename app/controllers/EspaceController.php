<?php
// app/controllers/EspaceController.php
// Espace étudiant. Règle de sécurité : l'id de l'étudiant vient TOUJOURS de la session,
// jamais de l'URL. Un étudiant ne peut donc pas voir le bulletin d'un camarade.

class EspaceController
{
    private PDO $pdo;
    private EtudiantModel $etudiants;
    private NoteModel $notes;
    private PlanningModel $planning;
    private MatiereModel $matieres;

    public function __construct(PDO $pdo)
    {
        $this->pdo       = $pdo;
        $this->etudiants = new EtudiantModel($pdo);
        $this->notes     = new NoteModel($pdo);
        $this->planning  = new PlanningModel($pdo);
        $this->matieres  = new MatiereModel($pdo);
    }

    /** action=mon_espace : tableau de bord de l'étudiant */
    public function accueil(): void
    {
        $moi = $this->moi();
        $classe_id = $moi["classe_id"] !== null ? (int) $moi["classe_id"] : null;

        // Moyenne et rang pour chacun des 3 trimestres
        $resultats = [];
        for ($t = 1; $t <= 3; $t++) {
            $resultats[$t] = $this->notes->rang((int) $moi["id"], $classe_id, $t);
        }

        $jour_semaine  = (int) date("N");   // 1 = lundi ... 7 = dimanche
        $cours_du_jour = ($classe_id !== null && $jour_semaine <= 6) ? $this->planning->coursDuJour($classe_id, $jour_semaine) : [];
        $compositions  = $classe_id !== null ? $this->planning->compositions($classe_id, true, 10) : [];
        $matieres      = $this->matieres->tous();

        $titre = "Mon espace";
        require RACINE . "/app/views/espace/accueil.php";
    }

    /** action=mon_emploi_du_temps */
    public function emploiDuTemps(): void
    {
        $moi   = $this->moi();
        $cours = $moi["classe_id"] !== null ? $this->planning->coursClasse((int) $moi["classe_id"]) : [];
        $titre = "Mon emploi du temps";
        require RACINE . "/app/views/espace/emploi_du_temps.php";
    }

    /** action=mon_bulletin&t=1 */
    public function bulletin(): void
    {
        exiger_etudiant();
        (new NoteController($this->pdo))->afficherBulletin((int) $_SESSION["etudiant_id"], trimestre_demande());
    }

    /** L'étudiant connecté (avec sa classe). */
    private function moi(): array
    {
        exiger_etudiant();
        $moi = $this->etudiants->avecClasse((int) $_SESSION["etudiant_id"]);
        if ($moi === null) {
            // L'étudiant a été supprimé pendant sa session : on le déconnecte.
            $_SESSION = [];
            session_destroy();
            rediriger("connexion");
        }
        return $moi;
    }
}
