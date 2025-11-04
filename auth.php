<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verifica se o utilizador está autenticado.
 * @return bool
 */
function isAuthenticated(): bool
{
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Exige autenticação.
 * Redireciona o utilizador para index.php se não estiver logado.
 */
function requireAuth(): void
{
    if (!isAuthenticated()) {
        header("Location: index.php");
        exit();
    }
}
?>
