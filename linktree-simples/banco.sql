CREATE DATABASE IF NOT EXISTS linktree
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE linktree;

CREATE TABLE IF NOT EXISTS links (
    url VARCHAR(255) NOT NULL,
    nome VARCHAR(50) NOT NULL,
    apelido VARCHAR(50) NOT NULL,
    PRIMARY KEY (url),
    INDEX idx_links_apelido (apelido)
) ENGINE=InnoDB;

-- Tabela adicional para o acesso administrativo (Parte 3).
CREATE TABLE IF NOT EXISTS administradores (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario VARCHAR(50) NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_administradores_usuario (usuario)
) ENGINE=InnoDB;

-- Dados opcionais para testar o perfil publico.
INSERT IGNORE INTO links (url, nome, apelido) VALUES
('https://github.com/', 'Meu GitHub', 'fulano'),
('https://www.linkedin.com/', 'Meu LinkedIn', 'fulano'),
('https://www.youtube.com/', 'Meu YouTube', 'fulano');
