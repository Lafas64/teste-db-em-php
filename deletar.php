<?php
require 'conexao.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID inválido.");
}

$id = (int) $_GET['id'];

try {
    // Busca caminho do anexo (para deletar depois)
    $stmt = $pdo->prepare("SELECT caminho_anexo FROM clientes WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cliente) {
        die("Cliente não encontrado.");
    }

    // Deleta o registro do banco
    $stmt = $pdo->prepare("DELETE FROM clientes WHERE id = :id");
    $stmt->execute([':id' => $id]);

    // Remove arquivo (se existir)
    if (!empty($cliente['caminho_anexo']) && file_exists($cliente['caminho_anexo'])) {
        unlink($cliente['caminho_anexo']);
    }

    // Redireciona
    header("Location: cadastro.php");
    exit;

} catch (PDOException $e) {
    die("Erro ao deletar cliente: " . $e->getMessage());
}
?>
