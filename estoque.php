<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Estoque - Quitutes</title>
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

        .table-row-hover:hover {
            background-color: #f8fafc;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body class="bg-gray-50">
    <?php
    // Configurações do banco de dados
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "quitutes_ai";

    // Criar conexão
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Verificar conexão
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Sistema de autenticação
    session_start();

    // Verificar se o usuário está logado
    $logged_in = isset($_SESSION['user_id']);
    $user_name = $logged_in ? $_SESSION['user_name'] : '';

    // Se não estiver logado, redirecionar para login
    if (!$logged_in) {
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }

    // Processar adição de produto
    if (isset($_POST['adicionar_produto'])) {
        $nome = $conn->real_escape_string($_POST['nome']);
        $categoria = $conn->real_escape_string($_POST['categoria']);
        $estoque_atual = (int)$_POST['estoque_atual'];
        $estoque_minimo = (int)$_POST['estoque_minimo'];
        $preco = (float)$_POST['preco'];
        $unidade_medida = $conn->real_escape_string($_POST['unidade_medida']);
        
        $stmt = $conn->prepare("INSERT INTO produtos (nome, categoria, estoque_atual, estoque_minimo, preco, unidade_medida) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiids", $nome, $categoria, $estoque_atual, $estoque_minimo, $preco, $unidade_medida);
        $stmt->execute();
        
        $_SESSION['success_message'] = "Produto adicionado com sucesso!";
        header("Location: estoque.php");
        exit();
    }

    // Processar atualização de produto
    if (isset($_POST['atualizar_produto'])) {
        $id = (int)$_POST['id'];
        $nome = $conn->real_escape_string($_POST['nome']);
        $categoria = $conn->real_escape_string($_POST['categoria']);
        $estoque_atual = (int)$_POST['estoque_atual'];
        $estoque_minimo = (int)$_POST['estoque_minimo'];
        $preco = (float)$_POST['preco'];
        $unidade_medida = $conn->real_escape_string($_POST['unidade_medida']);
        
        $stmt = $conn->prepare("UPDATE produtos SET nome = ?, categoria = ?, estoque_atual = ?, estoque_minimo = ?, preco = ?, unidade_medida = ? WHERE id = ?");
        $stmt->bind_param("ssiidsi", $nome, $categoria, $estoque_atual, $estoque_minimo, $preco, $unidade_medida, $id);
        $stmt->execute();
        
        $_SESSION['success_message'] = "Produto atualizado com sucesso!";
        header("Location: estoque.php");
        exit();
    }

    // Processar remoção de produto
    if (isset($_GET['remover_produto'])) {
        $id = (int)$_GET['remover_produto'];
        
        $stmt = $conn->prepare("DELETE FROM produtos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $_SESSION['success_message'] = "Produto removido com sucesso!";
        header("Location: estoque.php");
        exit();
    }

    // Obter lista de produtos
    $produtos = [];
    $sql = "SELECT * FROM produtos ORDER BY nome ASC";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $produtos[] = $row;
        }
    }

    // Obter produtos com estoque baixo
    $produtos_baixo_estoque = [];
    $sql = "SELECT * FROM produtos WHERE estoque_atual <= estoque_minimo ORDER BY estoque_atual ASC";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $produtos_baixo_estoque[] = $row;
        }
    }

    // Obter categorias de produtos
    $categorias = [];
    $sql = "SELECT DISTINCT categoria FROM produtos ORDER BY categoria ASC";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categorias[] = $row['categoria'];
        }
    }
    ?>

    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="sidebar bg-indigo-700 text-white w-64 flex flex-col fixed h-full">
            <a href="dashboard.php">
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
                            <a href="dashboard.php" class="nav-item flex items-center p-3 rounded-lg hover:bg-indigo-800 transition">
                                <i class="fas fa-tachometer-alt mr-3"></i>
                                <span class="nav-text">Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="estoque.php" class="nav-item flex items-center p-3 rounded-lg bg-indigo-800">
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
                            <a href="tarefas.php" class="nav-item flex items-center p-3 rounded-lg hover:bg-indigo-800 transition">
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
                    <i class="fas fa-box-open text-indigo-600 mr-2"></i>
                    Gestão de Estoque
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
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"><?php echo count($produtos_baixo_estoque); ?></span>
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
                <?php if (isset($_SESSION['success_message'])): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <p><?php echo $_SESSION['success_message']; ?></p>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                </div>
                <?php endif; ?>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white rounded-xl shadow-sm p-6 card-hover transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Total de Produtos</p>
                                <h3 class="text-2xl font-bold text-gray-800 mt-1"><?php echo count($produtos); ?></h3>
                                <p class="text-sm text-indigo-500 mt-2 flex items-center">
                                    <i class="fas fa-boxes mr-1"></i>
                                    <span><?php echo count($produtos_baixo_estoque); ?> com estoque baixo</span>
                                </p>
                            </div>
                            <div class="bg-indigo-100 p-3 rounded-lg">
                                <i class="fas fa-boxes text-indigo-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-6 card-hover transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Categorias</p>
                                <h3 class="text-2xl font-bold text-gray-800 mt-1"><?php echo count($categorias); ?></h3>
                                <p class="text-sm text-green-500 mt-2 flex items-center">
                                    <i class="fas fa-tags mr-1"></i>
                                    <span>Última: <?php echo !empty($categorias) ? end($categorias) : 'Nenhuma'; ?></span>
                                </p>
                            </div>
                            <div class="bg-green-100 p-3 rounded-lg">
                                <i class="fas fa-tags text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-6 card-hover transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Produtos em Falta</p>
                                <h3 class="text-2xl font-bold text-gray-800 mt-1">
                                    <?php 
                                    $sql = "SELECT COUNT(*) as total FROM produtos WHERE estoque_atual = 0";
                                    $result = $conn->query($sql);
                                    $produtos_falta = $result->fetch_assoc()['total'];
                                    echo $produtos_falta;
                                    ?>
                                </h3>
                                <p class="text-sm text-red-500 mt-2 flex items-center">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    <span>Precisa de atenção</span>
                                </p>
                            </div>
                            <div class="bg-red-100 p-3 rounded-lg">
                                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-6 card-hover transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Valor Total</p>
                                <h3 class="text-2xl font-bold text-gray-800 mt-1">
                                    <?php 
                                    $sql = "SELECT SUM(estoque_atual * preco) as total FROM produtos";
                                    $result = $conn->query($sql);
                                    $valor_total = $result->fetch_assoc()['total'];
                                    echo 'R$ ' . number_format($valor_total ? $valor_total : 0, 2, ',', '.');
                                    ?>
                                </h3>
                                <p class="text-sm text-blue-500 mt-2 flex items-center">
                                    <i class="fas fa-chart-line mr-1"></i>
                                    <span>Investimento em estoque</span>
                                </p>
                            </div>
                            <div class="bg-blue-100 p-3 rounded-lg">
                                <i class="fas fa-dollar-sign text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Product Form -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">
                        <i class="fas fa-plus-circle text-indigo-600 mr-2"></i>
                        Adicionar Novo Produto
                    </h2>

                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="adicionar_produto" value="1">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">Nome do Produto</label>
                                <input type="text" id="nome" name="nome" required
                                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            <div>
                                <label for="categoria" class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                                <input type="text" id="categoria" name="categoria" list="categorias" required
                                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <datalist id="categorias">
                                    <?php foreach ($categorias as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>">
                                    <?php endforeach; ?>
                                </datalist>
                            </div>

                            <div>
                                <label for="unidade_medida" class="block text-sm font-medium text-gray-700 mb-1">Unidade de Medida</label>
                                <select id="unidade_medida" name="unidade_medida" required
                                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="unidade">Unidade</option>
                                    <option value="kg">Quilograma (kg)</option>
                                    <option value="g">Grama (g)</option>
                                    <option value="l">Litro (l)</option>
                                    <option value="ml">Mililitro (ml)</option>
                                    <option value="fatia">Fatia</option>
                                    <option value="porção">Porção</option>
                                </select>
                            </div>

                            <div>
                                <label for="estoque_atual" class="block text-sm font-medium text-gray-700 mb-1">Estoque Atual</label>
                                <input type="number" id="estoque_atual" name="estoque_atual" min="0" required
                                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            <div>
                                <label for="estoque_minimo" class="block text-sm font-medium text-gray-700 mb-1">Estoque Mínimo</label>
                                <input type="number" id="estoque_minimo" name="estoque_minimo" min="0" required
                                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            <div>
                                <label for="preco" class="block text-sm font-medium text-gray-700 mb-1">Preço Unitário (R$)</label>
                                <input type="number" id="preco" name="preco" min="0" step="0.01" required
                                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg transition flex items-center">
                                <i class="fas fa-plus-circle mr-2"></i>
                                Adicionar Produto
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Inventory Table -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                    <div class="p-4 border-b flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-gray-800">
                            <i class="fas fa-boxes text-indigo-600 mr-2"></i>
                            Lista de Produtos
                        </h2>
                        <div class="flex space-x-2">
                            <button class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition flex items-center">
                                <i class="fas fa-file-export mr-2"></i>
                                Exportar
                            </button>
                            <button class="bg-indigo-50 hover:bg-indigo-100 text-indigo-600 px-4 py-2 rounded-lg transition flex items-center">
                                <i class="fas fa-filter mr-2"></i>
                                Filtrar
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produto</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estoque</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preço</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($produtos as $index => $produto): 
                                    $status_class = $produto['estoque_atual'] == 0 ? 'bg-red-100 text-red-800' : 
                                                  ($produto['estoque_atual'] <= $produto['estoque_minimo'] ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800');
                                    $status_text = $produto['estoque_atual'] == 0 ? 'Esgotado' : 
                                                 ($produto['estoque_atual'] <= $produto['estoque_minimo'] ? 'Baixo' : 'OK');
                                ?>
                                <tr class="table-row-hover transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $index + 1; ?></td>
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo htmlspecialchars($produto['categoria']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span class="mr-2 font-medium"><?php echo $produto['estoque_atual']; ?></span>
                                            <span class="px-2 py-1 text-xs rounded-full <?php echo $status_class; ?>">
                                                <?php echo $status_text; ?>
                                            </span>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">Mín: <?php echo $produto['estoque_minimo']; ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button onclick="openEditPopup(
                                            <?php echo $produto['id']; ?>, 
                                            '<?php echo addslashes($produto['nome']); ?>', 
                                            '<?php echo addslashes($produto['categoria']); ?>', 
                                            <?php echo $produto['estoque_atual']; ?>, 
                                            <?php echo $produto['estoque_minimo']; ?>, 
                                            <?php echo $produto['preco']; ?>, 
                                            '<?php echo $produto['unidade_medida']; ?>'
                                        )" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="estoque.php?remover_produto=<?php echo $produto['id']; ?>" onclick="return confirm('Tem certeza que deseja remover este produto?')" class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 border-t flex items-center justify-between text-sm text-gray-600">
                        <div>Mostrando <span class="font-medium">1</span> a <span class="font-medium"><?php echo count($produtos); ?></span> de <span class="font-medium"><?php echo count($produtos); ?></span> produtos</div>
                        <div class="flex space-x-2">
                            <button class="px-3 py-1 border rounded hover:bg-gray-50"><i class="fas fa-chevron-left"></i></button>
                            <button class="px-3 py-1 border rounded bg-indigo-600 text-white">1</button>
                            <button class="px-3 py-1 border rounded hover:bg-gray-50"><i class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Products -->
                <?php if (!empty($produtos_baixo_estoque)): ?>
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="p-4 border-b flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-gray-800">
                            <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                            Produtos com Estoque Baixo
                        </h2>
                        <a href="estoque.php" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium flex items-center">
                            <span>Ver Todos</span>
                            <i class="fas fa-chevron-right ml-1"></i>
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produto</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estoque Atual</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estoque Mínimo</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ação</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($produtos_baixo_estoque as $produto): ?>
                                <tr class="hover:bg-yellow-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center mr-3">
                                                <i class="fas fa-box text-yellow-600"></i>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($produto['nome']); ?></div>
                                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($produto['categoria']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-yellow-600">
                                        <?php echo $produto['estoque_atual']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo $produto['estoque_minimo']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button onclick="openEditPopup(
                                            <?php echo $produto['id']; ?>, 
                                            '<?php echo addslashes($produto['nome']); ?>', 
                                            '<?php echo addslashes($produto['categoria']); ?>', 
                                            <?php echo $produto['estoque_atual']; ?>, 
                                            <?php echo $produto['estoque_minimo']; ?>, 
                                            <?php echo $produto['preco']; ?>, 
                                            '<?php echo $produto['unidade_medida']; ?>'
                                        )" class="text-indigo-600 hover:text-indigo-900">
                                            Repor Estoque
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Edit Popup -->
    <div id="popup-overlay" class="fixed inset-0 overlay z-50 hidden"></div>
    <div id="edit-popup" class="hidden fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-xl shadow-xl z-50 w-full max-w-md popup">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-800">
                    <i class="fas fa-edit text-indigo-600 mr-2"></i>
                    Editar Produto
                </h3>
                <button id="close-popup" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="edit-form" method="POST" class="space-y-4">
                <input type="hidden" name="atualizar_produto" value="1">
                <input type="hidden" id="edit-id" name="id">
                
                <div>
                    <label for="edit-nome" class="block text-sm font-medium text-gray-700 mb-1">Nome do Produto</label>
                    <input type="text" id="edit-nome" name="nome" required
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label for="edit-categoria" class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                    <input type="text" id="edit-categoria" name="categoria" list="categorias" required
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="edit-estoque-atual" class="block text-sm font-medium text-gray-700 mb-1">Estoque Atual</label>
                        <input type="number" id="edit-estoque-atual" name="estoque_atual" min="0" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="edit-estoque-minimo" class="block text-sm font-medium text-gray-700 mb-1">Estoque Mínimo</label>
                        <input type="number" id="edit-estoque-minimo" name="estoque_minimo" min="0" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="edit-preco" class="block text-sm font-medium text-gray-700 mb-1">Preço Unitário (R$)</label>
                        <input type="number" id="edit-preco" name="preco" min="0" step="0.01" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="edit-unidade-medida" class="block text-sm font-medium text-gray-700 mb-1">Unidade de Medida</label>
                        <select id="edit-unidade-medida" name="unidade_medida" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="unidade">Unidade</option>
                            <option value="kg">Quilograma (kg)</option>
                            <option value="g">Grama (g)</option>
                            <option value="l">Litro (l)</option>
                            <option value="ml">Mililitro (ml)</option>
                            <option value="fatia">Fatia</option>
                            <option value="porção">Porção</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button type="submit"
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition flex items-center">
                        <i class="fas fa-save mr-2"></i>
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>

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

        // Edit popup functions
        function openEditPopup(id, nome, categoria, estoque_atual, estoque_minimo, preco, unidade_medida) {
            document.getElementById('edit-id').value = id;
            document.getElementById('edit-nome').value = nome;
            document.getElementById('edit-categoria').value = categoria;
            document.getElementById('edit-estoque-atual').value = estoque_atual;
            document.getElementById('edit-estoque-minimo').value = estoque_minimo;
            document.getElementById('edit-preco').value = preco;
            document.getElementById('edit-unidade-medida').value = unidade_medida;
            
            document.getElementById('popup-overlay').classList.remove('hidden');
            document.getElementById('edit-popup').classList.remove('hidden');
        }

        function closeEditPopup() {
            document.getElementById('popup-overlay').classList.add('hidden');
            document.getElementById('edit-popup').classList.add('hidden');
        }

        document.getElementById('close-popup').addEventListener('click', closeEditPopup);
        document.getElementById('popup-overlay').addEventListener('click', closeEditPopup);

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

        // Auto-close success message after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const successMessage = document.querySelector('.bg-green-100');
            if (successMessage) {
                setTimeout(() => {
                    successMessage.style.opacity = '0';
                    setTimeout(() => successMessage.remove(), 300);
                }, 5000);
            }
        });
    </script>
</body>

</html>