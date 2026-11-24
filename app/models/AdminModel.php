<?php
// app/models/AdminModel.php
// Fonctions réservées au directeur : caisse, journal d'activité, comptes du personnel, classes.

class AdminModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /* ---------- Caisse ---------- */

    public function mouvements(int $limite = 100): array
    {
        return $this->pdo->query(
            "SELECT mv.id, mv.type, mv.montant, mv.motif, mv.date_mvt, mv.cree_par,
                    e.matricule, e.nom, e.prenom
             FROM mouvements mv
             LEFT JOIN etudiants e ON e.id = mv.etudiant_id
             ORDER BY mv.date_mvt DESC, mv.id DESC
             LIMIT " . max(1, $limite)
        )->fetchAll();
    }

    /** Totaux : entrées, sorties, solde ; et les mêmes pour le mois en cours. */
    public function totauxCaisse(): array
    {
        return $this->pdo->query(
            "SELECT COALESCE(SUM(CASE WHEN type = 'entree' THEN montant END), 0) AS entrees,
                    COALESCE(SUM(CASE WHEN type = 'sortie' THEN montant END), 0) AS sorties,
                    COALESCE(SUM(CASE WHEN type = 'entree' AND DATE_FORMAT(date_mvt, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') THEN montant END), 0) AS entrees_mois,
                    COALESCE(SUM(CASE WHEN type = 'sortie' AND DATE_FORMAT(date_mvt, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') THEN montant END), 0) AS sorties_mois
             FROM mouvements"
        )->fetch();
    }

    public function ajouterMouvement(array $d): void
    {
        $req = $this->pdo->prepare(
            "INSERT INTO mouvements (type, montant, motif, etudiant_id, date_mvt, cree_par)
             VALUES (:type, :montant, :motif, :etudiant_id, :date_mvt, :cree_par)"
        );
        $req->execute($d);
    }

    /** Étudiants qui n'ont encore rien payé (aucune entrée à leur nom). */
    public function etudiantsSansPaiement(): array
    {
        return $this->pdo->query(
            "SELECT e.matricule, e.nom, e.prenom, c.nom AS classe
             FROM etudiants e
             LEFT JOIN classes c ON c.id = e.classe_id
             WHERE NOT EXISTS (
                 SELECT 1 FROM mouvements mv WHERE mv.etudiant_id = e.id AND mv.type = 'entree'
             )
             ORDER BY e.nom, e.prenom"
        )->fetchAll();
    }

    /* ---------- Journal ---------- */

    public function journal(int $limite = 200): array
    {
        return $this->pdo->query(
            "SELECT utilisateur, role, action, ip, cree_le FROM journal
             ORDER BY cree_le DESC, id DESC LIMIT " . max(1, $limite)
        )->fetchAll();
    }

    /** Connexions réussies et échouées des 7 derniers jours. */
    public function connexionsSemaine(): array
    {
        return $this->pdo->query(
            "SELECT SUM(action LIKE 'Connexion réussie%') AS reussies,
                    SUM(action LIKE 'Échec de connexion%') AS echecs
             FROM journal
             WHERE cree_le >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
        )->fetch();
    }

    /* ---------- Comptes du personnel ---------- */

    public function membres(): array
    {
        return $this->pdo->query("SELECT id, login, role, cree_le FROM membres ORDER BY role, login")->fetchAll();
    }

    public function ajouterMembre(string $login, string $hash, string $role): void
    {
        $req = $this->pdo->prepare("INSERT INTO membres (login, mdp_hash, role) VALUES (:login, :hash, :role)");
        $req->execute(["login" => $login, "hash" => $hash, "role" => $role]);
    }

    public function supprimerMembre(int $id): ?string
    {
        $req = $this->pdo->prepare("SELECT login FROM membres WHERE id = :id");
        $req->execute(["id" => $id]);
        $login = $req->fetchColumn();
        if ($login === false) {
            return null;
        }
        $req = $this->pdo->prepare("DELETE FROM membres WHERE id = :id");
        $req->execute(["id" => $id]);
        return $login;
    }

    /* ---------- Classes ---------- */

    public function classesAvecEffectif(): array
    {
        return $this->pdo->query(
            "SELECT c.id, c.nom, COUNT(e.id) AS effectif
             FROM classes c LEFT JOIN etudiants e ON e.classe_id = c.id
             GROUP BY c.id, c.nom ORDER BY c.nom"
        )->fetchAll();
    }

    public function ajouterClasse(string $nom): void
    {
        $req = $this->pdo->prepare("INSERT INTO classes (nom) VALUES (:nom)");
        $req->execute(["nom" => $nom]);
    }

    /** Les étudiants de la classe deviennent "non affectés" (ON DELETE SET NULL). */
    public function supprimerClasse(int $id): bool
    {
        $req = $this->pdo->prepare("DELETE FROM classes WHERE id = :id");
        $req->execute(["id" => $id]);
        return $req->rowCount() > 0;
    }
}
