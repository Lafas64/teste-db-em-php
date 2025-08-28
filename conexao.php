<?php
$host = 'localhost';
$dbname = 'clientes_db';
$usuario = 'root';
$senha = ''; // Altere aqui se sua senha do MySQL for diferente

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $usuario, $senha);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
?>