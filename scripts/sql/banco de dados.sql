-- sql/stockwise.sql

-- Criação do Banco de Dados (se ainda não existir)
CREATE DATABASE IF NOT EXISTS `loja` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `loja`;

-- Tabela para Usuários
-- Observação: Para uma aplicação real, a senha deve ser HASHED (ex: usando password_hash() no PHP)
-- e não armazenada em texto puro. Esta é uma simplificação para fins de demonstração.
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL, -- Armazenar hash da senha (ex: SHA256, bcrypt)
  `role` VARCHAR(50) DEFAULT 'Vendedor', -- Ex: 'Administrador', 'Gerente', 'Vendedor'
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

UPDATE users SET password = MD5('admin123') WHERE username = 'admin';

-- Inserir um usuário padrão para testes (SENHA: admin123)
-- Em um ambiente de produção, use password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO `users` (`username`, `password`, `role`) VALUES
('admin', '21232f297a57a5a743894a0e4a801fc3', 'Administrador'); -- MD5 de 'admin123' (APENAS PARA DEMO, NÃO SEGURO!)


-- Tabela para Produtos
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL, -- Para isolar dados por usuário, se for multi-usuário no futuro
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `sku` VARCHAR(100) UNIQUE,
  `cost_price` DECIMAL(10, 2) NOT NULL,
  `sale_price` DECIMAL(10, 2) NOT NULL,
  `category` VARCHAR(100),
  `unit` VARCHAR(50),
  `min_stock` INT DEFAULT 0,
  `location` VARCHAR(255),
  `current_stock` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela para Movimentações de Estoque
CREATE TABLE IF NOT EXISTS `movements` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `product_name_at_move` VARCHAR(255) NOT NULL, -- Armazena o nome no momento da movimentação
  `type` ENUM('entrada', 'saida') NOT NULL,
  `quantity` INT NOT NULL,
  `details` TEXT,
  `current_stock_after` INT, -- Estoque após esta movimentação
  `movement_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela para Vendas
CREATE TABLE IF NOT EXISTS `sales` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `sale_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `total_amount` DECIMAL(10, 2) NOT NULL,
  `payment_method` VARCHAR(100),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela para Itens de Venda (detalhes de cada venda)
CREATE TABLE IF NOT EXISTS `sale_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sale_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `product_name_at_sale` VARCHAR(255) NOT NULL, -- Armazena o nome no momento da venda
  `quantity` INT NOT NULL,
  `price_at_sale` DECIMAL(10, 2) NOT NULL, -- Preço unitário no momento da venda
  FOREIGN KEY (`sale_id`) REFERENCES `sales`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
