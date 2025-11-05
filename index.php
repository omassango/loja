<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'config/db.php';
include_once 'auth.php';
include_once 'functions.php';


$login_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $user = loginUser($username, $password); // função definida em functions.php

    if ($user) {
        // Define variáveis de sessão
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Redireciona para o dashboard
        header("Location: index.php?page=dashboard");
        exit();
    } else {
        $login_error = "Nome de usuário ou senha inválidos.";
    }
}

// =====================================
// Logout (encerra a sessão)
// =====================================
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();
    header("Location: index.php");
    exit();
}

// =====================================
// Exibe o formulário de login se o utilizador não estiver logado
// =====================================
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true):
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAXIXE BOTTLE STORE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card shadow-lg p-4" style="width: 100%; max-width: 400px; border-radius: 15px;">
            <h2 class="card-title text-center mb-4 text-primary">MAXIXE BOTTLE STORE</h2>
            <?php if (!empty($login_error)) : ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($login_error); ?>
                </div>
            <?php endif; ?>
            <form action="index.php" method="POST">
                <input type="hidden" name="action" value="login">
                <div class="mb-3">
                    <label for="username" class="form-label">Nome de Usuário</label>
                    <input type="text" class="form-control rounded-pill" id="username" name="username" required>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Senha</label>
                    <input type="password" class="form-control rounded-pill" id="password" name="password" required>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill">Entrar</button>
                </div>
               
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
    exit(); // Sai após exibir o formulário de login
endif;

// =====================================
// Utilizador autenticado - carrega a aplicação
// =====================================
$current_user_id = $_SESSION['user_id'];
$current_username = $_SESSION['username'];
$current_role = $_SESSION['role'];

$page = $_GET['page'] ?? 'dashboard'; // Página padrão
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bottle Store - Maxixe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="index.php?page=dashboard">Bottle Store - Maxixe</a>
            <span class="navbar-text text-white-50 ms-3 me-auto d-none d-lg-block">
                Olá, <?php echo htmlspecialchars($current_username); ?>!
            </span>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link <?php echo ($page === 'dashboard' ? 'active' : ''); ?>" href="index.php?page=dashboard"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo ($page === 'products' ? 'active' : ''); ?>" href="index.php?page=products"><i class="fas fa-boxes me-1"></i> Produtos</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo ($page === 'inventory' ? 'active' : ''); ?>" href="index.php?page=inventory"><i class="fas fa-exchange-alt me-1"></i> Stock</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo ($page === 'sales' ? 'active' : ''); ?>" href="index.php?page=sales"><i class="fas fa-shopping-cart me-1"></i> Vendas</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo ($page === 'reports' ? 'active' : ''); ?>" href="index.php?page=reports"><i class="fas fa-chart-line me-1"></i> Relatórios</a></li>

                    <?php if ($current_role === 'Admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($page === 'users' ? 'active' : ''); ?>" href="index.php?page=users">
                                <i class="fas fa-users me-1"></i> Utilizadores
                            </a>
                        </li>
                    <?php endif; ?>


                    <li class="nav-item"><a class="nav-link" href="index.php?action=logout"><i class="fas fa-sign-out-alt me-1"></i> Sair</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <main>
            <?php
            // Controla qual conteúdo será incluído
            switch ($page) {
                case 'dashboard': include __DIR__ . '/dashboard.php'; break;
                case 'products': include __DIR__ . '/products.php'; break;
                case 'inventory': include __DIR__ . '/inventory.php'; break;
                case 'sales': include __DIR__ . '/sales.php'; break;
                case 'reports': include __DIR__ . '/reports.php'; break;
                default: include __DIR__ . '/dashboard.php'; break;
                case 'users': include __DIR__ . '/users.php'; break;
            }
            ?>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

