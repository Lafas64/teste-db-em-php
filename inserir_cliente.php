<?php
require 'conexao.php';

// Verifica se o formulário foi enviado via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitiza os dados
    $razao_social = trim($_POST['razao_social']);
    $cnpj = trim($_POST['cnpj']);
    $cep = trim($_POST['cep']);
    $endereco = trim($_POST['endereco']);

    // Lida com o upload do anexo (PDF)
    if (isset($_FILES['anexo']) && $_FILES['anexo']['error'] === UPLOAD_ERR_OK) {
        $nomeTemporario = $_FILES['anexo']['tmp_name'];
        $nomeArquivo = basename($_FILES['anexo']['name']);
        $caminhoDestino = 'uploads/' . $nomeArquivo;

        // Cria a pasta 'uploads' se não existir
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }

        // Move o arquivo para o destino final
        if (move_uploaded_file($nomeTemporario, $caminhoDestino)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO clientes (razao_social, cnpj, cep, endereco, caminho_anexo)
                                       VALUES (:razao_social, :cnpj, :cep, :endereco, :caminho_anexo)");

                $stmt->execute([
                    ':razao_social' => $razao_social,
                    ':cnpj' => $cnpj,
                    ':cep' => $cep,
                    ':endereco' => $endereco,
                    ':caminho_anexo' => $caminhoDestino
                ]);

                // Redireciona após o sucesso
                header("Location: salvar_cliente.php");
                exit;
            } catch (PDOException $e) {
                die("Erro ao inserir no banco: " . $e->getMessage());
            }
        } else {
            die("Erro ao mover o arquivo.");
        }
    } else {
        die("Erro no upload do arquivo.");
    }
} else {
    die("Requisição inválida.");
}
?>