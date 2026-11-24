<?php
// app/models/MatiereModel.php
// Tout le SQL des matières. Aucun echo, aucun $_POST : le modèle reçoit des valeurs
// déjà vérifiées par le contrôleur et renvoie des tableaux.

class MatiereModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Toutes les matières, triées par nom, avec le nombre de notes de chacune.
     * LEFT JOIN : une matière sans note apparaît quand même (nb_notes = 0).
     * Pas de marqueur ici car la requête ne contient aucune valeur venant de l'utilisateur.
     */
    public function tous(): array
    {
        $sql = "SELECT m.id, m.nom, m.coefficient, COUNT(n.id) AS nb_notes
                FROM matieres m
                LEFT JOIN notes n ON n.matiere_id = m.id
                GROUP BY m.id, m.nom, m.coefficient
                ORDER BY m.nom";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Une matière par son id, ou null si elle n'existe pas. */
    public function trouver(int $id): ?array
    {
        $requete = $this->pdo->prepare(
            "SELECT id, nom, coefficient FROM matieres WHERE id = :id"
        );
        $requete->execute(['id' => $id]);
        $matiere = $requete->fetch(PDO::FETCH_ASSOC);
        return $matiere ?: null;   // fetch() renvoie false si rien : on le transforme en null
    }

    public function ajouter(string $nom, int $coefficient): int
    {
        $requete = $this->pdo->prepare(
            "INSERT INTO matieres (nom, coefficient) VALUES (:nom, :coefficient)"
        );
        $requete->execute(['nom' => $nom, 'coefficient' => $coefficient]);
        return (int) $this->pdo->lastInsertId();
    }

    public function modifier(int $id, string $nom, int $coefficient): void
    {
        $requete = $this->pdo->prepare(
            "UPDATE matieres SET nom = :nom, coefficient = :coefficient WHERE id = :id"
        );
        $requete->execute(['nom' => $nom, 'coefficient' => $coefficient, 'id' => $id]);
    }

    /**
     * Supprime une matière. Renvoie true si une ligne a été supprimée.
     * Si des notes y sont rattachées, MySQL refuse (ON DELETE RESTRICT, erreur 1451) :
     * c'est le contrôleur qui attrape l'exception et affiche un message clair.
     */
    public function supprimer(int $id): bool
    {
        $requete = $this->pdo->prepare("DELETE FROM matieres WHERE id = :id");
        $requete->execute(['id' => $id]);
        return $requete->rowCount() > 0;
    }
}
