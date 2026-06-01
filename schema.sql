-- Script de Criação do Banco de Dados
-- =========================================================================
-- IMPORTANTE PARA INFINITYFREE:
-- 1. No painel do InfinityFree, vá em "MySQL Databases" e crie um banco de dados.
--    Ele terá um nome automático no formato: if0_XXXXXX_fatec_contatos
-- 2. No phpMyAdmin do InfinityFree, selecione esse banco de dados criado.
-- 3. Importe este arquivo SQL diretamente nele.
-- 
-- As linhas de CREATE DATABASE e USE foram comentadas abaixo para evitar
-- erros de permissão na importação do InfinityFree.
-- =========================================================================

-- CREATE DATABASE IF NOT EXISTS `fatec_contatos` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE `fatec_contatos`;

-- Estrutura da tabela `contatos`
CREATE TABLE IF NOT EXISTS `contatos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `telefone` VARCHAR(20) NOT NULL,
    `mensagem` TEXT NOT NULL,
    `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
