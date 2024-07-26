<?php

//Mecanismo de login
//session_start();
include('verifica_login.php');

// Inclui arquivo de configuração
require_once "config.php";

// Definir variáveis e inicializar com valores vazios
$banner = "";
$banner_err = "";

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
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Atualizar Registro</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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
                    <h2 class="mt-5">Atualizar Banner</h2>
                    <p>Por favor edite as informações para atualizar o Banner.</p>
                    <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
                        <div class="form-group">
                            <label>Banner</label>
                            <textarea name="banner"
                                class="form-control <?php echo (!empty($banner_err)) ? 'is-invalid' : ''; ?>"
                                value="<?php echo $banner; ?>" rows="5" cols="33"> Digite o novo Banner aqui!!! </textarea>
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