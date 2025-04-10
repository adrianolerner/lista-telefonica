<?php

//Mecanismo de login
//session_start();
include('../verifica_login.php');
// Inclui arquivo de configuração
require_once "../config.php";
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


    // Processa a operação de exclusão após a confirmação
    if (isset($_POST["id"]) && !empty($_POST["id"])) {

        // Prepara uma declaração de exclusão
        $sql = "DELETE FROM usuarios WHERE id = ?";

        if ($stmt = mysqli_prepare($link, $sql)) {
            // Vincula as variáveis à instrução preparada como parâmetros
            mysqli_stmt_bind_param($stmt, "i", $param_id);

            // Definir parâmetros
            $param_id = trim($_POST["id"]);

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
        if (empty(trim($_GET["id"]))) {
            // URL não contém o parâmetro id. Redirecionar para a página de erro
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
        <link rel="stylesheet" href="../css/bootstrap.min.css">
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
                                <input type="hidden" name="id" value="<?php echo trim($_GET["id"]); ?>" />
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
<?php } else {
    header("Location: /lista/index.php");
    exit;
}
?>