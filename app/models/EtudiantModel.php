<?php
class EtudiantModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function tous(): array
    {
        return $this->pdo->query(
            "SELECT e.id, e.matricule, e.nom, e.prenom, e.photo, c.nom AS classe
             FROM etudiants e
             LEFT JOIN classes c ON c.id = e.classe_id
             ORDER BY e.nom, e.prenom"
        )->fetchAll();
    }

    public function parId(int $id): ?array
    {
        $req = $this->pdo->prepare("SELECT * FROM etudiants WHERE id = :id");
        $req->execute(["id" => $id]);
        return $req->fetch() ?: null;
    }

    public function ajouter(array $d): int
    {
        $req = $this->pdo->prepare(
            "INSERT INTO etudiants (matricule, nom, prenom, email, tel, sexe, photo, classe_id)
            VALUES (:matricule, :nom, :prenom, :email, :tel, :sexe, :photo, :classe_id)"
        );
        $req->execute($d);
        return (int) $this->pdo->lastInsertId();
    }

    public function mettreAJour(int $id, array $d): void
    {
        $req = $this->pdo->prepare(
            "UPDATE etudiants
            SET matricule = :matricule, nom = :nom, prenom = :prenom,
                email = :email, tel = :tel, sexe = :sexe,
                photo = :photo, classe_id = :classe_id
            WHERE id = :id"
        );
        $req->execute(array_merge($d, ["id" => $id]));
    }

    public function supprimer(int $id): bool
    {
        $req = $this->pdo->prepare("DELETE FROM etudiants WHERE id = :id");
        $req->execute(["id" => $id]);
        return $req->rowCount() > 0;
    }
}