<?php
// =========================================
// functions.php - Funções auxiliares
// =========================================

include_once 'config/db.php';

/**
 * Faz o login do utilizador.
 * @param string $username
 * @param string $password
 * @return array|false Retorna dados do utilizador se sucesso, ou false se falhar.
 */
function loginUser(string $username, string $password)
{
    global $conn;

    // Prepara e executa a consulta de forma segura (evita SQL Injection)
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verifica a senha (em produção deve ser hashed com password_hash)
        if ($password === $user['password'] || password_verify($password, $user['password'])) {
            return $user;
        }
    }

    return false;
}

/**
 * Obtém dados de um utilizador específico.
 * @param int $user_id
 * @return array|null
 */
function getUserById(int $user_id): ?array
{
    global $conn;

    $stmt = $conn->prepare("SELECT id, username, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result && $result->num_rows > 0 ? $result->fetch_assoc() : null;
}
function getTodaySalesAmount(): float {
    global $conn;
    $today = date('Y-m-d');

    // 👉 Tenta primeiro com as colunas mais comuns
    $possible_columns = ['created_at', 'sale_date', 'date', 'data_venda'];
    $column_found = null;

    // Verifica qual coluna existe na tabela "sales"
    $result = $conn->query("SHOW COLUMNS FROM sales");
    $columns = array_column($result->fetch_all(MYSQLI_ASSOC), 'Field');
    foreach ($possible_columns as $col) {
        if (in_array($col, $columns)) {
            $column_found = $col;
            break;
        }
    }

    // Se nenhuma coluna de data for encontrada, retorna 0
    if (!$column_found) {
        return 0;
    }

    // Monta a query usando a coluna correta
    $sql = "SELECT SUM(total_amount) AS total FROM sales WHERE DATE($column_found) = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();

    return (float) ($row['total'] ?? 0);
}
function getAllUsers() {
    global $conn;
    $result = $conn->query("SELECT * FROM users ORDER BY id DESC");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function createUser($username, $password, $role) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO users (username, password, role, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $username, $password, $role);
    $stmt->execute();
    $stmt->close();
}

function updateUser($id, $username, $password, $role) {
    global $conn;
    if ($password) {
        $stmt = $conn->prepare("UPDATE users SET username=?, password=?, role=? WHERE id=?");
        $stmt->bind_param("sssi", $username, $password, $role, $id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET username=?, role=? WHERE id=?");
        $stmt->bind_param("ssi", $username, $role, $id);
    }
    $stmt->execute();
    $stmt->close();
}

function deleteUser($id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

?>
