<?php
// app/models/StatModel.php
// Statistiques des tableaux de bord. Uniquement des lectures (SELECT) : COUNT, SUM, AVG, GROUP BY.

class StatModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /** Effectif total, filles, garçons, non renseigné, sans classe. */
    public function effectifs(): array
    {
        return $this->pdo->query(
            "SELECT COUNT(*) AS total,
                    SUM(sexe = 'Feminin')  AS filles,
                    SUM(sexe = 'Masculin') AS garcons,
                    SUM(sexe IS NULL)      AS sexe_inconnu,
                    SUM(classe_id IS NULL) AS sans_classe
             FROM etudiants"
        )->fetch();
    }

    /**
     * Moyenne de chaque étudiant noté pour un trimestre (sous-requête), puis on travaille dessus.
     * Sert à la moyenne de l'établissement, au taux de réussite et aux classements.
     */
    private function sqlMoyennes(): string
    {
        return "SELECT e.id, e.matricule, e.nom, e.prenom, e.sexe, e.classe_id,
                       ROUND(SUM(n.valeur * m.coefficient) / SUM(m.coefficient), 2) AS moyenne
                FROM etudiants e
                JOIN notes n ON n.etudiant_id = e.id AND n.trimestre = :trimestre
                JOIN matieres m ON m.id = n.matiere_id
                GROUP BY e.id, e.matricule, e.nom, e.prenom, e.sexe, e.classe_id";
    }

    /** Moyenne de l'établissement, nombre d'élèves classés, nombre d'admis (>= 10). */
    public function resultatsGlobaux(int $trimestre): array
    {
        $req = $this->pdo->prepare(
            "SELECT ROUND(AVG(t.moyenne), 2) AS moyenne,
                    COUNT(*) AS classes,
                    SUM(t.moyenne >= 10) AS admis,
                    MAX(t.moyenne) AS meilleure,
                    MIN(t.moyenne) AS plus_faible
             FROM (" . $this->sqlMoyennes() . ") AS t"
        );
        $req->execute(["trimestre" => $trimestre]);
        return $req->fetch();
    }

    /** Par classe : effectif, filles, garçons, moyenne, admis. LEFT JOIN : une classe vide apparaît. */
    public function parClasse(int $trimestre): array
    {
        $req = $this->pdo->prepare(
            "SELECT c.id, c.nom,
                    COUNT(e.id) AS effectif,
                    SUM(e.sexe = 'Feminin')  AS filles,
                    SUM(e.sexe = 'Masculin') AS garcons,
                    ROUND(AVG(t.moyenne), 2) AS moyenne,
                    SUM(t.moyenne >= 10) AS admis,
                    COUNT(t.id) AS classes
             FROM classes c
             LEFT JOIN etudiants e ON e.classe_id = c.id
             LEFT JOIN (" . $this->sqlMoyennes() . ") AS t ON t.id = e.id
             GROUP BY c.id, c.nom
             ORDER BY c.nom"
        );
        $req->execute(["trimestre" => $trimestre]);
        return $req->fetchAll();
    }

    /** Moyenne et taux de réussite par matière. */
    public function parMatiere(int $trimestre): array
    {
        $req = $this->pdo->prepare(
            "SELECT m.nom, m.coefficient,
                    ROUND(AVG(n.valeur), 2) AS moyenne,
                    COUNT(n.id) AS nb_notes,
                    SUM(n.valeur >= 10) AS admis
             FROM matieres m
             LEFT JOIN notes n ON n.matiere_id = m.id AND n.trimestre = :trimestre
             GROUP BY m.id, m.nom, m.coefficient
             ORDER BY moyenne DESC"
        );
        $req->execute(["trimestre" => $trimestre]);
        return $req->fetchAll();
    }

    /** Meilleurs élèves ($ordre = DESC) ou élèves en difficulté ($ordre = ASC). */
    public function classement(int $trimestre, string $ordre = "DESC", int $limite = 5): array
    {
        // $ordre vient du code, jamais de l'utilisateur ; on le vérifie quand même (liste blanche).
        $ordre = $ordre === "ASC" ? "ASC" : "DESC";
        $req = $this->pdo->prepare(
            "SELECT t.*, c.nom AS classe
             FROM (" . $this->sqlMoyennes() . ") AS t
             LEFT JOIN classes c ON c.id = t.classe_id
             ORDER BY t.moyenne $ordre
             LIMIT " . max(1, $limite)
        );
        $req->execute(["trimestre" => $trimestre]);
        return $req->fetchAll();
    }

    /** Dernières inscriptions, avec la date. */
    public function dernieresInscriptions(int $limite = 8): array
    {
        return $this->pdo->query(
            "SELECT e.matricule, e.nom, e.prenom, e.sexe, e.inscrit_le, c.nom AS classe
             FROM etudiants e
             LEFT JOIN classes c ON c.id = e.classe_id
             ORDER BY e.inscrit_le DESC, e.id DESC
             LIMIT " . max(1, $limite)
        )->fetchAll();
    }

    /** Inscriptions par mois (12 derniers mois), pour un petit graphique. */
    public function inscriptionsParMois(): array
    {
        return $this->pdo->query(
            "SELECT DATE_FORMAT(inscrit_le, '%Y-%m') AS mois, COUNT(*) AS nombre
             FROM etudiants
             WHERE inscrit_le >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
             GROUP BY mois
             ORDER BY mois"
        )->fetchAll();
    }

    /** Nombre d'étudiants qui ont un accès à l'espace étudiant. */
    public function accesEtudiants(): int
    {
        return (int) $this->pdo->query("SELECT COUNT(*) FROM etudiants WHERE mdp_hash IS NOT NULL")->fetchColumn();
    }
}
