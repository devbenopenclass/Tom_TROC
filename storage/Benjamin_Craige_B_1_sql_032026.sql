-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : mer. 01 avr. 2026 à 23:41
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
-- Base de données : `tomtroc`
--

-- --------------------------------------------------------

--
-- Structure de la table `books`
--

CREATE TABLE `books` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `status` enum('available','unavailable','reserved') NOT NULL DEFAULT 'available',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `books`
--

INSERT INTO `books` (`id`, `user_id`, `title`, `author`, `image`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 301, 'Esther', 'Alabaster', '/assets/img/exchange-covers/esther.png', 'Un ouvrage contemplatif propose par CamilleDuCuir, autour de paysages apaises et d une mise en page epuree.', 'available', '2026-03-15 16:58:42', NULL),
(2, 302, 'The Kinfolk Table', 'Nathan Williams', '/assets/img/exchange-covers/the-kinfolk-table.png', 'J\'ai récemment plongé dans les pages de \'The Kinfolk Table\' et j\'ai été enchanté par cette œuvre captivante. Ce livre va bien au-delà d\'une simple collection de recettes : il célèbre l\'art de partager des moments authentiques autour de la table.\r\n\r\nLes photographies magnifiques et le ton chaleureux captivent dès le départ, transportant le lecteur dans un voyage à travers des recettes et des histoires qui mettent en avant la beauté de la simplicité et de la convivialité.\r\n\r\nChaque page est une invitation à ralentir, à savourer et à créer des souvenirs durables avec les êtres chers.\r\n\r\n\'The Kinfolk Table\' incarne parfaitement l\'esprit de la cuisine et de la camaraderie, et il est certain que ce livre trouvera une place spéciale dans le cœur de tout amoureux de la cuisine et des rencontres inspirantes.', 'available', '2026-03-15 16:58:42', NULL),
(3, 303, 'Wabi Sabi', 'Beth Kempton', '/assets/img/exchange-covers/wabi-sabi.png', 'Un livre delicat sur la simplicite, la lenteur et la beaute de l imperfection, partage par Alicecture.', 'available', '2026-03-15 16:58:42', NULL),
(4, 304, 'Milk & honey', 'Rupi Kaur', '/assets/img/exchange-covers/milk-and-honey.png', '<script>alert(\'Faille XSS !\')</script>', 'available', '2026-03-15 16:58:42', NULL),
(5, 305, 'Delight!', 'Justin Rossow', '/assets/img/exchange-covers/delight.png', 'Un ouvrage graphique et energique autour de l experience, du plaisir et de la creation, actuellement non disponible.', 'unavailable', '2026-03-15 16:58:42', NULL),
(6, 306, 'Milwaukee Mission', 'Elder Cooper Low', '/assets/img/exchange-covers/milwaukee-mission.png', 'Une couverture sobre pour un livre au ton plus interieur, propose par Christiane75014.', 'available', '2026-03-15 16:58:42', NULL),
(7, 307, 'Minimalist Graphics', 'Julia Schonlau', '/assets/img/exchange-covers/minimalist-graphics.png', 'Un livre de design editorial et de composition minimaliste partage par Hamzalecture.', 'available', '2026-03-15 16:58:42', NULL),
(8, 308, 'Hygge', 'Meik Wiking', '/assets/img/exchange-covers/hygge.png', 'Le guide du confort et de l art de vivre danois, dans une edition chaleureuse, disponible chez Hugo1990_12.', 'available', '2026-03-15 16:58:42', NULL),
(9, 309, 'Innovation', 'Matt Ridley', '/assets/img/exchange-covers/innovation.png', 'Une lecture vive et lumineuse sur l invention, les idees et le progres, proposee par LouBen50.', 'available', '2026-03-15 16:58:42', NULL),
(10, 310, 'Psalms', 'Alabaster', '/assets/img/exchange-covers/psalms.png', 'Une edition visuelle et contemplative des psaumes, partagee par Lolobzh.', 'available', '2026-03-15 16:58:42', NULL),
(11, 311, 'Thinking, Fast & Slow', 'Daniel Kahneman', '/assets/img/exchange-covers/thinking-fast-and-slow.png', 'Le classique de Daniel Kahneman sur nos biais cognitifs et nos deux systemes de pensee, actuellement non disponible.', 'unavailable', '2026-03-15 16:58:42', NULL),
(12, 312, 'A Book Full Of Hope', 'Rupi Kaur', '/assets/img/exchange-covers/a-book-full-of-hope.png', 'Un ouvrage bref et sensible de Rupi Kaur, aux accents d espoir, propose par ML95.', 'available', '2026-03-15 16:58:42', NULL),
(13, 313, 'The Subtle Art Of Not Giving A F*ck', 'Mark Manson', '/assets/img/exchange-covers/the-subtle-art-of-not-giving-a-fck.png', 'Un essai frontal et populaire sur les priorites et le sens, disponible chez Verogo33.', 'available', '2026-03-15 16:58:42', NULL),
(14, 314, 'Narnia', 'C.S Lewis', '/assets/img/exchange-covers/narnia.png', 'Une edition illustree de Narnia, plus rare, actuellement non disponible chez AnnikaBrahms.', 'unavailable', '2026-03-15 16:58:42', '2026-04-01 22:36:22'),
(15, 315, 'Company Of One', 'Paul Jarvis', '/assets/img/exchange-covers/company-of-one.png', 'Un livre sur la croissance volontairement maitrisee et le travail independant, partage par Victoirefabr912.', 'available', '2026-03-15 16:58:42', '2026-04-01 22:36:15'),
(16, 316, 'The Two Towers', 'J.R.R Tolkien', '/assets/img/exchange-covers/the-two-towers.png', 'Le second tome du Seigneur des Anneaux, dans une edition tres visuelle, disponible chez Lotrfanclub67.', 'available', '2026-03-15 16:58:42', '2026-04-01 22:36:13');

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

CREATE TABLE `messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `sender_id` int(10) UNSIGNED NOT NULL,
  `receiver_id` int(10) UNSIGNED NOT NULL,
  `content` longtext NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `content`, `is_read`, `created_at`) VALUES
(1, 301, 302, 'test', 1, '2026-03-15 16:59:43'),
(2, 302, 301, 'Bonjour Camille, est-ce que Esther est toujours disponible pour un echange ?', 1, '2026-03-12 20:54:58'),
(3, 301, 302, 'Bonjour Nathalie, oui, le livre est encore disponible. Tu voudrais proposer quel titre en retour ?', 0, '2026-03-12 21:19:58'),
(4, 302, 301, 'Je peux te proposer The Kinfolk Table ou venir le recuperer cette semaine.', 0, '2026-03-12 21:54:58'),
(5, 303, 304, 'Bonjour Hugo, Milk and Honey m interesse beaucoup. Est-ce que le livre est en bon etat ?', 1, '2026-03-13 20:54:58'),
(6, 304, 303, 'Oui, il est en tres bon etat. Je peux t envoyer d autres photos si tu veux.', 0, '2026-03-13 21:12:58'),
(7, 305, 307, 'Salut Hamza, Minimalist Graphics est toujours dispo ?', 0, '2026-03-14 20:54:58'),
(8, 307, 305, 'Salut Juju, oui il est dispo. Tu cherches un echange ou un simple pret ?', 0, '2026-03-14 21:06:58'),
(9, 309, 311, 'Bonjour, Thinking Fast & Slow est-il encore disponible ?', 0, '2026-03-15 08:54:58'),
(10, 311, 309, 'Oui, toujours disponible. Je peux te le reserver si tu veux.', 0, '2026-03-15 10:34:58'),
(11, 314, 316, 'Bonjour, The Two Towers m interesse pour un echange Tolkien contre Narnia.', 0, '2026-03-15 14:54:58'),
(12, 316, 314, 'Bonne idee. On peut s organiser en message prive cette semaine.', 0, '2026-03-15 16:14:58'),
(13, 315, 302, 'Bonjour Nathalie, j aime beaucoup The Kinfolk Table. Est-ce que tu acceptes un echange contre Company of One ?', 0, '2026-03-15 19:24:58'),
(14, 303, 304, 'intéressé ??', 1, '2026-03-15 20:57:20'),
(15, 304, 4, 'allo', 1, '2026-03-15 21:14:30'),
(16, 4, 301, 'test', 0, '2026-03-17 14:33:37'),
(17, 4, 301, 'test', 0, '2026-03-17 14:46:32'),
(18, 4, 304, 'test', 1, '2026-03-17 14:46:38'),
(19, 304, 4, 'test lkjlkjpomkmo', 1, '2026-03-17 14:46:57'),
(20, 304, 307, 'hello', 0, '2026-03-17 21:22:00'),
(21, 304, 4, 'hello dispo', 1, '2026-03-17 21:22:23'),
(22, 4, 304, 'oooojojoj,kjnlk', 1, '2026-03-18 13:15:01'),
(23, 4, 304, ',nbhjbhbnkjn', 1, '2026-03-18 13:15:06'),
(24, 304, 303, '<script>alert(\'Faille XSS !\')</script>', 0, '2026-04-01 19:08:35'),
(25, 304, 303, '<script>alert(\'Faille XSS !\')</script>', 0, '2026-04-01 19:09:14');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(80) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password` varchar(255) NOT NULL,
  `bio` text DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `bio`, `deleted_at`, `avatar`, `created_at`, `updated_at`) VALUES
(3, 'fix1772709481', 'fix1772709481@example.com', '$2y$10$Y2gwA6ewd9sfWBG.LUieq.QAYQFxRJ.TUgNV3l8xQ9UDdXRI6t.v2', '', NULL, '/assets/img/figma/mask-group-2.png', '2026-03-05 12:18:01', NULL),
(4, 'ben', 'ben@tomtroc.local', '$2y$10$C3pGztM41HtTYHhOHCeEVeUT6eayTKi/pKdctl/aUac6Xb3vmOcvu', '', NULL, '/assets/img/figma/mask-group-2.png', '2026-03-05 12:18:52', '2026-04-01 23:34:02'),
(301, 'CamilleDuCuir', 'tom301@tomtroc.local', '$2y$10$YQ8nqRAdThIQxxbkbrWteuOonOhV3I1.YVz2icfrSVmbQktpJOtD2', '', NULL, '/assets/img/avatars/camilleducuir.png', '2026-03-15 16:35:00', NULL),
(302, 'Nathalie', 'tom302@tomtroc.local', '$2y$10$YQ8nqRAdThIQxxbkbrWteuOonOhV3I1.YVz2icfrSVmbQktpJOtD2', '', NULL, '/assets/img/avatars/nathalie-photo.png', '2026-03-15 16:35:00', NULL),
(303, 'Alicecture', 'tom303@tomtroc.local', '$2y$10$YQ8nqRAdThIQxxbkbrWteuOonOhV3I1.YVz2icfrSVmbQktpJOtD2', '', NULL, '/assets/img/avatars/alicecture.png', '2026-03-15 16:35:00', NULL),
(304, 'Hugo1990_12', 'tom304@tomtroc.local', '$2y$10$YQ8nqRAdThIQxxbkbrWteuOonOhV3I1.YVz2icfrSVmbQktpJOtD2', '', NULL, '/assets/img/avatars/hugo1990-12.png', '2026-03-15 16:35:00', NULL),
(305, 'Juju1432', 'tom305@tomtroc.local', '$2y$10$YQ8nqRAdThIQxxbkbrWteuOonOhV3I1.YVz2icfrSVmbQktpJOtD2', '', NULL, '/assets/img/avatars/juju1432.png', '2026-03-15 16:35:00', NULL),
(306, 'Christiane75014', 'tom306@tomtroc.local', '$2y$10$YQ8nqRAdThIQxxbkbrWteuOonOhV3I1.YVz2icfrSVmbQktpJOtD2', '', NULL, '/assets/img/avatars/christiane75014.png', '2026-03-15 16:35:00', NULL),
(307, 'Hamzalecture', 'tom307@tomtroc.local', '$2y$10$YQ8nqRAdThIQxxbkbrWteuOonOhV3I1.YVz2icfrSVmbQktpJOtD2', '', NULL, '/assets/img/avatars/hamzalecture.png', '2026-03-15 16:35:00', NULL),
(308, 'Hugo1990_12_2', 'tom308@tomtroc.local', '$2y$10$YQ8nqRAdThIQxxbkbrWteuOonOhV3I1.YVz2icfrSVmbQktpJOtD2', '', NULL, '/assets/img/avatars/hugo1990-12.png', '2026-03-15 16:35:00', '2026-04-01 22:20:51'),
(309, 'LouBen50', 'tom309@tomtroc.local', '$2y$10$YQ8nqRAdThIQxxbkbrWteuOonOhV3I1.YVz2icfrSVmbQktpJOtD2', '', NULL, '/assets/img/avatars/louben50.png', '2026-03-15 16:35:00', NULL),
(310, 'Lolobzh', 'tom310@tomtroc.local', '$2y$10$YQ8nqRAdThIQxxbkbrWteuOonOhV3I1.YVz2icfrSVmbQktpJOtD2', '', NULL, '/assets/img/avatars/lolobzh.png', '2026-03-15 16:35:00', NULL),
(311, 'Sas634', 'tom311@tomtroc.local', '$2y$10$YQ8nqRAdThIQxxbkbrWteuOonOhV3I1.YVz2icfrSVmbQktpJOtD2', '', NULL, '/assets/img/avatars/sas634.png', '2026-03-15 16:35:00', NULL),
(312, 'ML95', 'tom312@tomtroc.local', '$2y$10$YQ8nqRAdThIQxxbkbrWteuOonOhV3I1.YVz2icfrSVmbQktpJOtD2', '', NULL, '/assets/img/avatars/ml95.png', '2026-03-15 16:35:00', NULL),
(313, 'Verogo33', 'tom313@tomtroc.local', '$2y$10$YQ8nqRAdThIQxxbkbrWteuOonOhV3I1.YVz2icfrSVmbQktpJOtD2', '', NULL, '/assets/img/avatars/verogo33.png', '2026-03-15 16:35:00', NULL),
(314, 'AnnikaBrahms', 'tom314@tomtroc.local', '$2y$10$YQ8nqRAdThIQxxbkbrWteuOonOhV3I1.YVz2icfrSVmbQktpJOtD2', '', NULL, '/assets/img/avatars/annikabrahms.png', '2026-03-15 16:35:00', NULL),
(315, 'Victoirefabr912', 'tom315@tomtroc.local', '$2y$10$YQ8nqRAdThIQxxbkbrWteuOonOhV3I1.YVz2icfrSVmbQktpJOtD2', '', NULL, '/assets/img/avatars/victoirefabr912.png', '2026-03-15 16:35:00', NULL),
(316, 'Lotrfanclub67', 'tom316@tomtroc.local', '$2y$10$YQ8nqRAdThIQxxbkbrWteuOonOhV3I1.YVz2icfrSVmbQktpJOtD2', '', NULL, '/assets/img/avatars/lotrfanclub67.png', '2026-03-15 16:35:00', NULL);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_books_user` (`user_id`),
  ADD KEY `idx_books_status` (`status`),
  ADD KEY `idx_books_title` (`title`);

--
-- Index pour la table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_messages_sender` (`sender_id`),
  ADD KEY `idx_messages_receiver` (`receiver_id`),
  ADD KEY `idx_messages_created` (`created_at`),
  ADD KEY `idx_messages_pair_date` (`sender_id`,`receiver_id`,`created_at`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `uk_users_username` (`username`),
  ADD KEY `idx_users_deleted_at` (`deleted_at`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `books`
--
ALTER TABLE `books`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT pour la table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=321;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `fk_books_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_messages_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_messages_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
