-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : lun. 16 mars 2026 à 01:41
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `gestion_upf_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `etudiant_id` varchar(10) NOT NULL,
  `type_doc` enum('releve_notes','attestation','autre') NOT NULL,
  `nom_fichier` varchar(255) NOT NULL,
  `chemin` varchar(255) NOT NULL,
  `taille` int(11) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `uploaded_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `documents`
--

INSERT INTO `documents` (`id`, `etudiant_id`, `type_doc`, `nom_fichier`, `chemin`, `taille`, `mime_type`, `uploaded_by`, `uploaded_at`) VALUES
(1, 'E005', 'releve_notes', 'PDO.pdf', 'uploads/documents/doc_E005_1772494041.pdf', 464829, 'application/pdf', 16, '2026-03-02 23:27:21'),
(2, 'E004', 'releve_notes', 'TP3-Matériaux et textures 1.pdf', 'uploads/documents/doc_E004_1772545546.pdf', 1426944, 'application/pdf', 16, '2026-03-03 13:45:46');

-- --------------------------------------------------------

--
-- Structure de la table `etudiants`
--

CREATE TABLE `etudiants` (
  `Code` varchar(10) NOT NULL,
  `Nom` varchar(50) NOT NULL,
  `Prenom` varchar(50) NOT NULL,
  `Filiere` varchar(10) DEFAULT NULL,
  `FNote` decimal(4,2) DEFAULT NULL,
  `Photo` varchar(255) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `etudiants`
--

INSERT INTO `etudiants` (`Code`, `Nom`, `Prenom`, `Filiere`, `FNote`, `Photo`, `date_naissance`, `email`, `telephone`, `created_at`) VALUES
('E001', 'ALAOUI', 'Ahmed', 'GINFO', 15.50, NULL, '2000-01-15', 'ahmed.alaoui@example.com', '0612345678', '2026-03-02 11:39:26'),
('E002', 'BENNANI', 'Fatima', 'GINFO', 8.50, NULL, '2001-03-22', 'fatima.bennani@example.com', '0623456789', '2026-03-02 11:39:26'),
('E003', 'CHRAIBI', 'Youssef', 'GINDUS', 20.00, NULL, '2000-11-10', 'youssef.chraibi@example.com', '0634567890', '2026-03-02 11:39:26'),
('E004', 'DAOUDI', 'Khadija', 'GINDUS', 17.00, NULL, '2001-07-18', 'khadija.daoudi@example.com', '0645678901', '2026-03-02 11:39:26'),
('E005', 'EL AMRANI', 'Omar', 'GSTR', 14.00, 'uploads/photos/photo_E005_1772494014.png', '2000-09-05', 'radouane.asri99@gmail.com', '0656789012', '2026-03-02 11:39:26');

-- --------------------------------------------------------

--
-- Structure de la table `filieres`
--

CREATE TABLE `filieres` (
  `CodeF` varchar(10) NOT NULL,
  `IntituleF` varchar(100) NOT NULL,
  `responsable` varchar(100) DEFAULT NULL,
  `nbPlaces` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `filieres`
--

INSERT INTO `filieres` (`CodeF`, `IntituleF`, `responsable`, `nbPlaces`, `created_at`) VALUES
('GINDUS', 'Génie Industriel', 'Pr. EL FALLAH', 45, '2026-03-02 11:39:26'),
('GINFO', 'Génie Informatique', 'Pr. KZADRI', 50, '2026-03-02 11:39:26'),
('GSTR', 'Gestion', 'Pr. BENALI', 60, '2026-03-02 11:39:26');

-- --------------------------------------------------------

--
-- Structure de la table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(4) DEFAULT 0,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`, `used`, `created_at`) VALUES
(11, 'radouane.asri99@gmail.com', 'b536cffc27aaa56d698bf9daca03458acfc0a98d35fa2774bd5fdcc7a5f7e466', '2026-03-13 18:11:46', 1, '2026-03-13 16:11:46');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL,
  `login` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `role` enum('admin','user') NOT NULL,
  `etudiant_id` varchar(10) DEFAULT NULL,
  `derniere_connexion` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `login`, `password`, `photo`, `role`, `etudiant_id`, `derniere_connexion`, `created_at`) VALUES
(1, 'radouane.asri1996@gmail.com', '$2y$10$BhQWQgJr2ecwaYRRr1uoiuO5LmflQWsgGVBr5AoeERXDLpcMHCDsu', NULL, 'admin', NULL, '2026-03-02 14:45:35', '2026-03-02 11:39:26'),
(2, 'ahmed.alaoui', '$2y$10$FAc2IqAotufaeClQ7HTWgepiMw5dNuGAt6lj9G5Mx2Den9nDob1Ri', NULL, 'user', 'E001', NULL, '2026-03-02 11:39:26'),
(3, 'fatima.bennani', '$2y$10$FAc2IqAotufaeClQ7HTWgepiMw5dNuGAt6lj9G5Mx2Den9nDob1Ri', NULL, 'user', 'E002', NULL, '2026-03-02 11:39:26'),
(7, 'youssef.chraibi', '$2y$10$Aczn8p6/u9M53xpstzGsf.uXrn0URIjBxT0vMQ30ikI7hmXf5hiU2', NULL, 'user', 'E003', '2026-03-02 23:29:19', '2026-03-02 12:51:29'),
(8, 'khadija.daoudi', '$2y$10$Aczn8p6/u9M53xpstzGsf.uXrn0URIjBxT0vMQ30ikI7hmXf5hiU2', NULL, 'user', 'E004', '2026-03-03 13:46:42', '2026-03-02 12:51:29'),
(9, 'omar.elamrani', '$2y$10$Br6lkea8ku9H9lrFc9fuRe.OBfbN6Cl.PK.MpTL4Hlp.ldhvO1zCG', NULL, 'user', 'E005', '2026-03-03 00:09:56', '2026-03-02 12:51:29'),
(16, 'radouane.asri99@gmail.com', '$2y$10$WCRF4DFUxr3QpvBhofJWOujnJLLolY3krWPrm128.o2aBgroJ3JOu', 'uploads/photos/admin_16_1773417251.jpg', 'admin', NULL, '2026-03-13 16:12:58', '2026-03-02 14:48:47'),
(32, 'Radouane.elasri@usmba.ac.ma', '$2y$10$x1t3hW/o0/XTKUh4tMl06eI5UAC2gYpneOaY08.C3cyDQaIO3KFS.', NULL, 'admin', NULL, '2026-03-03 00:17:28', '2026-03-02 22:31:12');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `etudiant_id` (`etudiant_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Index pour la table `etudiants`
--
ALTER TABLE `etudiants`
  ADD PRIMARY KEY (`Code`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `Filiere` (`Filiere`);

--
-- Index pour la table `filieres`
--
ALTER TABLE `filieres`
  ADD PRIMARY KEY (`CodeF`);

--
-- Index pour la table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `email` (`email`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`),
  ADD UNIQUE KEY `etudiant_id` (`etudiant_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`etudiant_id`) REFERENCES `etudiants` (`Code`) ON DELETE CASCADE,
  ADD CONSTRAINT `documents_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `utilisateurs` (`id`);

--
-- Contraintes pour la table `etudiants`
--
ALTER TABLE `etudiants`
  ADD CONSTRAINT `etudiants_ibfk_1` FOREIGN KEY (`Filiere`) REFERENCES `filieres` (`CodeF`) ON DELETE SET NULL;

--
-- Contraintes pour la table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`email`) REFERENCES `etudiants` (`email`) ON DELETE CASCADE;

--
-- Contraintes pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD CONSTRAINT `utilisateurs_ibfk_1` FOREIGN KEY (`etudiant_id`) REFERENCES `etudiants` (`Code`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
