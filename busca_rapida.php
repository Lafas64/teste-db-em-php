<?php
require 'conexao.php';  // conexão PDO

// Recebe e sanitiza o termo de busca
$q = isset($_GET['q']) ? strip_tags(trim($_GET['q'])) : '';

if ($_SERVER['REQUEST_METHOD'] !== 'GET' || $q === '') {
    http_response_code(400);
    echo json_encode([]);
    exit;
}

try {
    // Consulta buscando pelo termo no nome, cnpj, cep e endereço
    $sql = "SELECT id, razao_social
            FROM clientes
            WHERE razao_social LIKE :q
               OR cnpj LIKE :q
               OR cep LIKE :q
               OR endereco LIKE :q
            ORDER BY id DESC
            LIMIT 20";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':q' => "%$q%"]);
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Define o tipo da resposta como JSON
    header('Content-Type: application/json; charset=utf-8');

    // Retorna o array como JSON (pode estar vazio se nada achar)
    echo json_encode($clientes);

    

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro na busca: ' . $e->getMessage()]);
}
