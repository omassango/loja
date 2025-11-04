<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

requireAuth(); // garante que só logados entrem

if ($_SESSION['role'] !== 'Admin') {
    echo "<div class='alert alert-danger m-4'>Acesso negado! Apenas administradores podem gerir utilizadores.</div>";
    exit();
}

include_once __DIR__ . '/functions.php';

// Operações CRUD simples
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']); // senha simples
        $role     = $_POST['role'];
        createUser($username, $password, $role);
    }

    if ($action === 'update') {
        $id       = (int) $_POST['id'];
        $username = trim($_POST['username']);
        $role     = $_POST['role'];
        $password = !empty($_POST['password']) ? trim($_POST['password']) : null; // sem hash
        updateUser($id, $username, $password, $role);
    }
}

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    deleteUser($id);
}

$users = getAllUsers();
?>

<div class="p-4 bg-white rounded shadow-sm">
    <h3><i class="fas fa-users me-2"></i>Gestão de Utilizadores</h3>
    <hr>

    <!-- Formulário de criação -->
    <form method="POST" class="row g-3 mb-4">
        <input type="hidden" name="action" value="create">
        <div class="col-md-3">
            <input type="text" name="username" class="form-control" placeholder="Nome de utilizador" required>
        </div>
        <div class="col-md-3">
            <input type="text" name="password" class="form-control" placeholder="Senha" required>
        </div>
        <div class="col-md-3">
            <select name="role" class="form-select" required>
                <option value="">Função</option>
                <option value="Admin">Admin</option>
                <option value="User">User</option>
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus-circle me-1"></i> Adicionar</button>
        </div>
    </form>

    <!-- Lista de utilizadores -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Nome de Utilizador</th>
                    <th>Função</th>
                    <th>Criado em</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($users) > 0): ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?php echo $u['id']; ?></td>
                            <td><?php echo htmlspecialchars($u['username']); ?></td>
                            <td><?php echo htmlspecialchars($u['role']); ?></td>
                            <td><?php echo htmlspecialchars($u['created_at']); ?></td>
                            <td>
                                <!-- Editar -->
                                <button class="btn btn-sm btn-warning" 
                                    onclick="editUser(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars($u['username']); ?>', '<?php echo $u['role']; ?>')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <!-- Excluir -->
                                <a href="index.php?page=users&delete=<?php echo $u['id']; ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Tens certeza que queres eliminar este utilizador?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center text-muted">Nenhum utilizador encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal de edição -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" id="editId">
        <div class="modal-header">
            <h5 class="modal-title">Editar Utilizador</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Nome de Utilizador</label>
                <input type="text" name="username" id="editUsername" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Nova Senha (opcional)</label>
                <input type="text" name="password" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Função</label>
                <select name="role" id="editRole" class="form-select" required>
                    <option value="Admin">Admin</option>
                    <option value="User">User</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-success">Guardar Alterações</button>
        </div>
    </form>
  </div>
</div>

<script>
function editUser(id, username, role) {
    document.getElementById('editId').value = id;
    document.getElementById('editUsername').value = username;
    document.getElementById('editRole').value = role;
    var modal = new bootstrap.Modal(document.getElementById('editUserModal'));
    modal.show();
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>