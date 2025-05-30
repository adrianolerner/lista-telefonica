<?php

//Mecanismo de login
//session_start();
include('verifica_login.php');

// Inclui arquivo de configuração
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

    // Definir variáveis e inicializar com valores vazios
    $banner = "";
    $banner_err = "";

    //Carrega os dados do banner
    if ($stmtBanner = mysqli_prepare($link, "SELECT banner FROM banner WHERE id_banner = ?")) {
        $id_banner = 1;
        mysqli_stmt_bind_param($stmtBanner, "i", $id_banner);
        mysqli_stmt_execute($stmtBanner);
        mysqli_stmt_bind_result($stmtBanner, $banner);
        mysqli_stmt_fetch($stmtBanner);
        mysqli_stmt_close($stmtBanner);
    }

    $bannerarray = ['banner' => $banner];

    // Processamento de dados do formulário quando o formulário é enviado
    if (isset($_POST["id_banner"]) && !empty($_POST["id_banner"])) {
        // Obtém o valor de entrada oculto
        $id_banner = $_POST["id_banner"];

        // Valida banner
        $input_banner = trim($_POST["banner"]);
        if (empty($input_banner)) {
            $banner_err = "Por favor entre um banner.";
        } else {
            $banner = $input_banner;
        }

        // Verifica os erros de entrada antes de inserir no banco de dados
        if (empty($banner_err)) {
            // Prepara uma instrução de atualização
            $sql = "UPDATE banner SET banner=? WHERE id_banner=?";

            if ($stmt = mysqli_prepare($link, $sql)) {
                // Vincula as variáveis à instrução preparada como parâmetros
                mysqli_stmt_bind_param($stmt, "si", $param_banner, $param_id);

                // Configura Parâmetros
                $param_banner = $banner;
                $param_id = $id_banner;

                // Tentativa de executar a instrução preparada
                if (mysqli_stmt_execute($stmt)) {
                    // Registros atualizados com sucesso. Redirecionar para a página de destino
                    header("location: index.php");
                    exit();
                } else {
                    echo "Oops! Algo saiu errado. Tente novamente mais tarde.";
                }
            }

            // Fechar declaração
            mysqli_stmt_close($stmt);
        }

        // Fechar conexão
        mysqli_close($link);
    } else {
        // Verifique a existência do parâmetro id antes de processar mais
        if (isset($_GET["id_banner"]) && !empty(trim($_GET["id_banner"]))) {
            // Obter parâmetro de URL
            $id_banner = trim($_GET["id_banner"]);

            // Prepara uma declaração de seleção
            $sql = "SELECT * FROM banner WHERE id_banner = ?";
            if ($stmt = mysqli_prepare($link, $sql)) {
                // Vincula as variáveis à instrução preparada como parâmetros
                mysqli_stmt_bind_param($stmt, "i", $param_id);

                // Definir parâmetros
                $param_id = $id_banner;

                // Tentativa de executar a instrução preparada
                if (mysqli_stmt_execute($stmt)) {
                    $result = mysqli_stmt_get_result($stmt);

                    if (mysqli_num_rows($result) == 1) {
                        /* Busca a linha do resultado como um array associativo. Como o conjunto de resultados
                         contém apenas uma linha */
                        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

                        // Recupera o valor do campo individual
                        $banner = $row["banner"];
                    } else {
                        // URL não contém id válido. Redirecionar para a página de erro
                        header("location: error.php");
                        exit();
                    }

                } else {
                    echo "Oops! Algo saiu errado. Tente novamente mais tarde.";
                }
            }

            // Fecha declaração
            mysqli_stmt_close($stmt);

            // Fechar Conexão
            mysqli_close($link);
        } else {
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
        <title>Atualizar Registro</title>
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
                        <h2 class="mt-5">Atualizar Banner</h2>
                        <p>Por favor edite as informações para atualizar o Banner.</p>
                        <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
                            <div class="form-group">
                                <label>Banner</label>
                                <textarea name="banner"
                                    class="form-control <?php echo (!empty($banner_err)) ? 'is-invalid' : ''; ?>"
                                    value="<?php echo $banner; ?>" rows="5"
                                    cols="33"> <?php echo htmlspecialchars($bannerarray["banner"]); ?> </textarea>
                                <span class="invalid-feedback">
                                    <?php echo $banner_err; ?>
                                </span>
                            </div>
                            <input type="hidden" name="id_banner" value="<?php echo $id_banner; ?>" />
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