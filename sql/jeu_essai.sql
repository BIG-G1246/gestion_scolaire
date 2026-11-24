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
-- Les notes ci-dessus sont celles du 1er trimestre (valeur par défaut de la colonne trimestre).

-- Étudiants supplémentaires (pour des statistiques plus parlantes)
INSERT INTO etudiants (matricule, nom, prenom, sexe, classe_id, inscrit_le) VALUES
('E2026007', 'MOUKOKO',  'Grâce',   'Feminin',  1, '2026-09-08 09:15:00'),
('E2026008', 'ITOUA',    'Kevin',   'Masculin', 2, '2026-09-09 10:30:00'),
('E2026009', 'OKEMBA',   'Divine',  'Feminin',  3, '2026-09-10 08:45:00'),
('E2026010', 'MBEMBA',   'Rodrigue','Masculin', 3, '2026-09-15 14:00:00');

INSERT INTO notes (etudiant_id, matiere_id, trimestre, valeur) VALUES
(7,1,1,11), (7,2,1,13), (7,3,1,12), (7,4,1,10.5), (7,5,1,9),
(4,1,1,12), (4,2,1,11), (4,3,1,14), (4,4,1,13), (4,5,1,12.5),
(8,1,1,6.5), (8,2,1,10), (8,3,1,9), (8,4,1,11), (8,5,1,8),
(5,1,1,17), (5,2,1,14), (5,3,1,16), (5,4,1,15), (5,5,1,18),
(9,1,1,13), (9,2,1,12), (9,3,1,11), (9,4,1,14), (9,5,1,12),
(10,1,1,9), (10,2,1,8.5), (10,3,1,12), (10,4,1,10), (10,5,1,11),
-- 2e trimestre (en cours) pour la 6e A
(1,1,2,15), (1,2,2,13), (1,3,2,14),
(2,1,2,17), (2,2,2,16), (2,3,2,18),
(7,1,2,12), (7,2,2,12.5), (7,3,2,13);

-- Emploi du temps (jour : 1 = lundi ... 6 = samedi)
INSERT INTO cours (classe_id, matiere_id, jour, heure_debut, heure_fin, salle, enseignant) VALUES
(1,1,1,'07:30','09:30','Salle 1','M. NKOUKA'),  (1,2,1,'10:00','12:00','Salle 1','Mme LOEMBA'),
(1,3,2,'07:30','09:00','Salle 1','M. SAMBA'),   (1,5,2,'09:30','11:30','Labo','M. OBA'),
(1,4,3,'07:30','09:30','Salle 1','Mme NGOLO'),  (1,1,4,'07:30','09:30','Salle 1','M. NKOUKA'),
(1,2,5,'07:30','09:30','Salle 1','Mme LOEMBA'), (1,5,5,'10:00','12:00','Labo','M. OBA'),
(2,1,1,'07:30','09:30','Salle 4','M. NKOUKA'),  (2,5,2,'07:30','09:30','Labo','M. OBA'),
(2,2,3,'10:00','12:00','Salle 4','Mme LOEMBA'), (2,3,4,'07:30','09:00','Salle 4','M. SAMBA'),
(3,1,1,'10:00','12:00','Salle 7','M. NKOUKA'),  (3,5,3,'07:30','10:30','Labo','M. OBA');

-- Compositions
INSERT INTO compositions (classe_id, matiere_id, trimestre, date_compo, heure_debut, heure_fin, salle) VALUES
(1,1,2,'2026-12-07','08:00','10:00','Salle 1'), (1,2,2,'2026-12-07','10:30','12:30','Salle 1'),
(1,5,2,'2026-12-08','08:00','10:00','Labo'),    (1,3,2,'2026-12-09','08:00','09:30','Salle 1'),
(2,1,2,'2026-12-07','08:00','10:00','Salle 4'), (3,1,2,'2026-12-08','08:00','11:00','Salle 7');

-- Caisse (FCFA)
INSERT INTO mouvements (type, montant, motif, etudiant_id, date_mvt, cree_par) VALUES
('entree', 75000, 'Frais de scolarité 1er trimestre', 1, '2026-09-10', 'directeur'),
('entree', 75000, 'Frais de scolarité 1er trimestre', 2, '2026-09-11', 'directeur'),
('entree', 85000, 'Frais de scolarité 1er trimestre', 5, '2026-09-12', 'directeur'),
('entree', 40000, 'Frais de scolarité (acompte)',     3, '2026-10-02', 'directeur'),
('sortie', 120000, 'Salaires vacataires octobre',  NULL, '2026-10-31', 'directeur'),
('sortie', 35000,  'Craie, rames de papier, encre',NULL, '2026-11-05', 'directeur'),
('entree', 75000, 'Frais de scolarité 2e trimestre', 2, '2026-11-18', 'directeur');
