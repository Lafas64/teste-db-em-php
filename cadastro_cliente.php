<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Cadastro de Cliente</title>
    <link rel="stylesheet" href="css/style.css" />
    <style>

        .logo {
            height: 80px;
            max-height: 100%;
            margin: 0;
        }

        .menu-toggle {
            background-color: transparent;
            border: none;
            padding: 0;
            cursor: pointer;
            margin-right: 20px;
        }

        .menu-toggle img.menu-icon {
            height: 40px;
            width: auto;
            display: block;
            transition: filter 0.3s ease;
        }

        .menu-toggle:hover img.menu-icon {
            filter: brightness(0.8);
        }
        .menu-toggle:hover {
            background-color: #5936a2;
        }


        .info {
            max-width: 600px;
            margin: 140px auto 20px;
            background-color: #f3e8ff;
            border: 2px solid #a262a8;
            border-radius: 8px;
            padding: 15px;
            color: #4b2a7a;
            display: none;
        }

        form {
            width: 400px;
            margin: 20px auto;
            text-align: left;
            padding-top: 120px;
        }

        label {
            display: block;
            margin-top: 15px;
        }

        input[type="text"],
        input[type="file"] {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }

        button[type="submit"] {
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #a262a8;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        #mensagem {
            margin-top: 15px;
            font-weight: bold;
            text-align: center;
        }

    </style>
</head>

<body>
    <header>
        <a href="#">
            <img src="#" alt="Logo da Empresa" class="logo" />
        </a>
        <button class="menu-toggle" onclick="toggleInfo()" aria-label="Mais informações">
            <img src="menu.jpg" alt="Mais informações" class="menu-icon" />
        </button>
        <nav class="dropdown-menu" id="dropdownMenu">
            <ul>
                <li><a href="#">Indústria / Varejo

                </a></li>
                <li><a href="#">Promotores

                </a></li>
                <li><a href="#">Quem somos

                </a></li>
                <li><a href="#">? Ajuda

                </a></li>
            </ul>
        </nav>
    </header>

    <div class="info" id="infoBox">
        <p>Aqui você pode colocar as informações extras que quiser mostrar ou esconder.</p>
    </div>

    <main class="container">
        <form id="formCadastro" action="salvar_cliente.php" method="POST" enctype="multipart/form-data">
            <h2>Se Cadastre aqui!</h2>

            <label for="razao_social">Razão Social:</label>
            <input type="text" name="razao_social" id="razao_social" required autocomplete="off" />

            <label for="cnpj">CNPJ:</label>
            <input type="text" name="cnpj" id="cnpj" required autocomplete="off" />

            <label for="cep">CEP:</label>
            <input type="text" name="cep" id="cep" required autocomplete="off" />

            <label for="endereco">Endereço:</label>
            <input type="text" name="endereco" id="endereco" required autocomplete="off" />

            <label for="anexo">Anexo de Situação Cadastral (PDF):</label>
            <input type="file" name="anexo" id="anexo" accept="application/pdf" required />

            <button type="submit">Cadastrar Cliente</button>
        </form>

        <div id="mensagem"></div>
    </main>

    <script>
        function toggleInfo() {
            const infoBox = document.getElementById("infoBox");
            const dropdown = document.getElementById("dropdownMenu");

            // Toggle info box (se ainda quiser manter)
            if (infoBox.style.display === "none" || infoBox.style.display === "") {
                infoBox.style.display = "block";
            } else {
                infoBox.style.display = "none";
            }

            // Toggle dropdown menu
            dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
        }

        // Opcional: fecha o menu se clicar fora dele
        window.addEventListener("click", function(e) {
            const menuButton = document.querySelector(".menu-toggle");
            const dropdown = document.getElementById("dropdownMenu");

            if (!menuButton.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = "none";
            }
        });
        // Preenche o endereço automaticamente com base no CEP
        document.getElementById('cep').addEventListener('blur', function() {
            const cep = this.value.replace(/\D/g, '');

            if (cep.length === 8) {
                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(response => response.json())
                .then(data => {
                    if (!data.erro) {
                        document.getElementById('endereco').value = `${data.logradouro}, ${data.bairro}, ${data.localidade} - ${data.uf}`;
                    } else {
                        alert('CEP não encontrado!');
                        document.getElementById('endereco').value = '';
                    }
                })
                .catch(() => {
                    alert('Erro ao consultar o CEP.');
                    document.getElementById('endereco').value = '';
                });
            } else {
                alert('Formato de CEP inválido!');
                document.getElementById('endereco').value = '';
            }
        });

        // Mostra nome do arquivo selecionado
        document.getElementById('anexo').addEventListener('change', function() {
            if (this.files.length > 0) {
                const nome = this.files[0].name;
                alert("Arquivo selecionado: " + nome);
            }
        });

        // Envia o formulário com validações
        document.getElementById('formCadastro').addEventListener('submit', function(event) {
            event.preventDefault(); // Impede o envio tradicional do form

            const form = event.target;
            const mensagemDiv = document.getElementById('mensagem');
            const arquivo = form.anexo.files[0];

            // Validação do tipo de arquivo
            if (arquivo && arquivo.type !== 'application/pdf') {
                mensagemDiv.style.color = 'red';
                mensagemDiv.textContent = 'Por favor, envie um arquivo PDF válido.';
                return;
            }

            // Validação do tamanho do arquivo (máx. 5MB)
            if (arquivo && arquivo.size > 5 * 1024 * 1024) {
                mensagemDiv.style.color = 'red';
                mensagemDiv.textContent = 'O arquivo deve ter no máximo 5MB.';
                return;
            }

            // Validação do nome do arquivo (sem caracteres especiais)
            const nomeArquivo = arquivo.name;
            const regexNomeValido = /^[a-zA-Z0-9_\-\.]+\.(pdf|PDF)$/;
            if (!regexNomeValido.test(nomeArquivo)) {
                mensagemDiv.style.color = 'red';
                mensagemDiv.textContent = 'O nome do arquivo contém caracteres inválidos. Use apenas letras, números, hífen, underline e ponto.';
                return;
            }

            const formData = new FormData(form);
            mensagemDiv.style.color = 'black';
            mensagemDiv.textContent = 'Carregando...';

            fetch(form.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mensagemDiv.style.color = 'green';
                    mensagemDiv.textContent = data.message;
                    form.reset();

                    if (data.novo) {
                        // Redireciona para salvar_cliente.php (ou outra página) após 1,5s
                        setTimeout(() => {
                            window.location.href = 'salvar_cliente.php';
                        }, 1500);
                    }
                } else {
                    mensagemDiv.style.color = 'red';
                    mensagemDiv.textContent = data.message;
                }
            });
        });
        
    </script>
</body>
</html>
