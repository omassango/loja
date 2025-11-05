<?php
// =========================================
// functions.php - Funções auxiliares
// =========================================

include_once 'config/db.php';
include_once 'auth.php';
include_once 'functions.php';

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

// =============================
// FUNÇÕES DE GESTÃO DE PRODUTOS
// =============================

// Adicionar produto
function addProduct($data) {
    global $conn;
    $stmt = $conn->prepare("
        INSERT INTO products 
        (name, description, sku, cost_price, sale_price, category, unit, min_stock, current_stock, location, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param(
        "sssddssii s",
        $data['name'],
        $data['description'],
        $data['sku'],
        $data['cost_price'],
        $data['sale_price'],
        $data['category'],
        $data['unit'],
        $data['min_stock'],
        $data['current_stock'],
        $data['location']
    );
    return $stmt->execute();
}

// Atualizar produto
function updateProduct($id, $data) {
    global $conn;
    $stmt = $conn->prepare("
        UPDATE products
        SET name=?, description=?, sku=?, cost_price=?, sale_price=?, category=?, unit=?, min_stock=?, current_stock=?, location=?
        WHERE id=?
    ");
    $stmt->bind_param(
        "sssddssii si",
        $data['name'],
        $data['description'],
        $data['sku'],
        $data['cost_price'],
        $data['sale_price'],
        $data['category'],
        $data['unit'],
        $data['min_stock'],
        $data['current_stock'],
        $data['location'],
        $id
    );
    return $stmt->execute();
}

// Eliminar produto
function deleteProduct($id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// Buscar todos os produtos
function getProducts() {
    global $conn;
    $result = $conn->query("SELECT * FROM products ORDER BY id DESC");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// --- FUNÇÕES DE PRODUTOS ---

function getAllProducts() {
    global $conn;
    $sql = "SELECT * FROM products ORDER BY id DESC";
    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function createProduct($name, $category, $quantity, $price, $description) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO products (name, category, quantity, price, description) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssids", $name, $category, $quantity, $price, $description);
    return $stmt->execute();
}

function getProductById($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result && $result->num_rows > 0 ? $result->fetch_assoc() : null;
}
?>
