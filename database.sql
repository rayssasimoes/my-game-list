-- =========================================
-- MY GAME LIST - DATABASE SETUP
-- Execute este script no MySQL para criar
-- o banco de dados e todas as tabelas
-- =========================================

-- Criar o banco de dados
CREATE DATABASE IF NOT EXISTS db_mygamelist 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Usar o banco de dados
USE db_mygamelist;

-- =========================================
-- TABELA: users
-- Armazena os usuários cadastrados
-- =========================================
CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    username VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    profile_photo_path VARCHAR(2048) NULL,
    avatar_path VARCHAR(2048) NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- TABELA: games
-- Armazena informações dos jogos da IGDB
-- =========================================
CREATE TABLE IF NOT EXISTS games (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    igdb_id INT NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    cover_url VARCHAR(500) NULL,
    summary TEXT NULL,
    rating DECIMAL(5,2) NULL,
    release_date DATE NULL,
    genres JSON NULL,
    platforms JSON NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_igdb_id (igdb_id),
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- TABELA: game_user
-- Relacionamento entre usuários e jogos
-- (lista pessoal de cada usuário)
-- =========================================
CREATE TABLE IF NOT EXISTS game_user (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    game_id BIGINT UNSIGNED NOT NULL,
    status ENUM('playing', 'completed', 'want_to_play', 'dropped') DEFAULT 'want_to_play',
    rating INT NULL CHECK (rating >= 1 AND rating <= 10),
    notes TEXT NULL,
    added_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_user_game (user_id, game_id),
    
    CONSTRAINT fk_game_user_user 
        FOREIGN KEY (user_id) 
        REFERENCES users(id) 
        ON DELETE CASCADE,
    
    CONSTRAINT fk_game_user_game 
        FOREIGN KEY (game_id) 
        REFERENCES games(id) 
        ON DELETE CASCADE,
    
    INDEX idx_user_id (user_id),
    INDEX idx_game_id (game_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- VERIFICAÇÃO
-- =========================================
SELECT 
    'Database criado com sucesso! ✅' AS status,
    (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'db_mygamelist') AS total_tabelas;

SHOW TABLES;
