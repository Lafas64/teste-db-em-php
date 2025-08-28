<?php
header('Content-Type: text/html; charset=utf-8');

require_once 'conexao.php'; // Substitua pelo nome correto do seu arquivo de conexão

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

$sql = "SELECT * FROM clientes WHERE razao_social LIKE :q ORDER BY razao_social ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['q' => '%' . $q . '%']);
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($clientes) === 0): ?>
    <p>Nenhum cliente encontrado.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Razão Social</th>
                <th>CNPJ</th>
                <th>CEP</th>
                <th>Endereço</th>
                <th>Anexo</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($clientes as $cliente): ?>
            <tr>
                <td><?= htmlspecialchars($cliente['id']) ?></td>
                <td><?= htmlspecialchars($cliente['razao_social']) ?></td>
                <td><?= htmlspecialchars($cliente['cnpj']) ?></td>
                <td><?= htmlspecialchars($cliente['cep']) ?></td>
                <td><?= htmlspecialchars($cliente['endereco']) ?></td>
                <td>
                    <?php if (!empty($cliente['caminho_anexo'])): ?>
                        <a href="<?= htmlspecialchars($cliente['caminho_anexo']) ?>" target="_blank">Download</a>
                    <?php else: ?>
                        Sem anexo
                    <?php endif; ?>
                </td>
                <td>
                    <a href="editar.php?id=<?= $cliente['id'] ?>">Alterar</a> |
                    <a href="deletar.php?id=<?= $cliente['id'] ?>"
                       onclick="return confirm('Tem certeza que deseja deletar este cliente?');"
                       style="color:red;">Deletar</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>