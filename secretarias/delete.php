<?php

//Mecanismo de login
//session_start();
include('verifica_login.php');

// Processa a operação de exclusão após a confirmação
if (isset($_POST["id_secretaria"]) && !empty($_POST["id_secretaria"])) {
    // Inclui arquivo de configuração
    require_once "../config.php";

    // Prepara uma declaração de exclusão
    $sql = "DELETE FROM secretarias WHERE id_secretaria = ?";

    if ($stmt = mysqli_prepare($link, $sql)) {
        // Vincula as variáveis à instrução preparada como parâmetros
        mysqli_stmt_bind_param($stmt, "i", $param_id);

        // Definir parâmetros
        $param_id = trim($_POST["id_secretaria"]);

        // Tentativa de executar a instrução preparada
        if (mysqli_stmt_execute($stmt)) {
            // Registros deletados com sucesso. Redirecionar para a página de destino
            header("location: index.php");
            exit();
        } else {
            echo "Oops! Algo saiu errado, tente novamente.";
        }
    }

    // Fecha declaração
    mysqli_stmt_close($stmt);

    // Fecha conexão
    mysqli_close($link);
} else {
    // Verifica a existência do parâmetro id
    if (empty(trim($_GET["id_secretaria"]))) {
        // URL não contém o parâmetro id. Redirecionar para a página de erro
        header("location: error.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Apagar registro</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <style>
        .wrapper {
            width: 800px;
            margin: 0 auto;
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
                            <input type="hidden" name="id_secretaria" value="<?php echo trim($_GET["id_secretaria"]); ?>" />
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