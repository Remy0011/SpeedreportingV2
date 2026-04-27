-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : jeu. 10 avr. 2025 à 12:55
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

USE `speed_reporting`;

-- --------------------------------------------------------

--
-- Structure de la table `table_client`
--

DROP TABLE IF EXISTS `table_client`;
CREATE TABLE IF NOT EXISTS `table_client` (
  `client_id` int NOT NULL AUTO_INCREMENT,
  `client_name` varchar(100) DEFAULT NULL,
  `client_type` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`client_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `table_log`
--

DROP TABLE IF EXISTS `table_log`;
CREATE TABLE IF NOT EXISTS `table_log` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `log_action` varchar(255) DEFAULT NULL,
  `log_detail` text,
  `log_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `log_user` int NOT NULL,
  PRIMARY KEY (`log_id`),
  KEY `log_user` (`log_user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `table_project`
--

DROP TABLE IF EXISTS `table_project`;
CREATE TABLE IF NOT EXISTS `table_project` (
  `project_id` int NOT NULL AUTO_INCREMENT,
  `project_name` varchar(100) DEFAULT NULL,
  `project_description` varchar(255) DEFAULT NULL,
  `project_resource` decimal(7,2) DEFAULT NULL,
  `project_dev` int DEFAULT NULL,
  `project_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `project_start` date DEFAULT NULL,
  `project_end` date DEFAULT NULL,
  `project_realend` date DEFAULT NULL,
  `project_finish` date DEFAULT NULL,
  `project_status` enum('en_cours','termine','annule') DEFAULT NULL,
  `project_client` int DEFAULT NULL,
  `project_type` enum('travail','conges', 'maladie', 'absence') DEFAULT 'travail',
  PRIMARY KEY (`project_id`),
  KEY `project_client` (`project_client`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `table_project_user`
--

DROP TABLE IF EXISTS `table_project_user`;
CREATE TABLE IF NOT EXISTS `table_project_user` (
  `project_user_user_id` int NOT NULL,
  `project_user_project_id` int NOT NULL,
  PRIMARY KEY (`project_user_user_id`,`project_user_project_id`),
  KEY `project_user_project_id` (`project_user_project_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `table_role`
--

DROP TABLE IF EXISTS `table_role`;
CREATE TABLE IF NOT EXISTS `table_role` (
  `role_id` int NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) DEFAULT NULL,
  `role_fr` varchar(50) DEFAULT NULL,
  `role_description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`role_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `table_user`
--

DROP TABLE IF EXISTS `table_user`;
CREATE TABLE IF NOT EXISTS `table_user` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `user_email` varchar(100) NOT NULL,
  `user_firstname` varchar(50) DEFAULT NULL,
  `user_lastname` varchar(50) DEFAULT NULL,
  `user_password` varchar(255) DEFAULT NULL,
  `user_picture` varchar(255) DEFAULT NULL,
  `user_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `user_last` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user_status` enum('non_vailde','confirme','inactif') DEFAULT NULL,
  `user_token` varchar(255) DEFAULT NULL,
  `user_role` int NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_email` (`user_email`),
  KEY `user_role` (`user_role`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `table_user_preference`
--

DROP TABLE IF EXISTS `table_user_preferences`;
CREATE TABLE IF NOT EXISTS `table_user_preferences` (
  `preference_id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `key_name` VARCHAR(100) NOT NULL,
  `value` TEXT NOT NULL,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`preference_id`),
  UNIQUE KEY `unique_user_pref` (`user_id`, `key_name`),
  KEY `user_id_idx` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `table_work`
--

DROP TABLE IF EXISTS `table_work`;
CREATE TABLE IF NOT EXISTS `table_work` (
  `work_id` int NOT NULL AUTO_INCREMENT,
  `work_count` decimal(4,2) DEFAULT NULL,
  `work_week` int DEFAULT NULL,
  `work_year` int DEFAULT NULL,
  `work_day` int DEFAULT NULL,
  `work_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `work_description` varchar(255) DEFAULT NULL,
  `work_status` enum('non_vailde','confirme','inactif', 'en_attente', 'en_cours_de_creation') NOT NULL,
  `work_project` int DEFAULT NULL,
  `work_user` int NOT NULL,
  PRIMARY KEY (`work_id`),
  KEY `work_project` (`work_project`),
  KEY `work_user` (`work_user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
COMMIT;

-- --------------------------------------------------------
--
-- Insertion des données
--
-- Insertion des données dans la table "table_client"
INSERT IGNORE INTO `table_client` (`client_name`, `client_type`) VALUES
("Autre", "Autre"),
("Synapsia", "Interne");

-- Insertion des données dans la table "table_role"
INSERT IGNORE INTO `table_role` (`role_name`, `role_fr`, `role_description`) VALUES
("admin", "Administrateur", "Administrateur avec accès complet à toutes les fonctionnalités du système"),
("user", "Utilisateur", "Utilisateur standard avec accès limité aux fonctionnalités de base");

-- Insertion d'un projet inexistant avec id = 0
INSERT IGNORE INTO `table_project` (`project_id`, `project_name`, `project_description`, `project_status`, `project_client`, `project_type`) VALUES
(0, "Projet inexistant", "Projet introuvable. Probablmement supprimé ou jamais créé.", "inexistant", 2, NULL);

-- Insertion des projets d'absences
INSERT IGNORE INTO `table_project` (`project_name`, `project_description`, `project_status`, `project_client`, `project_type`) VALUES
-- Insertion d'un projet de type "conges".
("Congés", "Congés", 'en_cours', 1, 'conges'),
-- Insertion d'un projet de type "maladie".
("Maladie", "Maladie", 'en_cours', 1, 'maladie'),
-- Insertion d'un projet de type "absence".
("Absences", "Absences", 'en_cours', 1, 'absence');

-- Insertion d'un projet inexistant avec id = 0
INSERT IGNORE INTO `table_project` (`project_id`, `project_name`, `project_description`, `project_resource`, `project_start`, `project_end`, `project_status`, `project_client`) VALUES
(0, "Projet inexistant", "Projet introuvable. Probablmement supprimé ou jamais créé.", 0.00, NULL, NULL, "inexistant", NULL);

-- Insertion d'un utilisateur inexistant avec id = 0
INSERT IGNORE INTO `table_user` (`user_id`, `user_email`, `user_firstname`, `user_lastname`, `user_password`, `user_picture`, `user_status`, `user_role`) VALUES
(0, "inexistant@example.com", "Utilisateur", "Inexistant", "", NULL, "inexistant", NULL);

-- Insertion d'un utilisateur admin.
INSERT IGNORE INTO `table_user` (`user_email`, `user_firstname`, `user_lastname`, `user_password`, `user_picture`, `user_status`, `user_role`) VALUES
("admin@example.com", "Administrateur", "Example", "$2y$10$Epq7QMvLoQDq.30jZ8Si5emKiDtrqpcxTJyPbz8z700LbxqQbPFzG", "https://api.dicebear.com/9.x/identicon/svg?row1=xoxox&row2=xxxxx&row3=xxoxx&row4=xooox&row5=ooxoo&size=100&scale=75&backgroundColor=D4EFDF&rowColor=FFD54F", "confirme", 1);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
