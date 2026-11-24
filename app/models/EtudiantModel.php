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
            "SELECT e.id, e.matricule, e.nom, e.prenom, e.photo, (e.mdp_hash IS NOT NULL) AS a_acces, c.nom AS classe
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

    public function parMatricule(string $matricule): ?array
    {
        $req = $this->pdo->prepare(
            "SELECT id, matricule, nom, prenom, mdp_hash FROM etudiants WHERE matricule = :matricule"
        );
        $req->execute(["matricule" => $matricule]);
        return $req->fetch() ?: null;
    }

    /** Donne (ou change) le mot de passe de l'espace étudiant. NULL = accès retiré. */
    public function definirMotDePasse(int $id, ?string $hash): void
    {
        $req = $this->pdo->prepare("UPDATE etudiants SET mdp_hash = :hash WHERE id = :id");
        $req->execute(["hash" => $hash, "id" => $id]);
    }

    /** Étudiant avec le nom de sa classe (pour les pages de l'espace étudiant). */
    public function avecClasse(int $id): ?array
    {
        $req = $this->pdo->prepare(
            "SELECT e.id, e.matricule, e.nom, e.prenom, e.sexe, e.photo, e.classe_id, e.inscrit_le,
                    c.nom AS classe
             FROM etudiants e
             LEFT JOIN classes c ON c.id = e.classe_id
             WHERE e.id = :id"
        );
        $req->execute(["id" => $id]);
        return $req->fetch() ?: null;
    }
}
