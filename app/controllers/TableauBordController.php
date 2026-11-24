<?php
// app/controllers/TableauBordController.php
// Tableau de bord du personnel. La secrétaire voit les statistiques de l'établissement ;
// le directeur voit les mêmes, plus la caisse, les classements, les matières et le journal.

class TableauBordController
{
    private StatModel $stats;
    private PlanningModel $planning;
    private AdminModel $admin;

    public function __construct(PDO $pdo)
    {
        $this->stats    = new StatModel($pdo);
        $this->planning = new PlanningModel($pdo);
        $this->admin    = new AdminModel($pdo);
    }

    /** action=tableau_bord&t=1 */
    public function index(): void
    {
        exiger_connexion();
        $trimestre = trimestre_demande();

        // Partie commune (secrétaire et directeur)
        $effectifs     = $this->stats->effectifs();
        $resultats     = $this->stats->resultatsGlobaux($trimestre);
        $par_classe    = $this->stats->parClasse($trimestre);
        $inscriptions  = $this->stats->dernieresInscriptions();
        $par_mois      = $this->stats->inscriptionsParMois();
        $compositions  = $this->planning->compositions(null, true, 6);
        $acces         = $this->stats->accesEtudiants();

        // Partie réservée au directeur : on ne fait même pas les requêtes pour la secrétaire.
        if (est_admin()) {
            $caisse        = $this->admin->totauxCaisse();
            $meilleurs     = $this->stats->classement($trimestre, "DESC", 5);
            $difficulte    = array_filter($this->stats->classement($trimestre, "ASC", 5), fn($l) => (float) $l["moyenne"] < 10);
            $par_matiere   = $this->stats->parMatiere($trimestre);
            $journal       = $this->admin->journal(8);
            $connexions    = $this->admin->connexionsSemaine();
            $impayes       = $this->admin->etudiantsSansPaiement();
        }

        $titre = est_admin() ? "Tableau de bord du directeur" : "Tableau de bord";
        require RACINE . "/app/views/tableau_bord/index.php";
    }
}
