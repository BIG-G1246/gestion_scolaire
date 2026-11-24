<?php
// app/controllers/MatiereController.php
// Gestion des matières : réservé à l'admin (contrôle en première ligne de chaque méthode).

class MatiereController
{
    private MatiereModel $modele;

    public function __construct(PDO $pdo)
    {
        $this->modele = new MatiereModel($pdo);
    }

    /** action=matieres : liste des matières */
    public function liste(): void
    {
        exiger_admin();
        $matieres = $this->modele->tous();
        require __DIR__ . '/../views/matieres/liste.php';
    }

    /** action=matiere_formulaire (ajout) ou action=matiere_formulaire&id=3 (modification) */
    public function formulaire(): void
    {
        exiger_admin();
        $id = (int) ($_GET['id'] ?? 0);

        if ($id > 0) {
            $matiere = $this->modele->trouver($id);
            if ($matiere === null) {
                $this->introuvable();
                return;
            }
        } else {
            $matiere = ['id' => 0, 'nom' => '', 'coefficient' => 1];
        }

        $erreurs = [];
        require __DIR__ . '/../views/matieres/formulaire.php';
    }

    /** action=matiere_enregistrer (POST) : ajoute ou modifie selon le champ caché id */
    public function enregistrer(): void
    {
        exiger_admin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            rediriger('matieres');
        }
        csrf_verifier();   // même appel que dans EtudiantController

        $id = (int) post('id');
        // Un id caché peut être modifié dans le navigateur : on vérifie qu'il existe.
        if ($id > 0 && $this->modele->trouver($id) === null) {
            $this->introuvable();
            return;
        }

        // On garde les saisies pour re-remplir le formulaire en cas d'erreur.
        $matiere = [
            'id'          => $id,
            'nom'         => post('nom'),
            'coefficient' => post('coefficient'),
        ];
        $erreurs = [];

        $longueur = mb_strlen($matiere['nom']);
        if ($longueur < 2 || $longueur > 50) {
            $erreurs['nom'] = 'Le nom doit contenir entre 2 et 50 caractères.';
        }

        // FILTER_VALIDATE_INT renvoie l'entier, ou false si ce n'est pas un entier de 1 à 10.
        $coefficient = filter_var($matiere['coefficient'], FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 10],
        ]);
        if ($coefficient === false) {
            $erreurs['coefficient'] = 'Le coefficient doit être un nombre entier entre 1 et 10.';
        }

        if (empty($erreurs)) {
            try {
                if ($id > 0) {
                    $this->modele->modifier($id, $matiere['nom'], $coefficient);
                    flash('Matière modifiée.', 'success');
                } else {
                    $this->modele->ajouter($matiere['nom'], $coefficient);
                    flash('Matière ajoutée.', 'success');
                }
                rediriger('matieres');   // Post/Redirect/Get
            } catch (PDOException $ex) {
                // 1062 = doublon sur la colonne UNIQUE "nom"
                if (($ex->errorInfo[1] ?? 0) === 1062) {
                    $erreurs['nom'] = 'Cette matière existe déjà.';
                } else {
                    throw $ex;   // autre erreur : on ne la cache pas
                }
            }
        }

        // Ici seulement s'il y a des erreurs : on réaffiche le formulaire.
        require __DIR__ . '/../views/matieres/formulaire.php';
    }

    /** action=matiere_supprimer (POST) */
    public function supprimer(): void
    {
        exiger_admin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            rediriger('matieres');
        }
        csrf_verifier();

        $id = (int) post('id');
        try {
            if ($this->modele->supprimer($id)) {
                flash('Matière supprimée.', 'success');
            } else {
                flash('Matière introuvable.', 'warning');
            }
        } catch (PDOException $ex) {
            // 1451 = la clé étrangère des notes (ON DELETE RESTRICT) bloque la suppression
            if (($ex->errorInfo[1] ?? 0) === 1451) {
                flash('Impossible de supprimer cette matière : des notes y sont rattachées.', 'danger');
            } else {
                throw $ex;
            }
        }
        rediriger('matieres');
    }

    private function introuvable(): void
    {
        http_response_code(404);
        require __DIR__ . '/../views/404.php';
    }
}
