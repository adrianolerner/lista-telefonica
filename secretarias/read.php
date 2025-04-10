<?php

//Mecanismo de login
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

    // Verifique a existência do parâmetro id_secretaria antes de processar mais
    if (isset($_GET["id_secretaria"]) && !empty(trim($_GET["id_secretaria"]))) {

        // Prepara uma declaração de seleção
        $sql = "SELECT * FROM secretarias WHERE id_secretaria = ?";

        if ($stmt = mysqli_prepare($link, $sql)) {
            // Vincula as variáveis à instrução preparada como parâmetros
            mysqli_stmt_bind_param($stmt, "i", $param_id);

            // Definir parâmetros
            $param_id = trim($_GET["id_secretaria"]);

            // Tentativa de executar a instrução preparada
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);

                if (mysqli_num_rows($result) == 1) {
                    /* Busca a linha do resultado como um array associativo. Como o conjunto de resultados
                     contém apenas uma linha */
                    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

                    // Recupera o valor do campo individual
                    $secretaria = $row["secretaria"];
                } else {
                    // URL não contém parâmetro id_secretaria válido. Redirecionar para a página de erro
                    header("location: error.php");
                    exit();
                }

            } else {
                echo "Oops! Algo saiu errado, tente novamente.";
            }
        }

        // Fecha declaração
        mysqli_stmt_close($stmt);

        // Fecha conexão
        mysqli_close($link);
    } else {
        // URL não contém o parâmetro id_secretaria. Redirecionar para a página de erro
        header("location: error.php");
        exit();
    }
    ?>

    <!DOCTYPE html>
    <html lang="pt-br" class="dark" data-bs-theme="dark">

    <head>
        <meta charset="UTF-8">
        <title>Ver registro</title>
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
                        <h1 class="mt-5 mb-3">Ver Registro</h1>
                        <div class="form-group">
                            <label>Secretaria</label>
                            <p><b>
                                    <?php echo $row["secretaria"]; ?>
                                </b></p>
                        </div>
                        <p><a href="index.php" class="btn btn-primary">Voltar</a></p>
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