<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Quitutes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Custom styles */
        .sidebar {
            transition: all 0.3s;
        }

        .sidebar.collapsed {
            width: 80px;
        }

        .sidebar.collapsed .nav-text {
            display: none;
        }

        .sidebar.collapsed .logo-text {
            display: none;
        }

        .sidebar.collapsed .nav-item {
            justify-content: center;
        }

        .main-content {
            transition: all 0.3s;
        }

        .sidebar.collapsed+.main-content {
            margin-left: 80px;
        }

        .popup {
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translate(-50%, -45%);
            }
            to {
                opacity: 1;
                transform: translate(-50%, -50%);
            }
        }

        .overlay {
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
    </style>
</head>

<body class="bg-gray-50">
    <?php
    // Configurações do banco de dados
    $servername = "localhost";
    $username = "root"; // usuário padrão do MySQL
    $password = ""; // senha padrão (vazia no XAMPP)
    $dbname = "quitutes_ai";
        
    // Criar conexão
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Verificar conexão
    if ($conn->connect_error) {
        // Se não existir, criar o banco de dados e as tabelas
        $conn = new mysqli($servername, $username, $password);
        
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        
        // Criar banco de dados
        $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
        if ($conn->query($sql) === TRUE) {
            // Selecionar o banco de dados
            $conn->select_db($dbname);
            
            // Criar tabelas
            $sql = "CREATE TABLE IF NOT EXISTS usuarios (
                id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(50) NOT NULL,
                email VARCHAR(50) NOT NULL UNIQUE,
                senha VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            
            $conn->query($sql);
            
            $sql = "CREATE TABLE IF NOT EXISTS produtos (
                id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(100) NOT NULL,
                categoria VARCHAR(50) NOT NULL,
                estoque_atual INT(6) NOT NULL,
                estoque_minimo INT(6) NOT NULL,
                preco DECIMAL(10,2) NOT NULL,
                unidade_medida VARCHAR(20) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            
            $conn->query($sql);
            
            $sql = "CREATE TABLE IF NOT EXISTS pedidos (
                id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                cliente_id INT(6) UNSIGNED,
                cliente_nome VARCHAR(100) NOT NULL,
                valor_total DECIMAL(10,2) NOT NULL,
                status ENUM('pendente', 'em_preparo', 'a_caminho', 'entregue', 'cancelado') DEFAULT 'pendente',
                data_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (cliente_id) REFERENCES usuarios(id)
            )";
            
            $conn->query($sql);
            
            $sql = "CREATE TABLE IF NOT EXISTS itens_pedido (
                id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                pedido_id INT(6) UNSIGNED,
                produto_id INT(6) UNSIGNED,
                quantidade INT(6) NOT NULL,
                preco_unitario DECIMAL(10,2) NOT NULL,
                FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
                FOREIGN KEY (produto_id) REFERENCES produtos(id)
            )";
            
            $conn->query($sql);
            
            $sql = "CREATE TABLE IF NOT EXISTS clientes (
                id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                telefone VARCHAR(20),
                endereco TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            
            $conn->query($sql);
            
            // Inserir dados iniciais se as tabelas estiverem vazias
            $result = $conn->query("SELECT COUNT(*) as total FROM produtos");
            $row = $result->fetch_assoc();
            if ($row['total'] == 0) {
                $produtos = [
                    ['Bolo de Chocolate', 'Bolos', 15, 5, 45.00, 'unidade'],
                    ['Brigadeiro', 'Doces', 50, 20, 2.50, 'unidade'],
                    ['Beijinho', 'Doces', 40, 15, 2.00, 'unidade'],
                    ['Torta de Limão', 'Tortas', 8, 3, 35.00, 'fatia'],
                    ['Cookie', 'Biscoitos', 25, 10, 4.50, 'unidade']
                ];
                
                foreach ($produtos as $produto) {
                    $stmt = $conn->prepare("INSERT INTO produtos (nome, categoria, estoque_atual, estoque_minimo, preco, unidade_medida) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssiids", $produto[0], $produto[1], $produto[2], $produto[3], $produto[4], $produto[5]);
                    $stmt->execute();
                }
            }
            
            $result = $conn->query("SELECT COUNT(*) as total FROM clientes");
            $row = $result->fetch_assoc();
            if ($row['total'] == 0) {
                $clientes = [
                    ['Ana Silva', 'ana@example.com', '(11) 99999-9999', 'Rua A, 123 - São Paulo/SP'],
                    ['Carlos Oliveira', 'carlos@example.com', '(11) 98888-8888', 'Av. B, 456 - São Paulo/SP'],
                    ['Mariana Costa', 'mariana@example.com', '(11) 97777-7777', 'Rua C, 789 - São Paulo/SP'],
                    ['Roberto Santos', 'roberto@example.com', '(11) 96666-6666', 'Av. D, 101 - São Paulo/SP']
                ];
                
                foreach ($clientes as $cliente) {
                    $stmt = $conn->prepare("INSERT INTO clientes (nome, email, telefone, endereco) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssss", $cliente[0], $cliente[1], $cliente[2], $cliente[3]);
                    $stmt->execute();
                }
            }
            
            $result = $conn->query("SELECT COUNT(*) as total FROM usuarios");
            $row = $result->fetch_assoc();
            if ($row['total'] == 0) {
                $senha = password_hash('admin123', PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", 'Administrador', 'admin@quitutes.com', $senha);
                $stmt->execute();
            }
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } else {
            die("Error creating database: " . $conn->error);
        }
    }

    // Sistema de autenticação
    session_start();

    // Verificar se o usuário está logado
    $logged_in = isset($_SESSION['user_id']);
    $user_name = $logged_in ? $_SESSION['user_name'] : '';

    // Processar login
    if (isset($_POST['login'])) {
        $email = $conn->real_escape_string($_POST['email']);
        $password = $_POST['password'];
        
        $sql = "SELECT id, nome, senha FROM usuarios WHERE email = '$email'";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['senha'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_name'] = $row['nome'];
                $logged_in = true;
                $user_name = $row['nome'];
                
                // Redirecionar para evitar reenvio do formulário
                header("Location: ".$_SERVER['PHP_SELF']);
                exit();
            } else {
                $login_error = "Senha incorreta";
            }
        } else {
            $login_error = "Usuário não encontrado";
        }
    }

    // Processar logout
    if (isset($_GET['logout'])) {
        session_destroy();
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }

    // Se não estiver logado, mostrar tela de login
    if (!$logged_in) {
    ?>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <div class="mx-auto h-12 w-12 rounded-full bg-indigo-600 flex items-center justify-center">
                    <i class="fas fa-cookie-bite text-white text-xl"></i>
                </div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Acesse sua conta
                </h2>
            </div>
            <form class="mt-8 space-y-6" method="POST">
                <input type="hidden" name="login" value="true">
                <div class="rounded-md shadow-sm space-y-4">
                    <div>
                        <label for="email" class="sr-only">Email</label>
                        <input id="email" name="email" type="email" required
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                            placeholder="Email">
                    </div>
                    <div>
                        <label for="password" class="sr-only">Senha</label>
                        <input id="password" name="password" type="password" required
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                            placeholder="Senha">
                    </div>
                </div>

                <?php if (isset($login_error)): ?>
                <div class="text-red-500 text-sm text-center">
                    <?php echo $login_error; ?>
                </div>
                <?php endif; ?>

                <div>
                    <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <i class="fas fa-sign-in-alt"></i>
                        </span>
                        Entrar
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php
        exit();
    }

    // Consultas para o dashboard
    $vendas_hoje = 0;
    $pedidos_hoje = 0;
    $produtos_estoque = 0;
    $produtos_baixo_estoque = 0;
    $clientes_novos = 0;
    $pedidos_recentes = [];
    $produtos_baixo_estoque_lista = [];

    // Vendas hoje
    $sql = "SELECT SUM(valor_total) as total FROM pedidos WHERE DATE(data_pedido) = CURDATE() AND status != 'cancelado'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $vendas_hoje = $row['total'] ? $row['total'] : 0;
    }

    // Pedidos hoje
    $sql = "SELECT COUNT(*) as total FROM pedidos WHERE DATE(data_pedido) = CURDATE() AND status != 'cancelado'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $pedidos_hoje = $row['total'];
    }

    // Produtos em estoque
    $sql = "SELECT COUNT(*) as total FROM produtos WHERE estoque_atual > 0";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $produtos_estoque = $row['total'];
    }

    // Produtos com estoque baixo
    $sql = "SELECT COUNT(*) as total FROM produtos WHERE estoque_atual <= estoque_minimo";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $produtos_baixo_estoque = $row['total'];
    }

    // Clientes novos este mês
    $sql = "SELECT COUNT(*) as total FROM clientes WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $clientes_novos = $row['total'];
    }

    // Pedidos recentes
    $sql = "SELECT p.id, c.nome as cliente_nome, COUNT(i.id) as itens, p.valor_total, p.status, p.data_pedido 
            FROM pedidos p 
            LEFT JOIN itens_pedido i ON p.id = i.pedido_id 
            LEFT JOIN clientes c ON p.cliente_id = c.id 
            WHERE p.status != 'cancelado'
            GROUP BY p.id 
            ORDER BY p.data_pedido DESC 
            LIMIT 5";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $pedidos_recentes[] = $row;
        }
    }

    // Produtos com estoque baixo
    $sql = "SELECT id, nome, categoria, estoque_atual, estoque_minimo, unidade_medida 
            FROM produtos 
            WHERE estoque_atual <= estoque_minimo 
            ORDER BY estoque_atual ASC 
            LIMIT 5";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $produtos_baixo_estoque_lista[] = $row;
        }
    }

    // Vendas semanais para o gráfico
    $vendas_semanais = [0, 0, 0, 0, 0, 0, 0];
    $sql = "SELECT DAYOFWEEK(data_pedido) as dia_semana, SUM(valor_total) as total 
            FROM pedidos 
            WHERE data_pedido BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND CURDATE() 
            AND status != 'cancelado'
            GROUP BY DAYOFWEEK(data_pedido)";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Domingo = 1, Segunda = 2, etc.
            // Queremos Segunda = 0, Terça = 1, etc.
            $index = ($row['dia_semana'] + 5) % 7;
            $vendas_semanais[$index] = $row['total'];
        }
    }

    // Produtos mais vendidos para o gráfico
    $produtos_mais_vendidos = [];
    $sql = "SELECT pr.nome, SUM(i.quantidade) as total 
            FROM itens_pedido i 
            JOIN produtos pr ON i.produto_id = pr.id 
            JOIN pedidos p ON i.pedido_id = p.id 
            WHERE p.data_pedido BETWEEN DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND CURDATE() 
            AND p.status != 'cancelado'
            GROUP BY pr.nome 
            ORDER BY total DESC 
            LIMIT 5";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $produtos_mais_vendidos[] = $row;
        }
    }
    ?>

    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="sidebar bg-indigo-700 text-white w-64 flex flex-col fixed h-full">
            <a href="index.html">
                <div class="p-4 flex items-center space-x-3 border-b border-indigo-600">
                    <div class="bg-white p-2 rounded-lg">
                        <i class="fas fa-cookie-bite text-indigo-700 text-xl"></i>
                    </div>
                    <span class="logo-text font-bold text-xl">Quitutes</span>
                </div>
            </a>

            <div class="p-4 flex-1 overflow-y-auto">
                <nav>
                    <ul class="space-y-2">
                        <li>
                            <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="nav-item flex items-center p-3 rounded-lg bg-indigo-800">
                                <i class="fas fa-tachometer-alt mr-3"></i>
                                <span class="nav-text">Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="estoque.php" class="nav-item flex items-center p-3 rounded-lg hover:bg-indigo-800 transition">
                                <i class="fas fa-box-open mr-3"></i>
                                <span class="nav-text">Estoque</span>
                            </a>
                        </li>
                        <li>
                            <a href="receitas.php" class="nav-item flex items-center p-3 rounded-lg hover:bg-indigo-800 transition">
                                <i class="fas fa-utensils mr-3"></i>
                                <span class="nav-text">Receitas</span>
                            </a>
                        </li>
                        <li>
                            <a href="tarefas.php" class="nav-item flex items-center p-3 rounded-lg hover:bg-indigo-800">
                                <i class="fas fa-tasks mr-3"></i>
                                <span class="nav-text">Tarefas</span>
                            </a>
                        </li>
                        <li>
                            <a href="financas.php" class="nav-item flex items-center p-3 rounded-lg hover:bg-indigo-800 transition">
                                <i class="fas fa-money-bill-wave mr-3"></i>
                                <span class="nav-text">Finanças</span>
                            </a>
                        </li>
                        <li>
                            <a href="vendas.php" class="nav-item flex items-center p-3 rounded-lg hover:bg-indigo-800 transition">
                                <i class="fas fa-shopping-cart mr-3"></i>
                                <span class="nav-text">Vendas</span>
                            </a>
                        </li>
                        <li>
                            <a href="relatorios.php" class="nav-item flex items-center p-3 rounded-lg hover:bg-indigo-800 transition">
                                <i class="fas fa-chart-bar mr-3"></i>
                                <span class="nav-text">Relatórios</span>
                            </a>
                        </li>
                        <li>
                            <a href="sustentabilidade.php" class="nav-item flex items-center p-3 rounded-lg hover:bg-indigo-800 transition">
                                <i class="fas fa-leaf mr-3"></i>
                                <span class="nav-text">Sustentabilidade</span>
                            </a>
                        </li>
                        <li>
                            <a href="config.php" class="nav-item flex items-center p-3 rounded-lg hover:bg-indigo-800 transition">
                                <i class="fas fa-cog mr-3"></i>
                                <span class="nav-text">Configurações</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>

            <div class="p-4 border-t border-indigo-600">
                <button id="toggle-sidebar" class="flex items-center justify-center w-full p-2 rounded-lg hover:bg-indigo-800 transition">
                    <i class="fas fa-chevron-left"></i>
                    <span class="nav-text ml-3">Recolher</span>
                </button>
                <div class="mt-4 pt-4 border-t border-indigo-600">
                    <a href="?logout=1" class="flex items-center p-2 rounded-lg hover:bg-indigo-800 transition">
                        <i class="fas fa-sign-out-alt mr-3"></i>
                        <span class="nav-text">Sair</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content ml-64 flex-1 overflow-y-auto">
            <!-- Header -->
            <header class="bg-white shadow-sm p-4 flex justify-between items-center sticky top-0 z-10">
                <h1 class="text-2xl font-bold text-gray-800">
                    <i class="fas fa-tachometer-alt text-indigo-600 mr-2"></i>
                    Dashboard
                </h1>

                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" placeholder="Pesquisar..."
                            class="pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div class="flex items-center space-x-2">
                        <div class="relative">
                            <i class="fas fa-bell text-gray-600 text-xl cursor-pointer hover:text-indigo-600"></i>
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">3</span>
                        </div>

                        <div class="flex items-center space-x-2">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center cursor-pointer">
                                <i class="fas fa-user text-indigo-600"></i>
                            </div>
                            <span class="text-sm font-medium"><?php echo htmlspecialchars($user_name); ?></span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="p-6">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white rounded-xl shadow-sm p-6 card-hover transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Vendas Hoje</p>
                                <h3 class="text-2xl font-bold text-gray-800 mt-1">R$ <?php echo number_format($vendas_hoje, 2, ',', '.'); ?></h3>
                                <?php
                                $sql = "SELECT SUM(valor_total) as total FROM pedidos WHERE DATE(data_pedido) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND status != 'cancelado'";
                                $result = $conn->query($sql);
                                $vendas_ontem = 0;
                                if ($result->num_rows > 0) {
                                    $row = $result->fetch_assoc();
                                    $vendas_ontem = $row['total'] ? $row['total'] : 0;
                                }
                                
                                $diferenca = 0;
                                if ($vendas_ontem > 0) {
                                    $diferenca = (($vendas_hoje - $vendas_ontem) / $vendas_ontem) * 100;
                                } elseif ($vendas_hoje > 0) {
                                    $diferenca = 100;
                                }
                                ?>
                                <p class="text-sm <?php echo $diferenca >= 0 ? 'text-green-500' : 'text-red-500'; ?> mt-2 flex items-center">
                                    <i class="fas <?php echo $diferenca >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'; ?> mr-1"></i>
                                    <span><?php echo abs(round($diferenca)); ?>% em relação a ontem</span>
                                </p>
                            </div>
                            <div class="bg-indigo-100 p-3 rounded-lg">
                                <i class="fas fa-shopping-bag text-indigo-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-6 card-hover transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Pedidos Hoje</p>
                                <h3 class="text-2xl font-bold text-gray-800 mt-1"><?php echo $pedidos_hoje; ?></h3>
                                <?php
                                $sql = "SELECT COUNT(*) as total FROM pedidos WHERE DATE(data_pedido) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND status != 'cancelado'";
                                $result = $conn->query($sql);
                                $pedidos_ontem = 0;
                                if ($result->num_rows > 0) {
                                    $row = $result->fetch_assoc();
                                    $pedidos_ontem = $row['total'];
                                }
                                
                                $diferenca = 0;
                                if ($pedidos_ontem > 0) {
                                    $diferenca = (($pedidos_hoje - $pedidos_ontem) / $pedidos_ontem) * 100;
                                } elseif ($pedidos_hoje > 0) {
                                    $diferenca = 100;
                                }
                                ?>
                                <p class="text-sm <?php echo $diferenca >= 0 ? 'text-green-500' : 'text-red-500'; ?> mt-2 flex items-center">
                                    <i class="fas <?php echo $diferenca >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'; ?> mr-1"></i>
                                    <span><?php echo abs(round($diferenca)); ?>% em relação a ontem</span>
                                </p>
                            </div>
                            <div class="bg-green-100 p-3 rounded-lg">
                                <i class="fas fa-receipt text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-6 card-hover transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Produtos em Estoque</p>
                                <h3 class="text-2xl font-bold text-gray-800 mt-1"><?php echo $produtos_estoque; ?></h3>
                                <p class="text-sm <?php echo $produtos_baixo_estoque > 0 ? 'text-red-500' : 'text-green-500'; ?> mt-2 flex items-center">
                                    <i class="fas <?php echo $produtos_baixo_estoque > 0 ? 'fa-exclamation-circle' : 'fa-check-circle'; ?> mr-1"></i>
                                    <span><?php echo $produtos_baixo_estoque; ?> produto(s) com estoque baixo</span>
                                </p>
                            </div>
                            <div class="bg-yellow-100 p-3 rounded-lg">
                                <i class="fas fa-boxes text-yellow-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-6 card-hover transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Clientes Novos</p>
                                <h3 class="text-2xl font-bold text-gray-800 mt-1"><?php echo $clientes_novos; ?></h3>
                                <?php
                                $sql = "SELECT COUNT(*) as total FROM clientes WHERE MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(created_at) = YEAR(CURDATE())";
                                $result = $conn->query($sql);
                                $clientes_mes_passado = 0;
                                if ($result->num_rows > 0) {
                                    $row = $result->fetch_assoc();
                                    $clientes_mes_passado = $row['total'];
                                }
                                ?>
                                <p class="text-sm text-blue-500 mt-2 flex items-center">
                                    <i class="fas fa-user-plus mr-1"></i>
                                    <span>Este mês: <?php echo $clientes_novos; ?></span>
                                </p>
                            </div>
                            <div class="bg-blue-100 p-3 rounded-lg">
                                <i class="fas fa-users text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Sales Chart -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-gray-800">Vendas Semanais</h2>
                            <div class="flex space-x-2">
                                <button class="px-3 py-1 text-sm border rounded-lg bg-indigo-50 text-indigo-600">Semana</button>
                                <button class="px-3 py-1 text-sm border rounded-lg hover:bg-gray-50">Mês</button>
                                <button class="px-3 py-1 text-sm border rounded-lg hover:bg-gray-50">Ano</button>
                            </div>
                        </div>
                        <div class="chart-container">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>

                    <!-- Top Products Chart -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-gray-800">Produtos Mais Vendidos</h2>
                            <button class="px-3 py-1 text-sm border rounded-lg bg-indigo-50 text-indigo-600 flex items-center">
                                <i class="fas fa-filter mr-1"></i>
                                <span>Filtrar</span>
                            </button>
                        </div>
                        <div class="chart-container">
                            <canvas id="productsChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                    <div class="p-4 border-b flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-gray-800">Pedidos Recentes</h2>
                        <a href="vendas.php" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium flex items-center">
                            <span>Ver Todos</span>
                            <i class="fas fa-chevron-right ml-1"></i>
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pedido #</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Itens</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($pedidos_recentes as $pedido): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#QT-<?php echo str_pad($pedido['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center mr-3">
                                                <i class="fas fa-user text-indigo-600"></i>
                                            </div>
                                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($pedido['cliente_nome']); ?></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $pedido['itens']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">R$ <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $status_classes = [
                                            'pendente' => 'bg-gray-100 text-gray-800',
                                            'em_preparo' => 'bg-yellow-100 text-yellow-800',
                                            'a_caminho' => 'bg-blue-100 text-blue-800',
                                            'entregue' => 'bg-green-100 text-green-800',
                                            'cancelado' => 'bg-red-100 text-red-800'
                                        ];
                                        $status_text = [
                                            'pendente' => 'Pendente',
                                            'em_preparo' => 'Em preparo',
                                            'a_caminho' => 'A caminho',
                                            'entregue' => 'Entregue',
                                            'cancelado' => 'Cancelado'
                                        ];
                                        ?>
                                        <span class="px-2 py-1 text-xs rounded-full <?php echo $status_classes[$pedido['status']]; ?>">
                                            <?php echo $status_text[$pedido['status']]; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                                        <?php echo date('d/m/Y', strtotime($pedido['data_pedido'])); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 border-t flex items-center justify-between text-sm text-gray-600">
                        <div>Mostrando <span class="font-medium">1</span> a <span class="font-medium"><?php echo count($pedidos_recentes); ?></span> de <span class="font-medium"><?php echo count($pedidos_recentes); ?></span> pedidos</div>
                        <div class="flex space-x-2">
                            <button class="px-3 py-1 border rounded hover:bg-gray-50"><i class="fas fa-chevron-left"></i></button>
                            <button class="px-3 py-1 border rounded bg-indigo-600 text-white">1</button>
                            <button class="px-3 py-1 border rounded hover:bg-gray-50"><i class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Products -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="p-4 border-b flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-gray-800">Produtos com Estoque Baixo</h2>
                        <a href="estoque.php" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium flex items-center">
                            <span>Ver Estoque Completo</span>
                            <i class="fas fa-chevron-right ml-1"></i>
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produto</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estoque Atual</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estoque Mínimo</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ação</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($produtos_baixo_estoque_lista as $produto): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center mr-3">
                                                <i class="fas fa-box text-indigo-600"></i>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($produto['nome']); ?></div>
                                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($produto['unidade_medida']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($produto['categoria']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-red-500 font-medium"><?php echo $produto['estoque_atual']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $produto['estoque_minimo']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button class="text-indigo-600 hover:text-indigo-900">Repor</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Toggle sidebar
        document.getElementById('toggle-sidebar').addEventListener('click', function () {
            document.querySelector('.sidebar').classList.toggle('collapsed');
            document.querySelector('.main-content').classList.toggle('ml-64');
            document.querySelector('.main-content').classList.toggle('ml-20');

            const icon = this.querySelector('i');
            if (document.querySelector('.sidebar').classList.contains('collapsed')) {
                icon.classList.remove('fa-chevron-left');
                icon.classList.add('fa-chevron-right');
            } else {
                icon.classList.remove('fa-chevron-right');
                icon.classList.add('fa-chevron-left');
            }
        });

        // Initialize charts
        document.addEventListener('DOMContentLoaded', function () {
            // Sales Chart
            const salesCtx = document.getElementById('salesChart').getContext('2d');
            const salesChart = new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
                    datasets: [{
                        label: 'Vendas (R$)',
                        data: <?php echo json_encode($vendas_semanais); ?>,
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        borderColor: 'rgba(79, 70, 229, 1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Products Chart
            const productsCtx = document.getElementById('productsChart').getContext('2d');
            const productsChart = new Chart(productsCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_column($produtos_mais_vendidos, 'nome')); ?>,
                    datasets: [{
                        label: 'Vendas',
                        data: <?php echo json_encode(array_column($produtos_mais_vendidos, 'total')); ?>,
                        backgroundColor: [
                            'rgba(79, 70, 229, 0.7)',
                            'rgba(99, 102, 241, 0.7)',
                            'rgba(129, 140, 248, 0.7)',
                            'rgba(165, 180, 252, 0.7)',
                            'rgba(199, 210, 254, 0.7)'
                        ],
                        borderColor: [
                            'rgba(79, 70, 229, 1)',
                            'rgba(99, 102, 241, 1)',
                            'rgba(129, 140, 248, 1)',
                            'rgba(165, 180, 252, 1)',
                            'rgba(199, 210, 254, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Show notification
            function showNotification(message, type) {
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 px-4 py-2 rounded-lg shadow-lg text-white flex items-center ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
                notification.innerHTML = `
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>
                    ${message}
                `;

                document.body.appendChild(notification);

                setTimeout(() => {
                    notification.classList.add('opacity-0', 'translate-x-4', 'transition');
                    setTimeout(() => notification.remove(), 300);
                }, 3000);
            }
        });
    </script>
</body>

</html>