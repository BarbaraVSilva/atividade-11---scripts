-- Script de Criação do Banco de Dados
-- 

-- CREATE DATABASE IF NOT EXISTS `if0_41931839_atv_11` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE `if0_41931839_atv_11`;

-- Estrutura da tabela `contatos`
CREATE TABLE IF NOT EXISTS `contatos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `telefone` VARCHAR(20) NOT NULL,
    `mensagem` TEXT NOT NULL,
    `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
