<?php

//Mecanismo de login
//session_start();
include('verifica_login.php');

// Inclui Arquivo de Configuração
require_once "config.php";

// Definir variáveis e inicializar com valores vazios
$nome = $ramal = $email = $setor = $secretaria = "";
$nome_err = $ramal_err = $email_err = $setor_err = $secretaria_err = "";

// Processamento de dados do formulário quando o formulário é enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Valida nome
    $input_nome = trim($_POST["nome"]);
    if (empty($input_nome)) {
        $nome_err = "Por favor entre um nome";
    } elseif (!filter_var($input_nome, FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => "/^[a-zA-Zà-úÀ-Ú\s]+$/")))) {
        $nome_err = "Por favor entre um nome válido.";
    } else {
        $nome = $input_nome;
    }

    // Valida ramal
    $input_ramal = trim($_POST["ramal"]);
    if (empty($input_ramal)) {
        $ramal_err = "Por favor entre um ramal.";
    } elseif (!filter_var($input_ramal, FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => "/^([0-9]|-|\s)+$/")))) {
        $ramal_err = "Por favor entre ramal válido.";
    } else {
        $ramal = $input_ramal;
    }

    // Valida e-mail
    $input_email = trim($_POST["e-mail"]);
    if (empty($input_email)) {
        $email = "-";
    } elseif (!filter_var($input_email, FILTER_VALIDATE_EMAIL)) {
        $email_err = "Por favor entre e-mail válido.";
    } else {
        $email = $input_email;
    }

    // Valida setor
    $input_setor = trim($_POST["setor"]);
    if (empty($input_setor)) {
        $setor_err = "Por favor entre um setor.";
    } else {
        $setor = $input_setor;
    }

    // Valida secretaria
    $input_secretaria = trim($_POST["secretaria"]);
    if (empty($input_secretaria)) {
        $secretaria_err = "Por favor entre uma secretaria.";
    } else {
        $secretaria = $input_secretaria;
    }

    // Verifica os erros de entrada antes de inserir no banco de dados
    if (empty($nome_err) && empty($ramal_err) && empty($email_err) && empty($setor_err) && empty($secretaria_err)) {
        // Prepara uma instrução de inserção
        $sql = "INSERT INTO lista (nome, ramal, email, setor, secretaria) VALUES (?, ?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($link, $sql)) {
            // Vincula as variáveis à instrução preparada como parâmetros
            mysqli_stmt_bind_param($stmt, "sssss", $param_nome, $param_ramal, $param_email, $param_setor, $param_secretaria);

            // Configura parametros
            $param_nome = $nome;
            $param_ramal = $ramal;
            $param_email = $email;
            $param_setor = $setor;
            $param_secretaria = $secretaria;

            // Tentativa de executar a instrução preparada
            if (mysqli_stmt_execute($stmt)) {
                // Registros criados com sucesso. Redirecionar para a página de destino
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
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Criar Ramal</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
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
                    <h2 class="mt-5">Criar registro</h2>
                    <p>Por favor preencha dos campos para adicionar um novo ramal a lista</p>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="form-group">
                            <label>Nome</label>
                            <input type="text" name="nome"
                                class="form-control <?php echo (!empty($nome_err)) ? 'is-invalid' : ''; ?>"
                                value="<?php echo $nome; ?>">
                            <span class="invalid-feedback">
                                <?php echo $nome_err; ?>
                            </span>
                        </div>
                        <div class="form-group">
                            <label>Ramal</label>
                            <input type="text" name="ramal"
                                class="form-control <?php echo (!empty($ramal_err)) ? 'is-invalid' : ''; ?>"
                                value="<?php echo $ramal; ?>">
                            <span class="invalid-feedback">
                                <?php echo $ramal_err; ?>
                            </span>
                        </div>
                        <div class="form-group">
                            <label>E-mail</label>
                            <input type="text" name="e-mail"
                                class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>"
                                value="<?php echo $email; ?>">
                            <span class="invalid-feedback">
                                <?php echo $email_err; ?>
                            </span>
                        </div>
                        <div class="form-group">
                            <label>Setor</label>
                            <input type="text" name="setor"
                                class="form-control <?php echo (!empty($setor_err)) ? 'is-invalid' : ''; ?>"
                                value="<?php echo $setor; ?>">
                            <span class="invalid-feedback">
                                <?php echo $setor_err; ?>
                            </span>
                        </div>
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
