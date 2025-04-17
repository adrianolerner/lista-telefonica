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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['arquivo_csv'])) {
    $arquivo_tmp = $_FILES['arquivo_csv']['tmp_name'];
    $handle = fopen($arquivo_tmp, 'r');

    if ($handle) {
        $linha_teste = fgets($handle); // Lê a primeira linha como texto bruto

        if (strpos($linha_teste, ";") !== false && strpos($linha_teste, ",") === false) {
            // Parece estar usando ; como delimitador, mostra alerta e encerra
            $mensagem = "Erro: O arquivo CSV está usando ponto e vírgula (;) como delimitador. Por favor, substitua por vírgula (,) conforme o modelo.";
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

            $mensagem = "Importação concluída: $inseridos registros inseridos, $ignorados ignorados.";
        }
    } else {
        $mensagem = "Erro ao ler o arquivo CSV.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br" class="dark" data-bs-theme="dark">
<head>
    <meta charset="UTF-8" />
    <title>Importar CSV - Lista Telefônica</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <style>
        body { background-color: #1C1C1C; color: white; }
        section { width: 60%; margin: auto; padding: 20px; }
        .btn { margin-right: 10px; }
    </style>
</head>
<body>
<section>
    <h2 class="text-center">Importar Lista Telefônica via CSV</h2>
    <div class="mb-3">
        <a href="index.php" class="btn btn-secondary">← Voltar</a>
        <a href="modelo_importacao.csv" class="btn btn-info"><i class="fa fa-sign-in"></i> Baixar Modelo CSV</a>
        <a href="historico_importacoes.php" class="btn btn-primary pull-right"><i class="fa fa-search"></i> Históricos de importação</a>
    </div>
    <div class="mb-3">
        <p>Atenção! Somente serão importados registros que tiverem o campo "Secretaria" iguais aos previamente cadastrados no menu "Gerenciar Secretarias".<br />Registros com este campo diferente dos cadastrados serão ignorados.</p>
    </div>
    <?php if ($mensagem): ?>
        <div class="alert alert-<?php echo (strpos($mensagem, 'Erro') !== false) ? 'danger' : 'info'; ?>">
            <?php echo htmlspecialchars($mensagem); ?>
        </div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group mb-3">
            <label for="arquivo_csv">Selecione o arquivo CSV:</label>
            <input type="file" name="arquivo_csv" id="arquivo_csv" class="form-control" accept=".csv" required>
        </div>
        <button type="submit" class="btn btn-success">Importar</button>
    </form>
</section>
</body>
</html>
<?php } else {
    header("Location: /lista/index.php");
    exit;
}
?>