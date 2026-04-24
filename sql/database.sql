CREATE DATABASE IF NOT EXISTS stageconnect
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE stageconnect;

DROP TABLE IF EXISTS applications;
DROP TABLE IF EXISTS portfolios;
DROP TABLE IF EXISTS offers;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('student', 'admin') NOT NULL DEFAULT 'student',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE offers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(180) NOT NULL,
  company VARCHAR(150) NOT NULL,
  location VARCHAR(150) NOT NULL,
  duration VARCHAR(80) NOT NULL,
  description TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE applications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  offer_id INT UNSIGNED NOT NULL,
  status ENUM('pending', 'accepted', 'rejected') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_applications_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_applications_offer
    FOREIGN KEY (offer_id) REFERENCES offers(id)
    ON DELETE CASCADE,
  UNIQUE KEY unique_application (user_id, offer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE portfolios (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  phone VARCHAR(50) DEFAULT NULL,
  bio TEXT DEFAULT NULL,
  skills TEXT DEFAULT NULL,
  education TEXT DEFAULT NULL,
  experience TEXT DEFAULT NULL,
  languages TEXT DEFAULT NULL,
  cv_path VARCHAR(255) DEFAULT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_portfolios_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE,
  UNIQUE KEY unique_portfolio_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO offers (title, company, location, duration, description) VALUES
('Stage Développeur PHP / MySQL', 'TechNova', 'Tunis', '4 mois', 'Participation au développement d\'une application web interne en PHP natif avec base MySQL, amélioration de l\'interface et maintenance des modules métier.'),
('Stage Front-end Web', 'Digital Bridge', 'Sfax', '3 mois', 'Conception d\'interfaces responsives en HTML, CSS et JavaScript pour un portail de services numériques orienté expérience utilisateur.'),
('Stage Data & Reporting', 'Insight Consulting', 'Nabeul', '2 mois', 'Aide à la structuration de tableaux de bord, préparation de rapports analytiques et automatisation de traitements simples autour des données de suivi.');

-- Après la création d'un compte étudiant, vous pouvez promouvoir un administrateur avec :
-- UPDATE users SET role = 'admin' WHERE email = 'votre-email@example.com';
