<?php

//Mecanismo de login
include('../verifica_login.php');

// Include config file
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

    // Definir variáveis e inicializar com valores vazios
    $secretaria = "";
    $secretaria_err = "";

    // Processamento de dados do formulário quando o formulário é enviado
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Valida secretaria
        $input_secretaria = trim($_POST["secretaria"]);
        if (empty($input_secretaria)) {
            $secretaria_err = "Por favor entre uma secretaria";
        } else {
            $secretaria = $input_secretaria;
        }

        // Verifica os erros de entrada antes de inserir no banco de dados
        if (empty($secretaria_err)) {
            // Prepara uma instrução de inserção
            $sql = "INSERT INTO secretarias (secretaria) VALUES (?)";

            if ($stmt = mysqli_prepare($link, $sql)) {
                // Vincula as variáveis à instrução preparada como parâmetros
                mysqli_stmt_bind_param($stmt, "s", $param_secretaria);

                // Definir parâmetros
                $param_secretaria = $secretaria;

                $sql_duplicate = "SELECT * FROM secretarias WHERE secretaria = '{$secretaria}'";
                $duplicate_result = mysqli_query($link, $sql_duplicate);
                $check_duplicate = mysqli_num_rows($duplicate_result);

                if ($check_duplicate) {
                    header("location: error.php");
                    exit();
                } else {
                    // Tentativa de executar a instrução preparada
                    if (mysqli_stmt_execute($stmt)) {
                        // Registros criados com sucesso. Redirecionar para a página de destino
                        header("location: index.php");
                        exit();
                    } else {
                        echo "Oops! Algo saiu errado. Tente novamente mais tarde.";
                    }
                }
            }

            // Fecha declaração
            mysqli_stmt_close($stmt);
        }

        // Fecha conexão
        mysqli_close($link);
    }
    ?>

    <!DOCTYPE html>
    <html lang="pt-br" class="dark" data-bs-theme="dark">

    <head>
        <meta charset="UTF-8">
        <title>Criar secretaria</title>
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
                        <h2 class="mt-5">Criar Secretaria</h2>
                        <p>Por favor preencha o campo para adicionar uma nova secretaria a lista</p>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="form-group">
                                <label>Secretaria</label>
                                <input type="text" name="secretaria"
                                    class="form-control <?php echo (!empty($secretaria_err)) ? 'is-invalid' : ''; ?>"
                                    value="<?php echo $secretaria; ?>">
                                <span class="invalid-feedback">
                                    <?php echo $secretaria_err; ?>
                                </span>
                            </div>
                            <input type="submit" class="btn btn-primary" value="Salvar">
                            <a href="index.php" class="btn btn-secondary ml-2">Cancelar</a>
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