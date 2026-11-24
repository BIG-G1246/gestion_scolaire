SET NAMES utf8mb4;

DROP DATABASE IF EXISTS gestion_scolaire;
CREATE DATABASE gestion_scolaire
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE gestion_scolaire;

CREATE TABLE membres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(50) NOT NULL UNIQUE,
    mdp_hash VARCHAR(255) NOT NULL,
    role ENUM('membre','admin') NOT NULL DEFAULT 'membre',
    cree_le DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE etudiants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matricule VARCHAR(20) NOT NULL UNIQUE,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    email VARCHAR(120) DEFAULT NULL,
    tel VARCHAR(30) DEFAULT NULL,
    sexe ENUM('Masculin','Feminin') DEFAULT NULL,
    photo VARCHAR(255) DEFAULT NULL,
    classe_id INT DEFAULT NULL,
    inscrit_le DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_etudiants_classe
        FOREIGN KEY (classe_id) REFERENCES classes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE matieres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(80) NOT NULL UNIQUE,
    coefficient TINYINT UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    etudiant_id INT NOT NULL,
    matiere_id INT NOT NULL,
    valeur DECIMAL(4,2) NOT NULL,
    CONSTRAINT uq_note UNIQUE (etudiant_id, matiere_id),
    CONSTRAINT ck_note CHECK (valeur BETWEEN 0 AND 20),
    CONSTRAINT fk_notes_etudiant
        FOREIGN KEY (etudiant_id) REFERENCES etudiants(id) ON DELETE CASCADE,
    CONSTRAINT fk_notes_matiere
        FOREIGN KEY (matiere_id) REFERENCES matieres(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;