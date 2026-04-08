-- =============================================================
-- StageConnect - Database Schema
-- Project: Internship Management Platform
-- Technology: MySQL
-- =============================================================

-- Create and select the database
CREATE DATABASE IF NOT EXISTS stageconnect CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE stageconnect;

-- =============================================================
-- TABLE: users
-- Stores all registered users (students + admins)
-- =============================================================
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,           -- bcrypt hashed
    role        ENUM('student', 'admin') DEFAULT 'student',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =============================================================
-- TABLE: offers
-- Internship offers posted by admins
-- =============================================================
CREATE TABLE IF NOT EXISTS offers (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    company     VARCHAR(150) NOT NULL,
    location    VARCHAR(100) DEFAULT 'Tunisie',
    domain      VARCHAR(100) DEFAULT 'Informatique',
    duration    VARCHAR(50)  DEFAULT '2 mois',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =============================================================
-- TABLE: applications
-- Student applications for internship offers
-- =============================================================
CREATE TABLE IF NOT EXISTS applications (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    offer_id    INT NOT NULL,
    cv          VARCHAR(255),                    -- uploaded CV filename
    status      ENUM('en attente', 'acceptée', 'refusée') DEFAULT 'en attente',
    applied_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Foreign keys enforce referential integrity
    FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
    FOREIGN KEY (offer_id) REFERENCES offers(id) ON DELETE CASCADE,

    -- Prevent duplicate applications
    UNIQUE KEY unique_application (user_id, offer_id)
) ENGINE=InnoDB;

-- =============================================================
-- SAMPLE DATA — Admin account
-- Password: Admin1234 (bcrypt hashed)
-- =============================================================
INSERT INTO users (name, email, password, role) VALUES
('Administrateur', 'admin@stageconnect.tn',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- Note: above hash = "password" for demo — change in production!

-- =============================================================
-- SAMPLE DATA — Student accounts
-- Password: Student123
-- =============================================================
INSERT INTO users (name, email, password, role) VALUES
('Hajjej Mouheb',  'mouheb@email.tn',
 '$2y$10$TKh8H1.PfjfCU1uwr03G.uKTCcZTIAKMGWFIHrZQxVIYRPJKJBum2', 'student'),
('Abbessi Jesser',  'jesser@email.tn',
 '$2y$10$TKh8H1.PfjfCU1uwr03G.uKTCcZTIAKMGWFIHrZQxVIYRPJKJBum2', 'student'),
('Sara Ben Ali',   'sara@email.tn',
 '$2y$10$TKh8H1.PfjfCU1uwr03G.uKTCcZTIAKMGWFIHrZQxVIYRPJKJBum2', 'student');

-- =============================================================
-- SAMPLE DATA — Internship offers
-- =============================================================
INSERT INTO offers (title, description, company, location, domain, duration) VALUES
(
  'Développeur Web Front-End',
  'Nous recherchons un stagiaire passionné par le développement web pour rejoindre notre équipe dynamique. Vous travaillerez sur des projets modernes utilisant HTML5, CSS3 et JavaScript. Vous participerez à la création d''interfaces utilisateurs innovantes et responsive.',
  'TechTunisia',
  'Tunis',
  'Développement Web',
  '3 mois'
),
(
  'Stage en Cybersécurité',
  'Rejoignez notre équipe sécurité pour découvrir le monde passionnant de la cybersécurité. Vous apprendrez les techniques de pentest, l''analyse des vulnérabilités, et la mise en place de politiques de sécurité au sein d''une entreprise leader.',
  'SecureNet Solutions',
  'Sfax',
  'Sécurité Informatique',
  '2 mois'
),
(
  'Stagiaire Data Analyst',
  'Vous rejoindrez notre département data pour analyser des ensembles de données volumineuses. Maîtrise de Python et des bases SQL requises. Vous travaillerez sur des dashboards et des rapports analytiques pour aider à la prise de décision.',
  'DataVision Tunisia',
  'Tunis',
  'Data Science',
  '4 mois'
),
(
  'Développeur Mobile iOS/Android',
  'Stage passionnant dans le domaine du développement mobile. Vous participerez au développement d''applications mobiles cross-platform. Connaissance de Flutter ou React Native est un plus. Bonne ambiance et encadrement professionnel garantis.',
  'MobileTech Tunis',
  'La Marsa',
  'Développement Mobile',
  '3 mois'
),
(
  'Stage en Intelligence Artificielle',
  'Intégrez notre laboratoire IA pour travailler sur des projets de machine learning et de traitement du langage naturel. Vous utiliserez Python, TensorFlow et des datasets réels pour développer des modèles prédictifs innovants.',
  'AI Lab Tunisia',
  'Tunis',
  'Intelligence Artificielle',
  '6 mois'
),
(
  'Stagiaire Réseaux & Systèmes',
  'Au sein de notre équipe infrastructure, vous administrerez des serveurs Linux, configurerez des équipements réseau Cisco et participerez à la supervision du parc informatique. Stage idéal pour les étudiants en Réseaux & Télécoms.',
  'NetAdmin Pro',
  'Sousse',
  'Réseaux',
  '2 mois'
);

-- =============================================================
-- SAMPLE DATA — One application (demo)
-- =============================================================
INSERT INTO applications (user_id, offer_id, cv, status) VALUES
(2, 1, 'cv_mouheb.pdf', 'en attente'),
(3, 3, 'cv_jesser.pdf', 'acceptée');
