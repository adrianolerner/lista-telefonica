<?php
include('verifica_login.php');
require_once "config.php";

//Verificação de Admin
$useradmin = @$_SESSION['usuario'];

if ($stmt = mysqli_prepare($link, "SELECT admin FROM usuarios WHERE usuario = ?")) {
    // Bind parameters
    mysqli_stmt_bind_param($stmt, "s", $useradmin);
    
    // Execute statement
    mysqli_stmt_execute($stmt);
    
    // Bind result variables
    mysqli_stmt_bind_result($stmt, $admin);
    
    // Fetch the result
    mysqli_stmt_fetch($stmt);
    
    // Close the statement
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
        $inseridos = 0;
        $ignorados = 0;
        $linha = 0;

        while (($dados = fgetcsv($handle, 1000, ",")) !== false) {
            $linha++;
            if ($linha === 1) continue; // Ignora cabeçalho

            list($nome, $setor, $ramal, $email, $secretaria_nome) = $dados;

            // Validações básicas
            if (empty($nome) || empty($setor) || empty($ramal) || empty($email) || empty($secretaria_nome)) {
                $ignorados++;
                continue;
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !is_numeric($ramal)) {
                $ignorados++;
                continue;
            }

            // Busca o id da secretaria pelo nome
            $stmt = mysqli_prepare($link, "SELECT id_secretaria FROM secretarias WHERE secretaria = ?");
            mysqli_stmt_bind_param($stmt, "s", $secretaria_nome);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $id_secretaria);
            if (!mysqli_stmt_fetch($stmt)) {
                // Secretaria não encontrada
                $ignorados++;
                mysqli_stmt_close($stmt);
                continue;
            }
            mysqli_stmt_close($stmt);

            // Verifica duplicidade por ramal ou e-mail
            $stmt = mysqli_prepare($link, "SELECT id_lista FROM lista WHERE ramal = ? OR email = ?");
            mysqli_stmt_bind_param($stmt, "is", $ramal, $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) > 0) {
                $ignorados++;
                mysqli_stmt_close($stmt);
                continue;
            }
            mysqli_stmt_close($stmt);

            // Insere no banco
            $stmt = mysqli_prepare($link, "INSERT INTO lista (nome, setor, ramal, email, secretaria) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssisi", $nome, $setor, $ramal, $email, $id_secretaria);
            if (mysqli_stmt_execute($stmt)) {
                $inseridos++;
            } else {
                $ignorados++;
            }
            mysqli_stmt_close($stmt);
        }
        fclose($handle);

        // Salva no log
        $stmt = mysqli_prepare($link, "INSERT INTO log_importacoes (usuario, ip, inseridos, ignorados, data_hora) VALUES (?, ?, ?, ?, NOW())");
        mysqli_stmt_bind_param($stmt, "ssii", $useradmin, $ipaddress, $inseridos, $ignorados);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $mensagem = "Importação concluída: $inseridos registros inseridos, $ignorados ignorados.";
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
        <a href="historico_importacoes.php" class="btn btn-primary pull-right"><i class="fa fa-gear"></i> Históricos de importação</a>
    </div>
    <div class="mb-3">
        <p>Atenção! Somente serão importados registros que tiverem o campo "Secretaria" iguais aos previamente cadastrados no menu "Gerenciar Secretarias".<br />Registros com este campo diferente dos cadastrados serão ignorados.</p>
    </div>
    <?php if ($mensagem): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($mensagem); ?></div>
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