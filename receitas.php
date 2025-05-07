<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receitas - Quitutes</title>
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

        .receita-card {
            transition: all 0.3s ease;
        }

        .receita-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.1);
        }

        .ingredient-line {
            white-space: pre-line;
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
if (!isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Criar tabela de receitas se não existir
$sql = "CREATE TABLE IF NOT EXISTS receitas (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    descricao TEXT,
    ingredientes TEXT NOT NULL,
    imagem VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
$conn->query($sql);

// Processar formulário de receita
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['salvar_receita'])) {
        $titulo = $conn->real_escape_string($_POST['titulo']);
        $categoria = $conn->real_escape_string($_POST['categoria']);
        $descricao = $conn->real_escape_string($_POST['descricao']);
        $ingredientes = $conn->real_escape_string($_POST['ingredientes']);
        
        // Processar imagem (simplificado - em produção usar upload seguro)
        $imagem = '';
        if (!empty($_FILES['imagem']['name'])) {
            $imagem = 'uploads/' . basename($_FILES['imagem']['name']);
            move_uploaded_file($_FILES['imagem']['tmp_name'], $imagem);
        }
        
        if (isset($_POST['receita_id']) && !empty($_POST['receita_id'])) {
            // Atualizar receita existente
            $receita_id = intval($_POST['receita_id']);
            $sql = "UPDATE receitas SET titulo='$titulo', categoria='$categoria', descricao='$descricao', ingredientes='$ingredientes'";
            if (!empty($imagem)) {
                $sql .= ", imagem='$imagem'";
            }
            $sql .= " WHERE id=$receita_id";
        } else {
            // Inserir nova receita
            $sql = "INSERT INTO receitas (titulo, categoria, descricao, ingredientes, imagem) 
                    VALUES ('$titulo', '$categoria', '$$descricao', '$ingredientes', '$imagem')";
        }
        
        if ($conn->query($sql) === TRUE) {
            $_SESSION['success_message'] = "Receita salva com sucesso!";
        } else {
            $_SESSION['error_message'] = "Erro ao salvar receita: " . $conn->error;
        }
        
        header("Location: receitas.php");
        exit();
    }
}

// Processar exclusão de receita
if (isset($_GET['excluir'])) {
    $receita_id = intval($_GET['excluir']);
    $sql = "DELETE FROM receitas WHERE id=$receita_id";
    
    if ($conn->query($sql) === TRUE) {
        $_SESSION['success_message'] = "Receita excluída com sucesso!";
    } else {
        $_SESSION['error_message'] = "Erro ao excluir receita: " . $conn->error;
    }
    
    header("Location: receitas.php");
    exit();
}

// Obter lista de receitas
$receitas = [];
$sql = "SELECT * FROM receitas ORDER BY created_at DESC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $receitas[] = $row;
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
                            <a href="dashboard.php"
                                class="nav-item flex items-center p-3 rounded-lg hover:bg-indigo-800 transition">
                                <i class="fas fa-tachometer-alt mr-3"></i>
                                <span class="nav-text">Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="estoque.php"
                                class="nav-item flex items-center p-3 rounded-lg hover:bg-indigo-800 transition">
                                <i class="fas fa-box-open mr-3"></i>
                                <span class="nav-text">Estoque</span>
                            </a>
                        </li>
                        <li>
                            <a href="receitas.php" class="nav-item flex items-center p-3 rounded-lg bg-indigo-800">
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
                <button id="toggle-sidebar"
                    class="flex items-center justify-center w-full p-2 rounded-lg hover:bg-indigo-800 transition">
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
                    <i class="fas fa-utensils text-indigo-600 mr-2"></i>
                    Receitas
                </h1>

                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" id="busca-receita" placeholder="Buscar receita..."
                            class="pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div class="flex items-center space-x-2">
                        <div class="relative">
                            <i class="fas fa-bell text-gray-600 text-xl cursor-pointer hover:text-indigo-600"></i>
                            <span
                                class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">3</span>
                        </div>

                        <div
                            class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center cursor-pointer">
                            <i class="fas fa-user text-indigo-600"></i>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="p-6">
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?php echo $_SESSION['success_message']; ?></span>
                        <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                            <i class="fas fa-times"></i>
                        </span>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?php echo $_SESSION['error_message']; ?></span>
                        <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                            <i class="fas fa-times"></i>
                        </span>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>

                <!-- Filters and Add Button -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="filtro-categoria"
                                        class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                                    <select id="filtro-categoria"
                                        class="w-full pl-3 pr-10 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">Todas as categorias</option>
                                        <option value="massas">Massas</option>
                                        <option value="recheios doces">Recheios Doces</option>
                                        <option value="recheios salgados">Recheios Salgados</option>
                                        <option value="sobremesas">Sobremesas</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-end justify-end">
                            <button id="btn-add-receita"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg flex items-center">
                                <i class="fas fa-plus mr-2"></i>
                                Adicionar Receita
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Recipes List -->
                <div id="receitas-lista" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($receitas as $receita): ?>
                    <div class="col-span-1">
                        <div class="bg-white rounded-xl shadow-sm overflow-hidden receita-card cursor-pointer">
                            <img src="<?php echo $receita['imagem'] ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60'; ?>" 
                                 alt="<?php echo htmlspecialchars($receita['titulo']); ?>" 
                                 class="w-full h-48 object-cover">
                            <div class="p-4">
                                <h3 class="text-lg font-semibold text-gray-800 mb-1"><?php echo htmlspecialchars($receita['titulo']); ?></h3>
                                <span class="inline-block px-2 py-1 text-xs rounded-full <?php echo getCategoryColor($receita['categoria']); ?> mb-2">
                                    <?php echo formatCategory($receita['categoria']); ?>
                                </span>
                                <p class="text-sm text-gray-600 mb-3 line-clamp-2"><?php echo htmlspecialchars($receita['descricao'] ?? ''); ?></p>
                                <div class="flex space-x-2">
                                    <button onclick="editarReceita(<?php echo $receita['id']; ?>);event.stopPropagation();" 
                                            class="text-indigo-600 hover:text-indigo-800 text-sm font-medium flex items-center">
                                        <i class="fas fa-edit mr-1"></i> Editar
                                    </button>
                                    <button onclick="excluirReceita(<?php echo $receita['id']; ?>);event.stopPropagation();" 
                                            class="text-red-600 hover:text-red-800 text-sm font-medium flex items-center">
                                        <i class="fas fa-trash-alt mr-1"></i> Excluir
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Add/Edit Recipe Modal -->
    <div id="modal-receita" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900" id="modal-title">Adicionar Receita</h3>
                        <button type="button" id="close-modal" class="text-gray-400 hover:text-gray-500">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form id="form-receita" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="salvar_receita" value="1">
                        <input type="hidden" id="receita_id" name="receita_id" value="">
                        <div class="mb-4">
                            <label for="titulo" class="block text-sm font-medium text-gray-700">Título</label>
                            <input type="text" id="titulo" name="titulo"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                required>
                        </div>
                        <div class="mb-4">
                            <label for="categoria" class="block text-sm font-medium text-gray-700">Categoria</label>
                            <select id="categoria" name="categoria"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                required>
                                <option value="massas">Massas</option>
                                <option value="recheios doces">Recheios Doces</option>
                                <option value="recheios salgados">Recheios Salgados</option>
                                <option value="sobremesas">Sobremesas</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="imagem" class="block text-sm font-medium text-gray-700">Imagem</label>
                            <input type="file" id="imagem" name="imagem"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                accept="image/*">
                        </div>
                        <div class="mb-4">
                            <label for="descricao" class="block text-sm font-medium text-gray-700">Descrição</label>
                            <textarea id="descricao" name="descricao" rows="3"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                        </div>
                        <div class="mb-4">
                            <label for="ingredientes" class="block text-sm font-medium text-gray-700">Ingredientes (um
                                por linha: ingrediente:quantidade:unidade)</label>
                            <textarea id="ingredientes" name="ingredientes" rows="5"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Ex: Açúcar:200:g&#10;Farinha:500:g&#10;Ovo:3:un" required></textarea>
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="button" id="cancel-modal"
                                class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg">
                                Cancelar
                            </button>
                            <button type="submit"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg">
                                Salvar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- View Recipe Modal -->
    <div id="modal-visualizar-receita" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900" id="visualizar-titulo">Receita</h3>
                        <button type="button" id="close-view-modal" class="text-gray-400 hover:text-gray-500">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <img id="visualizar-imagem" src="" class="w-full h-48 object-cover rounded-lg mb-4"
                        alt="Imagem da Receita">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Categoria:</p>
                            <p class="text-gray-800" id="visualizar-categoria"></p>
                        </div>
                    </div>
                    <div class="mb-4">
                        <p class="text-sm font-medium text-gray-500">Descrição:</p>
                        <p class="text-gray-800" id="visualizar-descricao"></p>
                    </div>
                    <hr class="my-4">
                    <div class="mb-4">
                        <label for="qtd-porcoes" class="text-sm font-medium text-gray-500">Porções:</label>
                        <input type="number" id="qtd-porcoes"
                            class="ml-2 border border-gray-300 rounded-md shadow-sm py-1 px-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            value="1" min="1" style="width: 80px;">
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-2">Ingredientes:</p>
                        <ul id="visualizar-ingredientes" class="list-disc pl-5 ingredient-line"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let receitaEditandoId = null;
        let receitasCache = <?php echo json_encode($receitas); ?>;

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

        // Modal functions
        function openModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        // Event listeners for modals
        document.getElementById('btn-add-receita').addEventListener('click', () => {
            document.getElementById('form-receita').reset();
            receitaEditandoId = null;
            document.getElementById('modal-title').textContent = 'Adicionar Receita';
            document.getElementById('receita_id').value = '';
            openModal('modal-receita');
        });

        document.getElementById('close-modal').addEventListener('click', () => closeModal('modal-receita'));
        document.getElementById('cancel-modal').addEventListener('click', () => closeModal('modal-receita'));
        document.getElementById('close-view-modal').addEventListener('click', () => closeModal('modal-visualizar-receita'));

        // Close modals when clicking outside
        window.addEventListener('click', (event) => {
            if (event.target === document.getElementById('modal-receita')) {
                closeModal('modal-receita');
            }
            if (event.target === document.getElementById('modal-visualizar-receita')) {
                closeModal('modal-visualizar-receita');
            }
        });

        // Helper functions
        function getCategoryColor(category) {
            switch (category) {
                case 'massas': return 'bg-blue-100 text-blue-800';
                case 'recheios doces': return 'bg-purple-100 text-purple-800';
                case 'recheios salgados': return 'bg-yellow-100 text-yellow-800';
                case 'sobremesas': return 'bg-green-100 text-green-800';
                default: return 'bg-gray-100 text-gray-800';
            }
        }

        function formatCategory(category) {
            switch (category) {
                case 'massas': return 'Massa';
                case 'recheios doces': return 'Recheio Doce';
                case 'recheios salgados': return 'Recheio Salgado';
                case 'sobremesas': return 'Sobremesa';
                default: return category;
            }
        }

        // Filtro e busca
        function filtrarReceitas() {
            const busca = document.getElementById('busca-receita').value.toLowerCase();
            const categoria = document.getElementById('filtro-categoria').value;
            let filtradas = receitasCache.filter(r => r.titulo.toLowerCase().includes(busca));
            if (categoria) filtradas = filtradas.filter(r => r.categoria === categoria);
            renderizarReceitas(filtradas);
        }
        
        document.getElementById('busca-receita').addEventListener('input', filtrarReceitas);
        document.getElementById('filtro-categoria').addEventListener('change', filtrarReceitas);

        function renderizarReceitas(receitas) {
            const listaReceitas = document.getElementById('receitas-lista');
            listaReceitas.innerHTML = '';

            receitas.forEach(receita => {
                const card = document.createElement('div');
                card.className = 'col-span-1';
                card.innerHTML = `
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden receita-card cursor-pointer">
                        <img src="${receita.imagem || 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60'}" 
                             alt="${receita.titulo}" 
                             class="w-full h-48 object-cover">
                        <div class="p-4">
                            <h3 class="text-lg font-semibold text-gray-800 mb-1">${receita.titulo}</h3>
                            <span class="inline-block px-2 py-1 text-xs rounded-full ${getCategoryColor(receita.categoria)} mb-2">
                                ${formatCategory(receita.categoria)}
                            </span>
                            <p class="text-sm text-gray-600 mb-3 line-clamp-2">${receita.descricao || ''}</p>
                            <div class="flex space-x-2">
                                <button onclick="editarReceita(${receita.id});event.stopPropagation();" 
                                        class="text-indigo-600 hover:text-indigo-800 text-sm font-medium flex items-center">
                                    <i class="fas fa-edit mr-1"></i> Editar
                                </button>
                                <button onclick="excluirReceita(${receita.id});event.stopPropagation();" 
                                        class="text-red-600 hover:text-red-800 text-sm font-medium flex items-center">
                                    <i class="fas fa-trash-alt mr-1"></i> Excluir
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                card.querySelector('.receita-card').addEventListener('click', () => abrirVisualizacaoReceita(receita));
                listaReceitas.appendChild(card);
            });
        }

        function abrirVisualizacaoReceita(receita) {
            document.getElementById('visualizar-titulo').textContent = receita.titulo;
            document.getElementById('visualizar-imagem').src = receita.imagem || 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60';
            document.getElementById('visualizar-categoria').textContent = formatCategory(receita.categoria);
            document.getElementById('visualizar-descricao').textContent = receita.descricao || '';
            document.getElementById('qtd-porcoes').value = 1;
            atualizarIngredientes(receita, 1);
            document.getElementById('qtd-porcoes').oninput = function () {
                atualizarIngredientes(receita, this.value);
            };
            openModal('modal-visualizar-receita');
        }

        function atualizarIngredientes(receita, porcoes) {
            const ul = document.getElementById('visualizar-ingredientes');
            ul.innerHTML = '';
            if (!receita.ingredientes) return;
            const linhas = receita.ingredientes.split('\n');
            linhas.forEach(linha => {
                if (!linha.trim()) return;
                const partes = linha.split(':');
                if (partes.length < 3) return;
                const nome = partes[0];
                const qtd = parseFloat(partes[1]) * porcoes;
                const unidade = partes[2];
                const li = document.createElement('li');
                li.textContent = `${nome}: ${qtd} ${unidade}`;
                ul.appendChild(li);
            });
        }

        function editarReceita(id) {
            const receita = receitasCache.find(r => r.id == id);
            if (receita) {
                document.getElementById('titulo').value = receita.titulo;
                document.getElementById('categoria').value = receita.categoria;
                document.getElementById('descricao').value = receita.descricao || '';
                document.getElementById('ingredientes').value = receita.ingredientes || '';
                receitaEditandoId = id;
                document.getElementById('modal-title').textContent = 'Editar Receita';
                document.getElementById('receita_id').value = id;
                openModal('modal-receita');
            }
        }

        function excluirReceita(id) {
            if (confirm('Tem certeza que deseja excluir esta receita?')) {
                window.location.href = `receitas.php?excluir=${id}`;
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Highlight current page in sidebar
            document.querySelectorAll('.sidebar .nav-item').forEach(function(link) {
                link.classList.remove('bg-indigo-800');
            });
            document.querySelector('.sidebar .nav-item[href="receitas.php"]').classList.add('bg-indigo-800');
        });
    </script>
</body>

</html>