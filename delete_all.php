<?php

// Mecanismo de login
include('verifica_login.php');
// Inclui arquivo de configuração
require_once "config.php";

//Verificação de IP (usado para inserção do IP no LOG)
$ip = $_SERVER['HTTP_X_REAL_IP'];
//$ipaddress = "172.16.0.10";
$ipaddress = strstr($ip, ',', true);

// Verificação de Admin
$useradmin = @$_SESSION['usuario'];

if ($stmt = mysqli_prepare($link, "SELECT admin FROM usuarios WHERE usuario = ?")) {
    mysqli_stmt_bind_param($stmt, "s", $useradmin);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $admin);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
}

if (!$useradmin) {
    header("Location: login.php");
    exit;
}

if ($admin === "s") {

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["confirm"]) && $_POST["confirm"] === "sim") {
        // Executa exclusão total e reseta o AUTO_INCREMENT
        $sqlDelete = "DELETE FROM lista";
        $sqlResetAI = "ALTER TABLE lista AUTO_INCREMENT = 1";

        if (mysqli_query($link, $sqlDelete)) {
            mysqli_query($link, $sqlResetAI);

            // REGISTRA O LOG DE EXCLUSÃO
            $acao = "Limpeza";
            $ramal = "Todos";
            $id_lista = 0;
            $usuario = $_SESSION['usuario'];
            $datahora = date('Y-m-d H:i:s');
            $sql_log = "INSERT INTO log_alteracoes (acao, id_lista, ramal, usuario, ip, datahora) VALUES (?, ?, ?, ?, ?, ?)";
            if ($stmt_log = mysqli_prepare($link, $sql_log)) {
                mysqli_stmt_bind_param($stmt_log, "sissss", $acao, $id_lista, $ramal, $usuario, $ipaddress, $datahora);
                mysqli_stmt_execute($stmt_log);
                mysqli_stmt_close($stmt_log);
            }

            header("Location: index.php");
            exit;
        } else {
            echo "Erro ao excluir os registros.";
        }

        mysqli_close($link);
    }
    ?>

    <!DOCTYPE html>
    <html lang="pt-br" class="dark" data-bs-theme="dark">

    <head>
        <meta charset="UTF-8">
        <title>Apagar Todos os Registros</title>
        <link rel="stylesheet" href="css/bootstrap.min.css">
        <style>
            .wrapper {
                width: 800px;
                margin: 0 auto;
            }

            body {
                background-color: #1C1C1C;
                color: white;
                margin: 0px;
            }

            section {
                width: 150vh;
                margin: auto;
                padding: 10px;
            }

            .headcontainer {
                display: flex;
                justify-content: center;
                align-items: center;
            }

            .alert-danger {
                font-size: 18px;
                font-weight: bold;
            }

            .btn {
                margin: 5px;
            }
        </style>
    </head>

    <body>
        <div class="wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <h2 class="mt-5 mb-3 text-center">Apagar Todos os Registros</h2>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="alert alert-danger text-center">
                                <p>Atenção, esta ação irá <strong>apagar todos os registros</strong> da lista telefônica e é <strong>irreversível!</strong></p>
                                <p>Tem certeza que deseja continuar?</p>
                                <input type="hidden" name="confirm" value="sim" />
                                <p>
                                    <input type="submit" value="Sim" class="btn btn-danger">
                                    <a href="index.php" class="btn btn-secondary">Não</a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </body>

    </html>

<?php
} else {
    header("Location: index.php");
    exit;
}
?>
