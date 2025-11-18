<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/auth.php';
requireAuth();

include_once __DIR__ . '/functions.php';

// Mensagens de feedback
$message = '';
$message_type = ''; // success ou danger

// --- Adicionar ou Editar Produto ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $name        = trim($_POST['name'] ?? '');
    $category    = trim($_POST['category'] ?? '');
    $quantity    = (int)($_POST['quantity'] ?? 0);
    $price       = (float)($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $id          = (int)($_POST['id'] ?? 0);

    if ($_POST['action'] === 'create') {
        if (createProduct($name, $category, $quantity, $price, $description)) {
            $message = "Produto adicionado com sucesso!";
            $message_type = "success";
        } else {
            $message = "Erro ao adicionar produto.";
            $message_type = "danger";
        }
    } elseif ($_POST['action'] === 'update') {
        if (updateProduct($id, $name, $category, $quantity, $price, $description)) {
            $message = "Produto atualizado com sucesso!";
            $message_type = "success";
        } else {
            $message = "Erro ao atualizar produto.";
            $message_type = "danger";
        }
    }
}

// --- Apagar Produto ---
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if (deleteProduct($id)) {
        $message = "Produto eliminado com sucesso!";
        $message_type = "success";
    } else {
        $message = "Erro ao eliminar produto.";
        $message_type = "danger";
    }
}

$products = getAllProducts();
?>

<div class="p-4 bg-white rounded-lg shadow-sm mb-4">
    <h1 class="h3 mb-3"><i class="fas fa-boxes me-2"></i>Gestão de Produtos</h1>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Botão para abrir modal -->
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#productModal" onclick="clearProductForm()">
        <i class="fas fa-plus-circle me-1"></i> Adicionar Produto
    </button>

    <!-- Tabela de produtos -->
    <div class="table-responsive">
        <?php if (empty($products)): ?>
            <div class="alert alert-info text-center">Nenhum produto registado.</div>
        <?php else: ?>
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Categoria</th>
                        <th>Qtd</th>
                        <th>Preço (MZN)</th>
                        <th>Descrição</th>
                        <th>Criado em</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td><?php echo $p['id']; ?></td>
                            <td><?php echo htmlspecialchars($p['name']); ?></td>
                            <td><?php echo htmlspecialchars($p['category']); ?></td>
                            <td><?php echo $p['quantity']; ?></td>
                            <td><?php echo number_format($p['price'], 2, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($p['description']); ?></td>
                            <td><?php echo htmlspecialchars($p['created_at']); ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning text-white me-1" 
                                    onclick='editProduct(<?php echo json_encode($p); ?>)'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="index.php?page=products&delete=<?php echo $p['id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Deseja eliminar este produto?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Adicionar/Editar -->
<div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
        <input type="hidden" name="id" id="productId">
        <input type="hidden" name="action" id="formAction" value="create">

        <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">Adicionar Produto</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Nome</label>
                <input type="text" name="name" id="productName" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Categoria</label>
                <input type="text" name="category" id="productCategory" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Quantidade</label>
                <input type="number" name="quantity" id="productQuantity" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Preço (MZN)</label>
                <input type="number" name="price" id="productPrice" class="form-control" step="0.01" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Descrição</label>
                <textarea name="description" id="productDescription" class="form-control"></textarea>
            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Gravar</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
    </form>
  </div>
</div>

<script>
function clearProductForm() {
    document.getElementById('formAction').value = 'create';
    document.querySelector('.modal-title').innerText = 'Adicionar Produto';
    document.getElementById('productId').value = '';
    document.getElementById('productName').value = '';
    document.getElementById('productCategory').value = '';
    document.getElementById('productQuantity').value = '';
    document.getElementById('productPrice').value = '';
    document.getElementById('productDescription').value = '';
}

function editProduct(prod) {
    document.getElementById('formAction').value = 'update';
    document.querySelector('.modal-title').innerText = 'Editar Produto';
    document.getElementById('productId').value = prod.id;
    document.getElementById('productName').value = prod.name;
    document.getElementById('productCategory').value = prod.category;
    document.getElementById('productQuantity').value = prod.quantity;
    document.getElementById('productPrice').value = prod.price;
    document.getElementById('productDescription').value = prod.description;

    new bootstrap.Modal(document.getElementById('productModal')).show();
}
</script>
