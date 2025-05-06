
-- Crie o banco de dados
CREATE DATABASE IF NOT EXISTS quitutes_ai DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE quitutes_ai;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    email VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de produtos
CREATE TABLE IF NOT EXISTS produtos (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    estoque_atual INT(6) NOT NULL,
    estoque_minimo INT(6) NOT NULL,
    preco DECIMAL(10,2) NOT NULL,
    unidade_medida VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de pedidos
CREATE TABLE IF NOT EXISTS pedidos (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT(6) UNSIGNED,
    cliente_nome VARCHAR(100) NOT NULL,
    valor_total DECIMAL(10,2) NOT NULL,
    status ENUM('pendente', 'em_preparo', 'a_caminho', 'entregue', 'cancelado') DEFAULT 'pendente',
    data_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES usuarios(id)
);

-- Tabela de itens do pedido
CREATE TABLE IF NOT EXISTS itens_pedido (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT(6) UNSIGNED,
    produto_id INT(6) UNSIGNED,
    quantidade INT(6) NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
);

-- Tabela de clientes
CREATE TABLE IF NOT EXISTS clientes (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    telefone VARCHAR(20),
    endereco TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Dados iniciais para produtos
INSERT INTO produtos (nome, categoria, estoque_atual, estoque_minimo, preco, unidade_medida) VALUES
('Pastel', 'Salgado', 20, 50, 10, 'unidade');

-- Dados iniciais para clientes
INSERT INTO clientes (nome, email, telefone, endereco) VALUES
('Ana Silva', 'ana@example.com', '(11) 99999-9999', 'Rua A, 123 - São Paulo/SP'),
('Carlos Oliveira', 'carlos@example.com', '(11) 98888-8888', 'Av. B, 456 - São Paulo/SP'),
('Mariana Costa', 'mariana@example.com', '(11) 97777-7777', 'Rua C, 789 - São Paulo/SP'),
('Roberto Santos', 'roberto@example.com', '(11) 96666-6666', 'Av. D, 101 - São Paulo/SP');

-- Usuário administrador padrão (senha: admin123)
INSERT INTO usuarios (nome, email, senha) VALUES
('Administrador', 'admin@quitutes.com', '$2y$10$znSi5mF12vTcGPeSOvb5jeXukoMrEJGOT0Doazvydrw6btRm.aiDG');
-- ATENÇÃO: Troque o valor da senha pelo hash gerado pelo PHP para 'admin123'


select * from receitas;