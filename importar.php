<?php
include('verifica_login.php');
require_once "config.php";

// Verificação de Admin
$useradmin = @$_SESSION['usuario'];

if ($stmt = mysqli_prepare($link, "SELECT admin FROM usuarios WHERE usuario = ?")) {
    mysqli_stmt_bind_param($stmt, "s", $useradmin);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $admin);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
}

$adminarray = ['admin' => $admin];

if (!$useradmin) {
    header("Location: login.php");
    exit;
}

if ($adminarray['admin'] == "s") {

    $mensagem = '';
    $tipo_alerta = ''; // Para definir a cor do alerta (sucesso ou erro)

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['arquivo_csv'])) {
        $arquivo_tmp = $_FILES['arquivo_csv']['tmp_name'];
        $handle = fopen($arquivo_tmp, 'r');

        if ($handle) {
            $linha_teste = fgets($handle); // Lê a primeira linha como texto bruto

            if (strpos($linha_teste, ";") !== false && strpos($linha_teste, ",") === false) {
                // Parece estar usando ; como delimitador, mostra alerta e encerra
                $mensagem = "<strong>Erro de Formato:</strong> O arquivo CSV está usando ponto e vírgula (;) como delimitador. Por favor, substitua por vírgula (,) conforme o modelo.";
                $tipo_alerta = 'danger';
                fclose($handle);
            } else {
                rewind($handle); // Retorna o ponteiro para o início do arquivo
                $inseridos = 0;
                $ignorados = 0;
                $linha = 0;

                while (($dados = fgetcsv($handle, 1000, ",")) !== false) {
                    $linha++;
                    if ($linha === 1) continue; // Ignora cabeçalho

                    // Verifica se a linha tem todos os campos necessários
                    if (count($dados) < 5) {
                        $ignorados++;
                        continue;
                    }

                    list($nome, $setor, $ramal, $email, $secretaria_nome) = $dados;

                    // Tratamento dos campos
                    $nome = trim($nome);
                    $setor = trim($setor);
                    $ramal = trim($ramal);
                    $email = trim($email);
                    $secretaria_nome = trim($secretaria_nome);

                    // Se email estiver vazio, substitui por "-"
                    if (empty($email)) {
                        $email = "-";
                    }

                    // Validação dos campos obrigatórios
                    if (empty($nome) || empty($setor) || empty($ramal) || empty($secretaria_nome)) {
                        $ignorados++;
                        continue;
                    }

                    // Validação do email (deve ser válido ou "-")
                    if ($email !== "-" && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $ignorados++;
                        continue;
                    }

                    // Validação do ramal (deve conter apenas números, hífens ou espaços)
                    if (!preg_match('/^[\d\-\s]+$/', $ramal)) {
                        $ignorados++;
                        continue;
                    }

                    // Verifica se a secretaria existe
                    $stmt = mysqli_prepare($link, "SELECT id_secretaria FROM secretarias WHERE secretaria = ?");
                    mysqli_stmt_bind_param($stmt, "s", $secretaria_nome);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_bind_result($stmt, $id_secretaria);
                    if (!mysqli_stmt_fetch($stmt)) {
                        $ignorados++;
                        mysqli_stmt_close($stmt);
                        continue;
                    }
                    mysqli_stmt_close($stmt);

                    // Verifica se já existe um registro com o mesmo ramal E mesmo nome OU mesmo e-mail
                    $stmt = mysqli_prepare($link, "SELECT id_lista FROM lista WHERE ramal = ? AND nome = ?");
                    mysqli_stmt_bind_param($stmt, "ss", $ramal, $nome);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_store_result($stmt);
                    if (mysqli_stmt_num_rows($stmt) > 0) {
                        $ignorados++;
                        mysqli_stmt_close($stmt);
                        continue;
                    }
                    mysqli_stmt_close($stmt);

                    // Inserção do registro
                    $stmt = mysqli_prepare($link, "INSERT INTO lista (nome, setor, ramal, email, secretaria) VALUES (?, ?, ?, ?, ?)");
                    mysqli_stmt_bind_param($stmt, "ssssi", $nome, $setor, $ramal, $email, $id_secretaria);
                    if (mysqli_stmt_execute($stmt)) {
                        $inseridos++;
                    } else {
                        $ignorados++;
                        // Log de erro para depuração
                        error_log("Erro ao inserir registro (Linha $linha): " . mysqli_error($link));
                    }
                    mysqli_stmt_close($stmt);
                }

                fclose($handle);

                $ipaddress = $_SERVER['REMOTE_ADDR'] ?? 'DESCONHECIDO';
                $stmt = mysqli_prepare($link, "INSERT INTO log_importacoes (usuario, ip, inseridos, ignorados, data_hora) VALUES (?, ?, ?, ?, NOW())");
                mysqli_stmt_bind_param($stmt, "ssii", $useradmin, $ipaddress, $inseridos, $ignorados);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                $mensagem = "Importação concluída: <strong>$inseridos</strong> registros inseridos, <strong>$ignorados</strong> ignorados.";
                $tipo_alerta = 'success';
            }
        } else {
            $mensagem = "Erro ao ler o arquivo CSV.";
            $tipo_alerta = 'danger';
        }
    }
?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="dark">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar CSV - Lista Telefônica</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: var(--bs-body-bg); }
        .navbar-brand img { height: 40px; margin-right: 10px; }
        
        /* Estilo da área de upload */
        .upload-area {
            border: 2px dashed var(--bs-border-color);
            border-radius: 10px;
            background-color: var(--bs-tertiary-bg);
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
        }
        .upload-area:hover {
            border-color: var(--bs-success);
            background-color: rgba(25, 135, 84, 0.05);
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm mb-5">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fa fa-phone-square me-2"></i> LISTA TELEFÔNICA
            </a>
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-light btn-sm" id="themeToggle" title="Alternar Tema">
                    <i class="fa fa-moon"></i>
                </button>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                
                <div class="card shadow border-0">
                    <div class="card-header bg-success bg-opacity-10 border-bottom border-success border-opacity-25 py-3">
                        <div class="d-flex align-items-center">
                            <i class="fa fa-file-import fa-2x text-success me-3"></i>
                            <div>
                                <h4 class="mb-0 fw-bold text-success-emphasis">Importar Registros</h4>
                                <small class="text-muted">Carregue um arquivo CSV para adicionar múltiplos contatos.</small>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        
                        <div class="d-flex flex-wrap gap-2 justify-content-between mb-4">
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="fa fa-arrow-left me-1"></i> Voltar
                            </a>
                            <div class="d-flex gap-2">
                                <a href="modelo_importacao.csv" class="btn btn-outline-info">
                                    <i class="fa fa-download me-1"></i> Baixar Modelo
                                </a>
                                <a href="historico_importacoes.php" class="btn btn-outline-primary">
                                    <i class="fa fa-history me-1"></i> Histórico
                                </a>
                            </div>
                        </div>

                        <?php if ($mensagem): ?>
                            <div class="alert alert-<?php echo $tipo_alerta; ?> alert-dismissible fade show shadow-sm" role="alert">
                                <?php if ($tipo_alerta == 'success'): ?>
                                    <i class="fa fa-check-circle me-2"></i>
                                <?php else: ?>
                                    <i class="fa fa-exclamation-circle me-2"></i>
                                <?php endif; ?>
                                <?php echo $mensagem; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <div class="alert alert-warning d-flex align-items-start shadow-sm border-warning" role="alert">
                            <i class="fa fa-exclamation-triangle fa-lg me-3 mt-1"></i>
                            <div>
                                <h6 class="alert-heading fw-bold">Regras Importantes:</h6>
                                <p class="mb-0 small">
                                    1. O delimitador deve ser <strong>vírgula (,)</strong>.<br>
                                    2. O nome da <strong>Secretaria</strong> no CSV deve ser idêntico ao cadastrado no sistema.<br>
                                    3. Registros com Secretarias não encontradas serão <strong>ignorados</strong>.
                                </p>
                            </div>
                        </div>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="upload-area mb-4">
                                <div class="mb-3">
                                    <i class="fa fa-cloud-upload-alt fa-3x text-muted opacity-50"></i>
                                </div>
                                <label for="arquivo_csv" class="form-label fw-bold">Selecione o arquivo CSV</label>
                                <input type="file" name="arquivo_csv" id="arquivo_csv" class="form-control" accept=".csv" required>
                                <div class="form-text mt-2">Formatos aceitos: .csv (UTF-8 recomendado)</div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fa fa-upload me-2"></i> Iniciar Importação
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script de Dark Mode (Persistente)
        const themeToggle = document.getElementById('themeToggle');
        const htmlElement = document.documentElement;
        const icon = themeToggle.querySelector('i');

        const savedTheme = localStorage.getItem('theme') || 'dark';
        htmlElement.setAttribute('data-bs-theme', savedTheme);
        updateIcon(savedTheme);

        themeToggle.addEventListener('click', () => {
            const currentTheme = htmlElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            htmlElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateIcon(newTheme);
        });

        function updateIcon(theme) {
            if (theme === 'dark') {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            } else {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            }
        }
    </script>
</body>
</html>
<?php 
} else {
    // Redirecionamento caso não seja admin
    header("Location: index.php");
    exit;
}
?>