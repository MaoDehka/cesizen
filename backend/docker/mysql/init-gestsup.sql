CREATE DATABASE IF NOT EXISTS gestsup_cesizen;
GRANT ALL PRIVILEGES ON gestsup_cesizen.* TO 'cesizen_user'@'%';

USE gestsup_cesizen;

-- Tables de base Gestsup (simplifiées)
CREATE TABLE IF NOT EXISTS tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gestsup_id VARCHAR(20) UNIQUE,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    type ENUM('BUG', 'EVOLUTION', 'AMELIORATION', 'SUPPORT'),
    priority ENUM('CRITIQUE', 'ELEVEE', 'NORMALE', 'FAIBLE'),
    status VARCHAR(50) DEFAULT 'NOUVEAU',
    creator_id INT,
    assignee_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100),
    email VARCHAR(255),
    role VARCHAR(50) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Données initiales
INSERT INTO users (username, email, role) VALUES 
('admin', 'admin@cesizen.com', 'admin'),
('dev', 'dev@cesizen.com', 'developer');