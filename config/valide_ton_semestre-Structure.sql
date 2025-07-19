-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mar. 15 juil. 2025 à 20:54
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `valide_ton_semestre`
--

-- --------------------------------------------------------

--
-- Structure de la table `matieres`
--

DROP TABLE IF EXISTS `matieres`;
CREATE TABLE IF NOT EXISTS `matieres` (
  `id` int NOT NULL,
  `UE_id` int DEFAULT NULL,
  `Nom_Matiere` varchar(255) DEFAULT NULL,
  `coefficient` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `matiere_calcul`
--

DROP TABLE IF EXISTS `matiere_calcul`;
CREATE TABLE IF NOT EXISTS `matiere_calcul` (
  `id` int NOT NULL AUTO_INCREMENT,
  `result_id` int DEFAULT NULL,
  `field_name` varchar(100) DEFAULT NULL,
  `field_Grade` float DEFAULT NULL,
  `UE` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `resultat_calcul`
--

DROP TABLE IF EXISTS `resultat_calcul`;
CREATE TABLE IF NOT EXISTS `resultat_calcul` (
  `id` int NOT NULL AUTO_INCREMENT,
  `semester` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `goal` decimal(5,2) DEFAULT NULL,
  `average` decimal(5,2) DEFAULT NULL,
  `needed` decimal(5,2) DEFAULT NULL,
  `ue` varchar(50) DEFAULT NULL,
  `DATE` date DEFAULT (curdate()),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `semestre_choix`
--

DROP TABLE IF EXISTS `semestre_choix`;
CREATE TABLE IF NOT EXISTS `semestre_choix` (
  `semester_id` varchar(50) NOT NULL,
  `name` varchar(10) NOT NULL,
  `path` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `ue`
--

DROP TABLE IF EXISTS `ue`;
CREATE TABLE IF NOT EXISTS `ue` (
  `id` int NOT NULL,
  `Nom_UE` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `ues_choix`
--

DROP TABLE IF EXISTS `ues_choix`;
CREATE TABLE IF NOT EXISTS `ues_choix` (
  `id` varchar(10) NOT NULL,
  `semester_id` varchar(50) NOT NULL,
  `name_field` varchar(255) NOT NULL,
  `coefficient_field` float NOT NULL,
  `UE` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
