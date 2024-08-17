<?php

//Mecanismo de login
//session_start();
include('verifica_login.php');

// Inclui arquivo de configuração
require_once "../config.php";

// Definir variáveis e inicializar com valores vazios
$secretaria = "";
$secretaria_err = "";

// Processamento de dados do formulário quando o formulário é enviado
if (isset($_POST["id_secretaria"]) && !empty($_POST["id_secretaria"])) {
    // Get hidden input value
    $id_secretaria = $_POST["id_secretaria"];

    // Valida o secretaria
    $input_secretaria = trim($_POST["secretaria"]);
    if (empty($input_secretaria)) {
        $secretaria_err = "Por favor entre uma Secretaria.";
    } else {
        $secretaria = $input_secretaria;
    }

    // Verifica os erros de entrada antes de inserir no banco de dados
    if (empty($secretaria_err)) {
        // Prepara uma instrução de atualização
        $sql = "UPDATE secretarias SET secretaria=? WHERE id_secretaria=?";

        if ($stmt = mysqli_prepare($link, $sql)) {
            // Vincula as variáveis à instrução preparada como parâmetros
            mysqli_stmt_bind_param($stmt, "si", $param_secretaria, $param_id_secretaria);

            // Definir parâmetros
            $param_secretaria = $secretaria;
            $param_id_secretaria = $id_secretaria;

            // Tentativa de executar a instrução preparada
            if (mysqli_stmt_execute($stmt)) {
                // Registros atualizados com sucesso. Redirecionar para a página de destino
                header("location: index.php");
                exit();
            } else {
                echo "Oops! Algo saiu errado. Tente novamente mais tarde.";
            }
        }

        // Fecha declaração
        mysqli_stmt_close($stmt);
    }

    // Fecha conexão
    mysqli_close($link);
} else {
    // Verifique a existência do parâmetro id antes de processar mais
    if (isset($_GET["id_secretaria"]) && !empty(trim($_GET["id_secretaria"]))) {
        // Obter parâmetro de URL
        $id_secretaria = trim($_GET["id_secretaria"]);

        // Prepara uma declaração de seleção
        $sql = "SELECT * FROM secretarias WHERE id_secretaria = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            // Vincula as variáveis à instrução preparada como parâmetros
            mysqli_stmt_bind_param($stmt, "i", $param_id_secretaria);

            // Definir parâmetros
            $param_id_secretaria = $id_secretaria;

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
    <title>Atualizar Secretaria</title>
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
                    <h2 class="mt-5">Atualizar Secretaria</h2>
                    <p>Por favor utilize o formulário para atualizar a secretaria.</p>
                    <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
                        <div class="form-group">
                            <label>Secretaria</label>
                            <input type="text" name="secretaria"
                                class="form-control <?php echo (!empty($secretaria_err)) ? 'is-invalid' : ''; ?>"
                                value="<?php echo $secretaria; ?>">
                            <span class="invalid-feedback">
                                <?php echo $secretaria_err; ?>
                            </span>
                        </div>
                        <input type="hidden" name="id_secretaria" value="<?php echo $id_secretaria; ?>" />
                        <input type="submit" class="btn btn-primary" value="Salvar">
                        <a href="index.php" class="btn btn-secondary ml-2">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>