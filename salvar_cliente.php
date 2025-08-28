<?php
require 'conexao.php'; // conecta com o banco

// Função para sanitizar entrada de texto
function sanitize($str) {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

// Parte 1: Inserção dos dados se for método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $razao_social = trim($_POST['razao_social'] ?? '');
    $cnpj = trim($_POST['cnpj'] ?? '');
    $cep = trim($_POST['cep'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');

    // Verifica se o arquivo foi enviado sem erros
    if (isset($_FILES['anexo']) && $_FILES['anexo']['error'] === UPLOAD_ERR_OK) {
        $nomeTemporario = $_FILES['anexo']['tmp_name'];
        $nomeArquivo = basename($_FILES['anexo']['name']);
        $caminhoDestino = 'uploads/' . uniqid() . '_' . $nomeArquivo;

        // Cria a pasta uploads se não existir
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }

        // Move o arquivo para a pasta uploads
        if (move_uploaded_file($nomeTemporario, $caminhoDestino)) {
            try {
                // Verifica se já existe um cliente com o mesmo razao_social e cnpj
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM clientes WHERE razao_social = :razao_social AND cnpj = :cnpj");
                $stmt->execute([
                    ':razao_social' => $razao_social,
                    ':cnpj' => $cnpj
                ]);
                $existe = $stmt->fetchColumn();

                if ($existe) {
                    echo "<p style='color: red;'>Cliente já cadastrado com essa razão social e CNPJ.</p>";
                } else {
                    // Insere no banco
                    $stmt = $pdo->prepare("INSERT INTO clientes (razao_social, cnpj, cep, endereco, caminho_anexo) 
                                           VALUES (:razao_social, :cnpj, :cep, :endereco, :caminho_anexo)");

                    $stmt->execute([
                        ':razao_social' => $razao_social,
                        ':cnpj' => $cnpj,
                        ':cep' => $cep,
                        ':endereco' => $endereco,
                        ':caminho_anexo' => $caminhoDestino
                    ]);

                    // Redireciona após sucesso
                    header('Location: salvar_cliente.php');
                    exit;
                }

            } catch (PDOException $e) {
                die("Erro ao inserir cliente: " . $e->getMessage());
            }
        } else {
            die("Erro ao mover o arquivo enviado.");
        }
    } else {
        die("Erro no upload do arquivo ou arquivo não enviado.");
    }
}

// Parte 2: Consulta dos dados para exibição

// Pega os filtros via GET e sanitiza
$razao_social = isset($_GET['razao_social']) ? sanitize($_GET['razao_social']) : '';
$cnpj = isset($_GET['cnpj']) ? sanitize($_GET['cnpj']) : '';
$cep = isset($_GET['cep']) ? sanitize($_GET['cep']) : '';
$endereco = isset($_GET['endereco']) ? sanitize($_GET['endereco']) : '';

// Monta consulta dinâmica
$sql = "SELECT id, razao_social, cnpj, cep, endereco, caminho_anexo FROM clientes WHERE 1=1";
$params = [];

if ($razao_social !== '') {
    $sql .= " AND razao_social LIKE :razao_social";
    $params[':razao_social'] = '%' . $razao_social . '%';
}
if ($cnpj !== '') {
    $sql .= " AND cnpj LIKE :cnpj";
    $params[':cnpj'] = '%' . $cnpj . '%';
}
if ($cep !== '') {
    $sql .= " AND cep LIKE :cep";
    $params[':cep'] = '%' . $cep . '%';
}
if ($endereco !== '') {
    $sql .= " AND endereco LIKE :endereco";
    $params[':endereco'] = '%' . $endereco . '%';
}

$sql .= " ORDER BY id DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao buscar clientes: " . $e->getMessage());
}
?>

<!-- HTML e JS -->
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Consulta de Clientes</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<header>Consulta de Clientes</header>

<h2>Pesquisar Clientes</h2>

<form method="get" action="" id="formPesquisa" autocomplete="on">
    <label for="razao_social">Nome Cliente:</label>
    <input
        type="text"
        name="razao_social"
        id="razao_social"
        value="<?= htmlspecialchars($razao_social) ?>"
        autocomplete="off"
        aria-autocomplete="list"
        aria-haspopup="listbox"
        aria-expanded="false"
        aria-controls="sugestoes"
        aria-activedescendant=""
    >
    <div id="sugestoes" role="listbox" aria-label="Sugestões de clientes"></div>

    <div class="botao">
        <button type="submit">Pesquisar</button>
        <a href="<?= $_SERVER['PHP_SELF'] ?>" class="botao-limpar">Limpar Filtros</a>
    </div>
</form>

<h2>Resultados</h2>

<?php if (count($clientes) === 0): ?>
    <p>Nenhum cliente encontrado.</p>
<?php else: ?>
    <table id="tabela-clientes">
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
                        <a href="download.php?id=<?= $cliente['id'] ?>" target="_blank">Download</a>
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

<!-- JS -->
<script>
    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    const inputPesquisa = document.getElementById('razao_social');
    const sugestoes = document.getElementById('sugestoes');
    let currentFocus = -1;

    function setActive(items) {
        items.forEach((item, i) => {
            item.classList.remove('active');
            item.setAttribute('aria-selected', 'false');
        });

        if (currentFocus > -1 && currentFocus < items.length) {
            const activeItem = items[currentFocus];
            activeItem.classList.add('active');
            activeItem.setAttribute('aria-selected', 'true');
            inputPesquisa.setAttribute('aria-activedescendant', activeItem.id);

            const containerTop = sugestoes.scrollTop;
            const containerBottom = containerTop + sugestoes.offsetHeight;
            const itemTop = activeItem.offsetTop;
            const itemBottom = itemTop + activeItem.offsetHeight;

            if (itemBottom > containerBottom) {
                sugestoes.scrollTop += itemBottom - containerBottom;
            } else if (itemTop < containerTop) {
                sugestoes.scrollTop -= containerTop - itemTop;
            }
        } else {
            inputPesquisa.removeAttribute('aria-activedescendant');
        }
    }

    inputPesquisa.addEventListener('input', debounce(function () {
        const termo = this.value.trim();

        if (termo.length < 2) {
            sugestoes.style.display = 'none';
            sugestoes.innerHTML = '';
            inputPesquisa.setAttribute('aria-expanded', 'false');
            currentFocus = -1;
            return;
        }

        fetch('busca_rapida.php?razao_social=' + encodeURIComponent(termo))
            .then(response => response.json())
            .then(data => {
                if (!Array.isArray(data) || data.length === 0) {
                    sugestoes.innerHTML = '<div class="sugestao-item" tabindex="-1">Nenhuma sugestão encontrada</div>';
                    sugestoes.style.display = 'block';
                    inputPesquisa.setAttribute('aria-expanded', 'true');
                    currentFocus = -1;
                    return;
                }

                sugestoes.innerHTML = data.map((item, index) =>
                    `<div id="sugestao-${index}" class="sugestao-item" role="option" tabindex="-1">${item.razao_social}</div>`
                ).join('');

                sugestoes.style.display = 'block';
                inputPesquisa.setAttribute('aria-expanded', 'true');
                currentFocus = -1;
            })
            .catch(() => {
                sugestoes.style.display = 'none';
                sugestoes.innerHTML = '';
                inputPesquisa.setAttribute('aria-expanded', 'false');
                currentFocus = -1;
            });
    }, 300));

    sugestoes.addEventListener('click', (e) => {
        if (e.target.classList.contains('sugestao-item')) {
            inputPesquisa.value = e.target.textContent;
            sugestoes.innerHTML = '';
            sugestoes.style.display = 'none';
            inputPesquisa.setAttribute('aria-expanded', 'false');
            currentFocus = -1;
            document.getElementById('formPesquisa').submit();
        }
    });

    inputPesquisa.addEventListener('keydown', (e) => {
        const items = sugestoes.querySelectorAll('.sugestao-item');
        if (!items.length) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            currentFocus++;
            if (currentFocus >= items.length) currentFocus = 0;
            setActive(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            currentFocus--;
            if (currentFocus < 0) currentFocus = items.length - 1;
            setActive(items);
        } else if (e.key === 'Enter') {
            if (currentFocus > -1) {
                e.preventDefault();
                items[currentFocus].click();
            }
        } else if (e.key === 'Escape') {
            sugestoes.style.display = 'none';
            inputPesquisa.setAttribute('aria-expanded', 'false');
            currentFocus = -1;
        }
    });

    // Permite seleção com espaço ou enter nas sugestões (acessibilidade extra)
    sugestoes.addEventListener('keydown', (e) => {
        if (e.target.classList.contains('sugestao-item') && (e.key === 'Enter' || e.key === ' ')) {
            e.preventDefault();
            inputPesquisa.value = e.target.textContent;
            sugestoes.style.display = 'none';
            inputPesquisa.setAttribute('aria-expanded', 'false');
            currentFocus = -1;
            document.getElementById('formPesquisa').submit();
        }
    });

    // Fecha sugestões ao clicar fora
    document.addEventListener('click', (event) => {
        if (!inputPesquisa.contains(event.target) && !sugestoes.contains(event.target)) {
            sugestoes.style.display = 'none';
            inputPesquisa.setAttribute('aria-expanded', 'false');
            currentFocus = -1;
        }
        const tabela = document.getElementById('tabela-clientes');
if (tabela) {
  const linhas = tabela.tBodies[0].rows;

  inputPesquisa.addEventListener('input', function() {
    const filtro = this.value.toLowerCase();

    for (let i = 0; i < linhas.length; i++) {
      const linha = linhas[i];
      // Pegando os textos das colunas Razão Social, CNPJ, CEP e Endereço
      const textoRazao = linha.cells[1].textContent.toLowerCase();
      const textoCnpj = linha.cells[2].textContent.toLowerCase();
      const textoCep = linha.cells[3].textContent.toLowerCase();
      const textoEndereco = linha.cells[4].textContent.toLowerCase();

      const textoConcat = textoRazao + ' ' + textoCnpj + ' ' + textoCep + ' ' + textoEndereco;

      if (textoConcat.includes(filtro)) {
        linha.style.display = '';
      } else {
        linha.style.display = 'none';
      }
    }
  });
}
    });
</script>
