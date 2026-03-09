-- SkillHub - Dump MySQL converti depuis PostgreSQL

SET FOREIGN_KEY_CHECKS = 0;

-- Table: users
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `nom` VARCHAR(255) DEFAULT NULL,
    `prenom` VARCHAR(255) DEFAULT NULL,
    `email` VARCHAR(255) DEFAULT NULL,
    `telephone` VARCHAR(20) DEFAULT NULL,
    `password` VARCHAR(255) DEFAULT NULL,
    `competences` JSON DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: users_formateurs
CREATE TABLE IF NOT EXISTS `users_formateurs` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `bio` TEXT,
    `specialite` VARCHAR(255) DEFAULT NULL,
    `experiences` TEXT,
    `user_id` INT DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_user_id` (`user_id`),
    CONSTRAINT `fk_formateurs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: ateliers
CREATE TABLE IF NOT EXISTS `ateliers` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `titre` VARCHAR(255) DEFAULT NULL,
    `description` TEXT,
    `date` DATETIME DEFAULT NULL,
    `duree` VARCHAR(50) DEFAULT NULL,
    `capacite_max` INT DEFAULT NULL,
    `places_restantes` INT DEFAULT NULL,
    `image_url` VARCHAR(500) DEFAULT NULL,
    `formateur_id` INT DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_formateur` (`formateur_id`),
    CONSTRAINT `fk_ateliers_formateur` FOREIGN KEY (`formateur_id`) REFERENCES `users_formateurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: inscriptions
CREATE TABLE IF NOT EXISTS `inscriptions` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `date_inscription` DATETIME DEFAULT NULL,
    `utilisateur_id` INT DEFAULT NULL,
    `atelier_id` INT DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_utilisateur` (`utilisateur_id`),
    KEY `idx_atelier` (`atelier_id`),
    CONSTRAINT `fk_inscriptions_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `users` (`id`),
    CONSTRAINT `fk_inscriptions_atelier` FOREIGN KEY (`atelier_id`) REFERENCES `ateliers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Données: users
INSERT INTO `users` (`id`, `nom`, `prenom`, `email`, `telephone`, `password`, `competences`) VALUES
(1, 'Lazaar', 'Lazaar', 'lazaar17030@gmail.com', '1234567890', '$2y$12$iSpVMUKSIN6lQ0JLxRnQluN1aT2lzChngUyX9KaJ5OnE/eirxTExO', '["Symfony"]'),
(2, 'Utilisateur', 'Test', 'test@skillhub.com', '0601020304', '$2y$12$5UkB4AbHv1Jc8ggEAwJLUueSSt1AdZcHBl6HDe3ILpfWHMxaGJIq6', NULL),
(3, 'Admin', 'Admin', 'admin@skillhub.com', '0601020305', '$2y$12$dugmDob18ogdOL6PdgOdve7.KVCYy32kgJYwBJIvmDxLf0l1YPPCS', NULL),
(4, 'Test', 'Fichier', 'fichier@test.com', '0123456789', '$2y$12$DhkaqPpBTvKAPjEkz6foE.Lp7nb4WaGMTPnSnH6BUd34HCdjel0Ki', NULL),
(5, 'Gazo', 'Drillfr', 'gazo@gmail.com', '0611223344', '$2y$12$bmm42kvhj1g2L9CIcZKuJ.mLQKd1YSJjSKeMKvnRqhN4o7kZQ32rC', NULL),
(6, 'booba', 'booba', 'booba@gmail.com', '0655229901', '$2y$12$lWeEcXdJdEAMDB1zj/HJjuh5wEzCCTeZEj46DO11G5d37FsW70kVy', NULL),
(7, 'Dupont', 'Jean', 'jean.dupont@skillhub.com', '0600000000', '$2y$12$TNA5oyxMWkmSlRVnr2ayu.77E7slYXs95Fmed5abkuRmDZ94KNK2G', NULL),
(8, 'Martin', 'Marie', 'marie.martin@skillhub.com', '0600000000', '$2y$12$5tLQhZ0uOmSrhyubkmusM.WeNWvIoAPsHVtfHTA5Qcjc7RsuHPQxa', NULL),
(9, 'Durand', 'Pierre', 'pierre.durand@skillhub.com', '0600000000', '$2y$12$TVZrYa2EcByo9xIe3oqIzuX9rzzhgbrq5o7tQdfDAdQ9PwdFTnUHq', NULL),
(10, 'Bernard', 'Sophie', 'sophie.bernard@skillhub.com', '0600000000', '$2y$12$hvnfgHnX8HSEXMAE.UrTCewciMyq3FP02SgLd1tf6bbUcmMIc.ZEa', NULL),
(11, 'Moreau', 'Lucas', 'lucas.moreau@skillhub.com', '0600000000', '$2y$12$FyrtiiRVruXzl3KJGwMSK.dXtaZy2/keX5msBmOvaHt1opZOgp0mC', NULL),
(12, 'Sami', 'Sami', 'samie17030@gmail.com', '1234567890', '$2y$12$JRwvYyupK/c1mjK.7UQIAOzmz9Gh.RHjRM8yBc94uRWiQ.JsqbQ8G', NULL);

-- Données: users_formateurs
INSERT INTO `users_formateurs` (`id`, `bio`, `specialite`, `experiences`, `user_id`) VALUES
(1, 'Expert React et JavaScript', 'Développement Web', '10 ans', 7),
(2, 'Formatrice Symfony certifiée', 'PHP / Symfony', '8 ans', 8),
(3, 'DBA senior', 'Base de données', '12 ans', 9),
(4, 'Designer UX senior', 'Design UX/UI', '7 ans', 10),
(5, 'Expert DevOps et cybersécurité', 'DevOps / Sécurité', '9 ans', 11);

-- Données: ateliers
INSERT INTO `ateliers` (`id`, `titre`, `description`, `date`, `duree`, `capacite_max`, `places_restantes`, `image_url`, `formateur_id`) VALUES
(1, 'Développement Web avec React', 'Apprenez à créer des applications web modernes et réactives avec React. Ce cours couvre les fondamentaux du framework React, la gestion des composants, les hooks, le state management et les bonnes pratiques pour développer des applications web performantes et évolutives.', '2025-01-15 00:00:00', '3h', 15, 14, 'https://images.unsplash.com/photo-1587620962725-abab7fe55159?w=800&q=80', 1),
(2, 'Introduction à Symfony', 'Découvrez Symfony, le framework PHP puissant pour créer des applications web robustes et évolutives. Apprenez à maîtriser les composants clés du framework, la structure MVC, les routes, les contrôleurs et les templates Twig.', '2025-01-20 00:00:00', '4h', 20, 18, 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=800&q=80', 2),
(3, 'Base de données avancée', 'Approfondissez vos connaissances en bases de données relationnelles. Ce cours avancé couvre la modélisation complexe, l''optimisation des requêtes SQL, les jointures avancées, les transactions et les bonnes pratiques de sécurisation des données.', '2025-01-25 00:00:00', '2h', 10, 9, 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?w=800&q=80', 3),
(4, 'Design UX/UI moderne', 'Maîtrisez les principes fondamentaux du design d''expérience utilisateur et d''interface. Apprenez à créer des interfaces intuitives, accessibles et esthétiques en utilisant les outils de design les plus récents comme Figma et Adobe XD.', '2025-02-01 00:00:00', '3h', 12, 11, 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=800&q=80', 4),
(5, 'DevOps et CI/CD', 'Découvrez les pratiques DevOps modernes pour automatiser le déploiement et améliorer la qualité du code. Apprenez à utiliser Docker, Kubernetes, GitHub Actions et autres outils de CI/CD pour créer des pipelines de livraison continue.', '2025-02-05 00:00:00', '4h', 8, 8, 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=800&q=80', 5),
(6, 'Cybersécurité Web', 'Apprenez à sécuriser vos applications web contre les vulnérabilités courantes. Ce cours couvre les attaques XSS, CSRF, SQL injection, l''authentification sécurisée, le chiffrement et les meilleures pratiques de sécurité OWASP.', '2025-02-10 00:00:00', '3h', 15, 15, 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=800&q=80', 5);

-- Données: inscriptions
INSERT INTO `inscriptions` (`id`, `date_inscription`, `utilisateur_id`, `atelier_id`) VALUES
(1, '2026-01-06 14:16:28', 4, 3),
(2, '2026-01-06 14:16:34', 4, 1),
(3, '2026-01-06 14:16:38', 4, 2),
(4, '2026-01-06 14:17:45', 5, 4),
(5, '2026-02-12 10:41:46', 1, 2);

SET FOREIGN_KEY_CHECKS = 1;
