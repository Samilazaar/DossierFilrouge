-- =============================================
-- Création de la table sessions
-- =============================================

CREATE TABLE sessions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id)
        ON DELETE CASCADE,
    session_id VARCHAR(128) NOT NULL,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    CONSTRAINT uq_sessions_user UNIQUE (user_id)
);

CREATE INDEX idx_sessions_session_id ON sessions(session_id);

-- =============================================
-- Requêtes SQL utiles
-- =============================================

-- Voir la dernière session d'un utilisateur
SELECT s.session_id, s.last_activity, s.ip_address, u.email
FROM sessions s
JOIN users u ON u.id = s.user_id
WHERE u.id = 5;

-- Qui s'est connecté dans la dernière heure ?
SELECT u.nom, u.prenom, s.last_activity
FROM sessions s
JOIN users u ON u.id = s.user_id
WHERE s.last_activity > NOW() - INTERVAL '1 hour'
ORDER BY s.last_activity DESC;

-- Combien d'inscrits par atelier ?
SELECT a.titre, COUNT(i.id) AS nb_inscrits
FROM ateliers a
LEFT JOIN inscriptions i ON i.atelier_id = a.id
GROUP BY a.titre
ORDER BY nb_inscrits DESC;

-- Supprimer les sessions de plus de 24h
DELETE FROM sessions
WHERE last_activity < NOW() - INTERVAL '24 hours';

-- Les formateurs et leurs ateliers
SELECT uf.specialite, u.nom, COUNT(a.id) AS nb_ateliers
FROM users_formateurs uf
JOIN users u ON u.id = uf.user_id
LEFT JOIN ateliers a ON a.formateur_id = uf.id
GROUP BY uf.id, uf.specialite, u.nom;
