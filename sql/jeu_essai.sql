SET NAMES utf8mb4;
USE gestion_scolaire;

INSERT INTO classes (nom) VALUES ('6e A'), ('3e B'), ('Terminale D');

INSERT INTO matieres (nom, coefficient) VALUES
('Mathématiques', 4), ('Français', 3), ('Anglais', 2),
('Histoire-Géographie', 2), ('Sciences physiques', 3);

INSERT INTO etudiants (matricule, nom, prenom, sexe, classe_id) VALUES
('E2026001', 'BALOSSA',  'Junior',  'Masculin', 1),
('E2026002', 'MALONGA',  'Marie',   'Feminin',  1),
('E2026003', 'NGOMA',    'Paul',    'Masculin', 2),
('E2026004', 'BAKALA',   'Christ',  'Masculin', 2),
('E2026005', 'BABAKILA', 'Gylvine', 'Feminin',  3),
('E2026006', 'BAYIS',    'Mignon',  'Feminin',  NULL);

INSERT INTO notes (etudiant_id, matiere_id, valeur) VALUES
(1,1,14), (1,2,12.5), (1,3,15), (1,4,11), (1,5,13),
(2,1,16), (2,2,15.5), (2,3,17), (2,4,14), (2,5,15.5),
(3,1,8),  (3,2,9.5),  (3,3,10), (3,4,7),  (3,5,9);