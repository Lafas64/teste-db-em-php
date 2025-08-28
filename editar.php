<?php
require 'conexao.php';
require_once __DIR__ . '/vendor/autoload.php';
use Respect\Validation\Validator as v;

// Validação e carregamento
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID inválido.");
}

$id = (int) $_GET['id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cliente) {
        die("Cliente não encontrado.");
    }
} catch (PDOException $e) {
    die("Erro ao buscar cliente: " . $e->getMessage());
}

// Se enviado o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $razao_social = htmlspecialchars(trim($_POST['razao_social']));
    $cnpj = htmlspecialchars(trim($_POST['cnpj']));
    $cep = htmlspecialchars(trim($_POST['cep']));
    $endereco = htmlspecialchars(trim($_POST['endereco']));

    if (!v::cnpj()->validate($cnpj)) {
        echo "<p style='color:red;'>CNPJ inválido.</p>";
    } else {
        try {
            $sql = "UPDATE clientes SET razao_social = :razao_social, cnpj = :cnpj, cep = :cep, endereco = :endereco WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':razao_social' => $razao_social,
                ':cnpj' => $cnpj,
                ':cep' => $cep,
                ':endereco' => $endereco,
                ':id' => $id
            ]);
            echo "<p style='color:green;'>Cliente atualizado com sucesso.</p>";
            // Recarrega cliente atualizado
            $cliente['razao_social'] = $razao_social;
            $cliente['cnpj'] = $cnpj;
            $cliente['cep'] = $cep;
            $cliente['endereco'] = $endereco;
        } catch (PDOException $e) {
            echo "<p style='color:red;'>Erro ao atualizar: " . $e->getMessage() . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Cliente</title>
</head>
<body>
    <h2>Editar Cliente</h2>

    <form method="post">
        <label>Razão Social: <input type="text" name="razao_social" value="<?= htmlspecialchars($cliente['razao_social']) ?>" required></label><br>
        <label>CNPJ: <input type="text" name="cnpj" value="<?= htmlspecialchars($cliente['cnpj']) ?>" required></label><br>
        <label>CEP: <input type="text" name="cep" value="<?= htmlspecialchars($cliente['cep']) ?>"></label><br>
        <label>Endereço: <input type="text" name="endereco" value="<?= htmlspecialchars($cliente['endereco']) ?>"></label><br>
        <button type="submit">Salvar Alterações</button>
        <a href="cadastro.php"><button type="button">Voltar</button></a>
    </form>
</body>
</html>