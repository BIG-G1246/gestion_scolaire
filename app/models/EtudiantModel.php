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

    /**
     * Recherche par mot-clé : nom, prénom, nom complet, matricule ou classe.
     * Pour la classe, on compare SANS les espaces : "6eA", "6e A" et "6e a" trouvent la classe "6e A".
     */
    public function rechercher(string $motCle): array
    {
        // addcslashes : % et _ sont des jokers en SQL, on les neutralise pour les chercher tels quels
        $texte  = "%" . addcslashes($motCle, "%_\\") . "%";
        $classe = "%" . addcslashes(str_replace(" ", "", $motCle), "%_\\") . "%";

        $req = $this->pdo->prepare(
            "SELECT e.id, e.matricule, e.nom, e.prenom, e.photo, (e.mdp_hash IS NOT NULL) AS a_acces, c.nom AS classe
             FROM etudiants e
             LEFT JOIN classes c ON c.id = e.classe_id
             WHERE e.nom LIKE :t1
                OR e.prenom LIKE :t2
                OR e.matricule LIKE :t3
                OR CONCAT(e.prenom, ' ', e.nom) LIKE :t4
                OR CONCAT(e.nom, ' ', e.prenom) LIKE :t5
                OR REPLACE(c.nom, ' ', '') LIKE :classe
             ORDER BY c.nom, e.nom, e.prenom"
        );
        $req->execute(["t1" => $texte, "t2" => $texte, "t3" => $texte, "t4" => $texte, "t5" => $texte, "classe" => $classe]);
        return $req->fetchAll();
    }

    /** Les étudiants d'une classe, par ordre alphabétique */
    public function parClasse(int $classeId): array
    {
        $req = $this->pdo->prepare(
            "SELECT id, matricule, nom, prenom, sexe, tel, photo, (mdp_hash IS NOT NULL) AS a_acces
             FROM etudiants WHERE classe_id = :id ORDER BY nom, prenom"
        );
        $req->execute(["id" => $classeId]);
        return $req->fetchAll();
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
