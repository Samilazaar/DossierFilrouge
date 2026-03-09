-- Seed data for Alwaysdata PostgreSQL

-- Users (formateurs: id 7-11)
INSERT INTO users (id, nom, prenom, email, telephone, password, competences) VALUES
(7, 'Dupont', 'Jean', 'jean.dupont@skillhub.com', '0600000000', '$2y$12$TNA5oyxMWkmSlRVnr2ayu.77E7slYXs95Fmed5abkuRmDZ94KNK2G', NULL),
(8, 'Martin', 'Marie', 'marie.martin@skillhub.com', '0600000000', '$2y$12$5tLQhZ0uOmSrhyubkmusM.WeNWvIoAPsHVtfHTA5Qcjc7RsuHPQxa', NULL),
(9, 'Durand', 'Pierre', 'pierre.durand@skillhub.com', '0600000000', '$2y$12$TVZrYa2EcByo9xIe3oqIzuX9rzzhgbrq5o7tQdfDAdQ9PwdFTnUHq', NULL),
(10, 'Bernard', 'Sophie', 'sophie.bernard@skillhub.com', '0600000000', '$2y$12$hvnfgHnX8HSEXMAE.UrTCewciMyq3FP02SgLd1tf6bbUcmMIc.ZEa', NULL),
(11, 'Moreau', 'Lucas', 'lucas.moreau@skillhub.com', '0600000000', '$2y$12$FyrtiiRVruXzl3KJGwMSK.dXtaZy2/keX5msBmOvaHt1opZOgp0mC', NULL);

-- Formateurs profiles
INSERT INTO users_formateurs (id, bio, specialite, experiences, user_id) VALUES
(1, 'Expert React et JavaScript', 'Développement Web', '10 ans', 7),
(2, 'Formatrice Symfony certifiée', 'PHP / Symfony', '8 ans', 8),
(3, 'DBA senior', 'Base de données', '12 ans', 9),
(4, 'Designer UX senior', 'Design UX/UI', '7 ans', 10),
(5, 'Expert DevOps et cybersécurité', 'DevOps / Sécurité', '9 ans', 11);

-- Ateliers
INSERT INTO ateliers (id, titre, description, date, duree, capacite_max, places_restantes, image_url, formateur_id) VALUES
(1, 'Développement Web avec React', 'Apprenez à créer des applications web modernes et réactives avec React. Ce cours couvre les fondamentaux du framework React, la gestion des composants, les hooks, le state management et les bonnes pratiques.', '2025-01-15 00:00:00', '3h', 15, 15, 'https://images.unsplash.com/photo-1587620962725-abab7fe55159?w=800&q=80', 1),
(2, 'Introduction à Symfony', 'Découvrez Symfony, le framework PHP puissant pour créer des applications web robustes et évolutives. Apprenez la structure MVC, les routes, les contrôleurs et les templates Twig.', '2025-01-20 00:00:00', '4h', 20, 20, 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=800&q=80', 2),
(3, 'Base de données avancée', 'Approfondissez vos connaissances en bases de données relationnelles. Ce cours couvre la modélisation complexe, l''optimisation des requêtes SQL, les jointures avancées et les transactions.', '2025-01-25 00:00:00', '2h', 10, 10, 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?w=800&q=80', 3),
(4, 'Design UX/UI moderne', 'Maîtrisez les principes fondamentaux du design d''expérience utilisateur et d''interface. Créez des interfaces intuitives avec Figma et Adobe XD.', '2025-02-01 00:00:00', '3h', 12, 12, 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=800&q=80', 4),
(5, 'DevOps et CI/CD', 'Découvrez les pratiques DevOps modernes. Apprenez Docker, Kubernetes, GitHub Actions et les pipelines de livraison continue.', '2025-02-05 00:00:00', '4h', 8, 8, 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=800&q=80', 5),
(6, 'Cybersécurité Web', 'Apprenez à sécuriser vos applications web contre les vulnérabilités courantes : XSS, CSRF, SQL injection et les bonnes pratiques OWASP.', '2025-02-10 00:00:00', '3h', 15, 15, 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=800&q=80', 5);

-- Reset sequences
SELECT setval('users_id_seq', (SELECT MAX(id) FROM users));
SELECT setval('users_formateurs_id_seq', (SELECT MAX(id) FROM users_formateurs));
SELECT setval('ateliers_id_seq', (SELECT MAX(id) FROM ateliers));
