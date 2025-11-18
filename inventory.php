<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
requireAuth();
include 'functions.php';


$message = '';
$message_type = '';

// Processa o formulário de movimentação de estoque
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_movement') {
    $movement_data = [
        'product_id' => intval($_POST['product_id'] ?? 0),
        'quantity' => intval($_POST['quantity'] ?? 0),
        'type' => $_POST['movement_type'] ?? '',
        'details' => $_POST['details'] ?? ''
    ];

    if (addMovement($movement_data)) {
        $message = 'Movimentação registrada e estoque atualizado com sucesso!';
        $message_type = 'success';
    } else {
        // A função addMovement já loga o erro, aqui apenas exibe uma mensagem genérica ou específica
        // dependendo da necessidade de segurança
        $message = 'Erro ao registrar movimentação ou estoque insuficiente. Verifique o console para mais detalhes.';
        // Para uma mensagem mais específica para o usuário, você pode modificar addMovement para retornar o erro.
        $message_type = 'danger';
    }
}

$products = getProducts(); // Obter todos os produtos para o dropdown
$movements = getMovements(); // Obter histórico de movimentações
?>

<div class="p-4 bg-white rounded-lg shadow-sm mb-4">
    <h1 class="h3 mb-4 text-gray-800"><i class="fas fa-exchange-alt me-2"></i>Movimentação de Estoque</h1>
    <?php if (!empty($message)) : ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Registrar Nova Movimentação</h6>
        </div>
        <div class="card-body">
            <form action="index.php?page=inventory" method="POST">
                <input type="hidden" name="action" value="add_movement">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="product_id" class="form-label">Produto</label>
                        <select class="form-select" id="product_id" name="product_id" required>
                            <option value="">Selecione um produto</option>
                            <?php foreach ($products as $product) : ?>
                                <option value="<?php echo htmlspecialchars($product['id']); ?>">
                                    <?php echo htmlspecialchars($product['name']); ?> (Estoque: <?php echo htmlspecialchars($product['current_stock']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="quantity" class="form-label">Quantidade</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Tipo de Movimentação</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="movement_type" id="entrada" value="entrada" checked>
                            <label class="form-check-label" for="entrada">Entrada</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="movement_type" id="saida" value="saida">
                            <label class="form-check-label" for="saida">Saída</label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <label for="details" class="form-label">Detalhes (Opcional)</label>
                        <input type="text" class="form-control" id="details" name="details" placeholder="Ex: Compra do fornecedor X, Devolução de cliente">
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-success">Registrar Movimentação</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Histórico de Movimentações</h6>
        </div>
        <div class="card-body">
            <?php if (empty($movements)) : ?>
                <div class="alert alert-info text-center" role="alert">
                    Nenhuma movimentação registrada ainda.
                </div>
            <?php else : ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Produto</th>
                                <th>Tipo</th>
                                <th>Quantidade</th>
                                <th>Estoque Após</th>
                                <th>Detalhes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($movements as $movement) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(date('d/m/Y H:i:s', strtotime($movement['movement_date']))); ?></td>
                                    <td><?php echo htmlspecialchars($movement['product_name_at_move']); ?></td>
                                    <td>
                                        <span class="badge <?php echo ($movement['type'] === 'entrada' ? 'bg-success' : 'bg-danger'); ?>">
                                            <?php echo ($movement['type'] === 'entrada' ? 'Entrada' : 'Saída'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($movement['quantity']); ?></td>
                                    <td><?php echo htmlspecialchars($movement['current_stock_after']); ?></td>
                                    <td><?php echo htmlspecialchars($movement['details']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
