<?php
// app/models/PlanningModel.php
// Emploi du temps (table cours) et compositions (table compositions).

class PlanningModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /** Cours d'une classe, triés par jour puis par heure. */
    public function coursClasse(int $classe_id): array
    {
        $req = $this->pdo->prepare(
            "SELECT co.id, co.jour, co.heure_debut, co.heure_fin, co.salle, co.enseignant,
                    m.nom AS matiere, m.coefficient
             FROM cours co
             JOIN matieres m ON m.id = co.matiere_id
             WHERE co.classe_id = :classe_id
             ORDER BY co.jour, co.heure_debut"
        );
        $req->execute(["classe_id" => $classe_id]);
        return $req->fetchAll();
    }

    /** Cours d'une classe pour un jour donné (1 = lundi). */
    public function coursDuJour(int $classe_id, int $jour): array
    {
        return array_values(array_filter(
            $this->coursClasse($classe_id),
            fn(array $c) => (int) $c["jour"] === $jour
        ));
    }

    public function ajouterCours(array $d): void
    {
        $req = $this->pdo->prepare(
            "INSERT INTO cours (classe_id, matiere_id, jour, heure_debut, heure_fin, salle, enseignant)
             VALUES (:classe_id, :matiere_id, :jour, :heure_debut, :heure_fin, :salle, :enseignant)"
        );
        $req->execute($d);
    }

    public function supprimerCours(int $id): bool
    {
        $req = $this->pdo->prepare("DELETE FROM cours WHERE id = :id");
        $req->execute(["id" => $id]);
        return $req->rowCount() > 0;
    }

    /**
     * Compositions. $classe_id = null : toutes les classes.
     * $a_venir = true : seulement celles d'aujourd'hui ou plus tard (CURDATE()).
     */
    public function compositions(?int $classe_id = null, bool $a_venir = false, int $limite = 200): array
    {
        $sql = "SELECT cp.id, cp.trimestre, cp.date_compo, cp.heure_debut, cp.heure_fin, cp.salle,
                       m.nom AS matiere, c.nom AS classe
                FROM compositions cp
                JOIN matieres m ON m.id = cp.matiere_id
                JOIN classes c ON c.id = cp.classe_id
                WHERE 1 = 1";
        $params = [];
        if ($classe_id !== null) {
            $sql .= " AND cp.classe_id = :classe_id";
            $params["classe_id"] = $classe_id;
        }
        if ($a_venir) {
            $sql .= " AND cp.date_compo >= CURDATE()";
        }
        // LIMIT ne peut pas recevoir un marqueur avec certains pilotes : on force un entier.
        $sql .= " ORDER BY cp.date_compo, cp.heure_debut LIMIT " . max(1, $limite);
        $req = $this->pdo->prepare($sql);
        $req->execute($params);
        return $req->fetchAll();
    }

    public function ajouterComposition(array $d): void
    {
        $req = $this->pdo->prepare(
            "INSERT INTO compositions (classe_id, matiere_id, trimestre, date_compo, heure_debut, heure_fin, salle)
             VALUES (:classe_id, :matiere_id, :trimestre, :date_compo, :heure_debut, :heure_fin, :salle)"
        );
        $req->execute($d);
    }

    public function supprimerComposition(int $id): bool
    {
        $req = $this->pdo->prepare("DELETE FROM compositions WHERE id = :id");
        $req->execute(["id" => $id]);
        return $req->rowCount() > 0;
    }
}
