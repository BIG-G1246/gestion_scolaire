<?php
// app/models/NoteModel.php
// Tout le SQL des notes, des moyennes et du rang. Aucun echo, aucun $_POST.

class NoteModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Toutes les matières, avec la note de l'étudiant (valeur = NULL si pas encore de note).
     * LEFT JOIN : une matière sans note apparaît quand même dans la grille.
     * La condition sur l'étudiant est dans le ON (pas dans le WHERE), sinon les matières
     * sans note disparaîtraient.
     */
    public function grille(int $etudiant_id): array
    {
        $req = $this->pdo->prepare(
            "SELECT m.id AS matiere_id, m.nom AS matiere, m.coefficient, n.valeur
             FROM matieres m
             LEFT JOIN notes n ON n.matiere_id = m.id AND n.etudiant_id = :etudiant_id
             ORDER BY m.nom"
        );
        $req->execute(["etudiant_id" => $etudiant_id]);
        return $req->fetchAll();
    }

    /**
     * Ajoute la note, ou la remplace si elle existe déjà.
     * La contrainte UNIQUE (etudiant_id, matiere_id) déclenche le ON DUPLICATE KEY UPDATE.
     */
    public function enregistrer(int $etudiant_id, int $matiere_id, float $valeur): void
    {
        $req = $this->pdo->prepare(
            "INSERT INTO notes (etudiant_id, matiere_id, valeur)
             VALUES (:etudiant_id, :matiere_id, :valeur)
             ON DUPLICATE KEY UPDATE valeur = VALUES(valeur)"
        );
        $req->execute([
            "etudiant_id" => $etudiant_id,
            "matiere_id"  => $matiere_id,
            "valeur"      => $valeur,
        ]);
    }

    /** Case vidée dans la grille : on retire la note. */
    public function supprimer(int $etudiant_id, int $matiere_id): void
    {
        $req = $this->pdo->prepare(
            "DELETE FROM notes WHERE etudiant_id = :etudiant_id AND matiere_id = :matiere_id"
        );
        $req->execute(["etudiant_id" => $etudiant_id, "matiere_id" => $matiere_id]);
    }

    /**
     * Moyenne pondérée d'un étudiant : somme(note x coef) / somme(coef des matières notées).
     * Renvoie null si l'étudiant n'a aucune note.
     */
    public function moyenne(int $etudiant_id): ?float
    {
        $req = $this->pdo->prepare(
            "SELECT ROUND(SUM(n.valeur * m.coefficient) / SUM(m.coefficient), 2) AS moyenne
             FROM notes n
             JOIN matieres m ON m.id = n.matiere_id
             WHERE n.etudiant_id = :etudiant_id"
        );
        $req->execute(["etudiant_id" => $etudiant_id]);
        $moyenne = $req->fetchColumn();
        return $moyenne === null || $moyenne === false ? null : (float) $moyenne;
    }

    /**
     * Moyennes de tous les étudiants notés d'une classe, de la meilleure à la moins bonne.
     * Sert à calculer le rang et l'effectif sur le bulletin.
     */
    public function moyennesClasse(int $classe_id): array
    {
        $req = $this->pdo->prepare(
            "SELECT e.id,
                    ROUND(SUM(n.valeur * m.coefficient) / SUM(m.coefficient), 2) AS moyenne
             FROM etudiants e
             JOIN notes n ON n.etudiant_id = e.id
             JOIN matieres m ON m.id = n.matiere_id
             WHERE e.classe_id = :classe_id
             GROUP BY e.id
             ORDER BY moyenne DESC"
        );
        $req->execute(["classe_id" => $classe_id]);
        return $req->fetchAll();
    }
}
