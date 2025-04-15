<?php

// Mecanismo de login
include('verifica_login.php');

//Verificação de IP (usado para inserção do IP no LOG)
$ip = $_SERVER['HTTP_X_REAL_IP'];
//$ipaddress = "172.16.0.10";
$ipaddress = strstr($ip, ',', true);

// Processa a requisição de delete após a confirmação
if (isset($_POST["id_lista"]) && !empty($_POST["id_lista"])) {
    // Inclui config file
    require_once "config.php";

    // Primeiro buscamos o ramal para registrar no log antes de apagar
    $id_lista = trim($_POST["id_lista"]);
    $ramal = "";
    $sql_ramal = "SELECT ramal FROM lista WHERE id_lista = ?";
    if ($stmt_ramal = mysqli_prepare($link, $sql_ramal)) {
        mysqli_stmt_bind_param($stmt_ramal, "i", $id_lista);
        if (mysqli_stmt_execute($stmt_ramal)) {
            $result = mysqli_stmt_get_result($stmt_ramal);
            if ($row = mysqli_fetch_assoc($result)) {
                $ramal = $row['ramal'];
            }
        }
        mysqli_stmt_close($stmt_ramal);
    }

    // Prepara o statement de delete
    $sql = "DELETE FROM lista WHERE id_lista = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id_lista);

        // Tenta executar os parametros configurados
        if (mysqli_stmt_execute($stmt)) {

            // REGISTRA O LOG DE EXCLUSÃO
            $acao = "Exclusão";
            $usuario = $_SESSION['usuario'];
            $datahora = date('Y-m-d H:i:s');
            $sql_log = "INSERT INTO log_alteracoes (acao, id_lista, ramal, usuario, ip, datahora) VALUES (?, ?, ?, ?, ?, ?)";
            if ($stmt_log = mysqli_prepare($link, $sql_log)) {
                mysqli_stmt_bind_param($stmt_log, "sissss", $acao, $id_lista, $ramal, $usuario, $ipaddress, $datahora);
                mysqli_stmt_execute($stmt_log);
                mysqli_stmt_close($stmt_log);
            }

            // Redireciona para o index após exclusão
            header("location: index.php");
            exit();
        } else {
            echo "Oops! Algo saiu errado. Tente novamente mais tarde.";
        }
    }

    // Fecha o statement
    mysqli_stmt_close($stmt);

    // Fecha a conexão
    mysqli_close($link);
} else {
    // Checa a existência de id de parâmetros
    if (empty(trim($_GET["id_lista"]))) {
        // URL não possui parâmetro, redireciona para a página de erro
        header("location: error.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br" class="dark" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <title>Apagar registro</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <style>
        .wrapper {
            width: 800px;
            margin: 0 auto;
        }

        body {
            background-color: #1C1C1C;
            color: white;
        }

        section {
            width: 150vh;
            margin: auto;
            padding: 10px;
        }

        #userTable th,
        #userTable td {
            border: 1px solid #ccc;
            text-align: center;
        }

        #userTable thead {
            background: #4F4F4F;
        }

        .headcontainer {
            width: auto;
            height: auto;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        body {
            margin: 0px;
        }

        .h2 {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="mt-5 mb-3">Apagar registro</h2>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="alert alert-danger">
                            <input type="hidden" name="id_lista" value="<?php echo trim($_GET["id_lista"]); ?>" />
                            <p>Tem certeza que deseja apagar este registro da lista?</p>
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