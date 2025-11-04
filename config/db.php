<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "loja";

$conn = mysqli_connect($host, $user, $pass, $dbname);

// Verifica a conexão
if (!$conn) {
    die("Erro na conexão com o banco de dados: " . mysqli_connect_error());
}
?>
