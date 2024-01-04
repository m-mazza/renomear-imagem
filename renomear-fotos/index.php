<!DOCTYPE html>
<html>
<head>
    <title>Formulário de Upload e Replicação de Imagens</title>
    <!-- Incluindo Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
    <body>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-7">
                <h4 class="text-center text-uppercase mb-5">Formulário de Upload e Replicação de Imagens</h4>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="imagem">Enviar Imagem (JPG, PNG, GIF):</label>
                        <input type="file" id="imagem" name="imagem" class="form-control-file">
                    </div>
                    <div class="form-group mt-5">
                        <label for="numeros">Insira os números separados por vírgula:</label>
                        <textarea id="numeros" name="numeros" rows="10" cols="50" class="form-control" style="resize:none"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Replicar Imagem</button>
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-secondary">Limpar</a>
                </form>

                <div class="mt-4">
                    <?php
                        function gerarBotaoDownload($caminho_arquivo, $nome_arquivo) {
                            return "<a href='$caminho_arquivo' download='$nome_arquivo' class='btn btn-primary mt-3'>Baixar Arquivo Zipado</a>";
                        }
                    ?>
                </div>
                <?php
                    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['imagem']) && isset($_POST['numeros'])) {
                        $diretorio_geradas = 'geradas/';

                        if (!file_exists($diretorio_geradas)) {
                            mkdir($diretorio_geradas, 0777, true);
                        }

                        $imagem_nome = $_FILES['imagem']['name'];
                        $imagem_temp = $_FILES['imagem']['tmp_name'];
                        $caminho_imagem = $diretorio_geradas . $imagem_nome;

                        if (move_uploaded_file($imagem_temp, $caminho_imagem)) {
                            echo "<div class='alert alert-success'>Imagem enviada com sucesso: $imagem_nome </div>";

                            $numeros = explode(",", $_POST["numeros"]);
                            $numeros = array_map('trim', $numeros);

                            $arquivos_criados = 0;

                            foreach ($numeros as $numero) {
                                $novo_nome = $diretorio_geradas . $numero . '.jpg';

                                if (copy($caminho_imagem, $novo_nome)) {
                                    $arquivos_criados++;
                                }
                            }

                            if ($arquivos_criados > 0) {
                                
                                echo " <div class='alert alert-success'>$arquivos_criados arquivos criados com sucesso.</div>";

                                $arquivos_replicados = glob($diretorio_geradas . '*.jpg');
                                $nome_zip = 'arquivos_replicados.zip';
                                $zip = new ZipArchive();

                                if ($zip->open($nome_zip, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                                    foreach ($arquivos_replicados as $arquivo) {
                                        $zip->addFile($arquivo, basename($arquivo));
                                    }
                                    $zip->close();

                                    echo gerarBotaoDownload($nome_zip, $nome_zip);
                                    
                                    // Adicionando o botão "Excluir Arquivo Zipado" com JavaScript
                                    echo "<button id='excluirArquivo' class='btn btn-danger mt-3'>Excluir Arquivo Zipado</button>";

                                    foreach ($arquivos_replicados as $arquivo) {
                                        unlink($arquivo);
                                    }
                                } else {
                                    echo "<div class='alert alert-danger'>Falha ao criar o arquivo zip.</div>";
                                }
                            } else {

                                echo "<div class='alert alert-warning'>Nenhum arquivo foi criado.</div> ";
                            }
                        } else {
                            echo "<div class='alert alert-danger'>Erro ao fazer o upload da imagem.</div>";
                        }
                    }

                    if ($_SERVER["REQUEST_METHOD"] == "POST") {
                  
                        if (isset($_POST['limpar'])) {
                            header("Location: {$_SERVER['PHP_SELF']}");
                            exit;
                        }
                    }
                        // Lógica para exclusão do arquivo zip
                        if (isset($_GET['excluir_zip'])) {
                            $nome_zip = 'arquivos_replicados.zip';
                            if (file_exists($nome_zip)) {
                                unlink($nome_zip);
                                echo "<script>alert('Arquivo excluído com sucesso.');</script>";
                            }
                        }
                    ?>
       
            </div>
        </div>

    </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                document.querySelector('button[name="limpar"]').addEventListener('click', function(e) {
                    e.preventDefault();
                    location.reload();
                });
            });

            // Função para excluir o arquivo zip
            document.getElementById('excluirArquivo').addEventListener('click', function() {
                var xhr = new XMLHttpRequest();
                xhr.open('GET', '<?php echo $_SERVER["PHP_SELF"]; ?>?excluir_zip=true', true);
                xhr.onload = function () {
                    if (xhr.status === 200) {
                        alert('Arquivo excluído com sucesso.');
                    } else {
                        alert('Ocorreu um erro ao excluir o arquivo.');
                    }
                };
                xhr.send();
            });
        </script>
    </body>
</html>
