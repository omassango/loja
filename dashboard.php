<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
requireAuth();

// Garantir availability de funções utilitárias (não faz mal chamar include_once aqui)
include_once __DIR__ . '/functions.php';

// Dados do utilizador na sessão
$current_user_id   = $_SESSION['user_id']   ?? null;
$current_username  = $_SESSION['username']  ?? 'Usuário';
$current_role      = $_SESSION['role']      ?? '—';

// Exemplos de métricas simples (podes substituir por queries reais em functions.php)
$total_products    = function_exists('countProducts') ? countProducts() : null;
$total_sales       = function_exists('countSales') ? countSales() : null;
$today_sales_amt   = function_exists('getTodaySalesAmount') ? getTodaySalesAmount() : null;
?>

<div class="p-4 bg-white rounded-lg shadow-sm mb-4">
    <h1 class="h3 mb-2"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h1>
    <p class="text-muted">Bem-vindo de volta, <strong><?php echo htmlspecialchars($current_username); ?></strong>! (<?php echo htmlspecialchars($current_role); ?>)</p>
</div>

<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">ID do Utilizador</h6>
                <p class="card-text fs-5"><?php echo htmlspecialchars((string)$current_user_id); ?></p>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Função</h6>
                <p class="card-text fs-5"><?php echo htmlspecialchars($current_role); ?></p>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Produtos (total)</h6>
                <p class="card-text fs-5">
                    <?php echo is_null($total_products) ? '—' : number_format((int)$total_products); ?>
                </p>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Vendas (hoje)</h6>
                <p class="card-text fs-5">
                    <?php
                    if (is_null($today_sales_amt)) {
                        echo '—';
                    } else {
                        echo 'MZN ' . number_format((float)$today_sales_amt, 2, ',', '.');
                    }
                    ?>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                Atalhos rápidos
            </div>
            <div class="card-body">
                <a href="index.php?page=products" class="btn btn-outline-primary me-2"><i class="fas fa-boxes me-1"></i> Produtos</a>
                <a href="index.php?page=sales" class="btn btn-outline-success me-2"><i class="fas fa-shopping-cart me-1"></i> Vendas</a>
                <a href="index.php?page=inventory" class="btn btn-outline-warning me-2"><i class="fas fa-archive me-1"></i> Stock</a>
                <a href="index.php?action=logout" class="btn btn-outline-danger"><i class="fas fa-sign-out-alt me-1"></i> Sair</a>
            </div>
        </div>
    </div>
</div>

<!-- Pequeno rodapé com info de sessão -->
<div class="text-muted small">
    Sessão iniciada como <strong><?php echo htmlspecialchars($current_role); ?></strong>.
</div>
</main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>