-- Mise à jour d'une base EXISTANTE, sans rien effacer.
-- À lancer une seule fois :
--   mysql -u root --default-character-set=utf8mb4 gestion_scolaire < sql\migration_espaces.sql
SET NAMES utf8mb4;
USE gestion_scolaire;

-- 1) Mot de passe de l'espace étudiant
ALTER TABLE etudiants ADD COLUMN mdp_hash VARCHAR(255) DEFAULT NULL AFTER classe_id;

-- 2) Notes par trimestre. Les notes déjà saisies deviennent celles du 1er trimestre.
--    On crée la nouvelle contrainte AVANT de supprimer l'ancienne : la clé étrangère
--    sur etudiant_id a toujours besoin d'un index.
ALTER TABLE notes ADD COLUMN trimestre TINYINT UNSIGNED NOT NULL DEFAULT 1 AFTER matiere_id;
ALTER TABLE notes ADD CONSTRAINT uq_note_trimestre UNIQUE (etudiant_id, matiere_id, trimestre);
ALTER TABLE notes DROP INDEX uq_note;
ALTER TABLE notes ADD CONSTRAINT ck_trimestre CHECK (trimestre BETWEEN 1 AND 3);

-- 3) Nouvelles tables
-- Tables des espaces directeur / secrétaire / étudiant.


-- Emploi du temps : un cours = une matière, pour une classe, un jour, une heure.
CREATE TABLE IF NOT EXISTS cours (
    id INT AUTO_INCREMENT PRIMARY KEY,
    classe_id INT NOT NULL,
    matiere_id INT NOT NULL,
    jour TINYINT UNSIGNED NOT NULL,          -- 1 = lundi ... 6 = samedi
    heure_debut TIME NOT NULL,
    heure_fin TIME NOT NULL,
    salle VARCHAR(30) DEFAULT NULL,
    enseignant VARCHAR(80) DEFAULT NULL,
    CONSTRAINT ck_cours_jour CHECK (jour BETWEEN 1 AND 6),
    CONSTRAINT ck_cours_heures CHECK (heure_fin > heure_debut),
    CONSTRAINT fk_cours_classe FOREIGN KEY (classe_id) REFERENCES classes(id) ON DELETE CASCADE,
    CONSTRAINT fk_cours_matiere FOREIGN KEY (matiere_id) REFERENCES matieres(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Compositions (examens) : date et heure, par classe et par matière.
CREATE TABLE IF NOT EXISTS compositions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    classe_id INT NOT NULL,
    matiere_id INT NOT NULL,
    trimestre TINYINT UNSIGNED NOT NULL,
    date_compo DATE NOT NULL,
    heure_debut TIME NOT NULL,
    heure_fin TIME NOT NULL,
    salle VARCHAR(30) DEFAULT NULL,
    CONSTRAINT ck_compo_trimestre CHECK (trimestre BETWEEN 1 AND 3),
    CONSTRAINT ck_compo_heures CHECK (heure_fin > heure_debut),
    CONSTRAINT fk_compo_classe FOREIGN KEY (classe_id) REFERENCES classes(id) ON DELETE CASCADE,
    CONSTRAINT fk_compo_matiere FOREIGN KEY (matiere_id) REFERENCES matieres(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Caisse de l'établissement (réservée au directeur) : tout l'argent qui entre et qui sort.
CREATE TABLE IF NOT EXISTS mouvements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('entree','sortie') NOT NULL,
    montant INT UNSIGNED NOT NULL,           -- en FCFA, sans centimes
    motif VARCHAR(150) NOT NULL,
    etudiant_id INT DEFAULT NULL,            -- rempli pour un paiement de scolarité
    date_mvt DATE NOT NULL,
    cree_par VARCHAR(50) NOT NULL,
    cree_le DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT ck_montant CHECK (montant > 0),
    CONSTRAINT fk_mvt_etudiant FOREIGN KEY (etudiant_id) REFERENCES etudiants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Journal d'activité : qui a fait quoi, quand, et depuis quelle adresse.
CREATE TABLE IF NOT EXISTS journal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur VARCHAR(50) NOT NULL,
    role VARCHAR(10) NOT NULL,
    action VARCHAR(255) NOT NULL,
    ip VARCHAR(45) DEFAULT NULL,
    cree_le DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_journal_date (cree_le)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
