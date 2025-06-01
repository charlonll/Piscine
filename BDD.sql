CREATE DATABASE IF NOT EXISTS gestion_utilisateurs;
USE gestion_utilisateurs;

CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pseudo VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    nom VARCHAR(50),
    prenom VARCHAR(50),
    photo VARCHAR(255),
    image_fond VARCHAR(255),
    type ENUM('auteur','admin') DEFAULT 'auteur'
);

-- Exemples d'utilisateurs (mot de passe en clair !)
INSERT INTO utilisateurs (pseudo, email, mot_de_passe, nom, prenom, photo, type)
VALUES
('arthur', 'arthur@ece.fr', 'arthur123', 'Durand', 'Arthur', 'img/profils/arthur.png', 'auteur'),
('sophie', 'sophie@ece.fr', 'sophie123', 'Martin', 'Sophie', 'img/profils/sophie.jpg', 'auteur'),
('amine', 'amine@ece.fr', 'amine123', 'Ziani', 'Amine', 'img/profils/amine.jpg', 'auteur'),
('julie', 'julie@ece.fr', 'julie123', 'Petit', 'Julie', 'img/profils/julie.jpg', 'auteur'),
('charles', 'charles@ece.fr', 'charles123', 'Novella', 'Charles', 'img/profils/charles.jpg', 'auteur'),
('ines', 'ines@ece.fr', 'ines123', 'Dupont', 'Ines', 'img/profils/ines.jpg', 'auteur'),
('admin', 'admin@ece.fr', 'admin123', 'Administrateur', 'Super', 'img/profils/admin.jpg', 'admin');


CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    type ENUM('evenement','photo','video','texte') NOT NULL,
    fichier VARCHAR(255),
    description TEXT,
    lieu VARCHAR(255),
    date_event DATETIME,
    humeur VARCHAR(100),
);

CREATE TABLE relations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_expediteur INT NOT NULL, -- Celui qui invite
    id_destinataire INT NOT NULL, -- Celui qui reçoit
    statut ENUM('en_attente','accepte','refuse') NOT NULL DEFAULT 'en_attente',
    date_action DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO relations (id_expediteur, id_destinataire, statut)
VALUES
(1, 2, 'en_attente'),  
(3, 4, 'en_attente'); 

CREATE TABLE likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    utilisateur_id INT NOT NULL
);

CREATE TABLE commentaires (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    utilisateur_id INT NOT NULL,
    texte TEXT NOT NULL,
    date_commentaire DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expediteur_id INT NOT NULL,
    destinataire_id INT NOT NULL,
    contenu TEXT NOT NULL,
    date_message DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE offres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255),
    description TEXT,
    type ENUM('stage','emploi','alternance'),
    entreprise VARCHAR(255),
    date_publication DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Quelques offres d’exemple
INSERT INTO offres (titre, description, type, entreprise)
VALUES
('Stage Développeur Web', 'Stage de 6 mois sur Paris pour développement web fullstack PHP/JS', 'stage', 'WebTech'),
('Alternance Ingénieur IA', 'Alternance IA en apprentissage pour start-up innovante.', 'alternance', 'AIStartup'),
('CDI Ingénieur Réseau', 'CDI ingénieur réseaux, expérience 2 ans minimum.', 'emploi', 'TelecomCorp'),
('Stage Data Analyst', 'Analyse de données Big Data, outils Python/R, durée 5 mois.', 'stage', 'DataGroup');

CREATE TABLE ignores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_ignored INT NOT NULL
);
