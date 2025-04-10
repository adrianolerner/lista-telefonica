<?php

//Mecanismo de login
//session_start();
include('verifica_login.php');

// Inclui arquivo de configuração
require_once "config.php";

// Definir variáveis e inicializar com valores vazios
$nome = $ramal = $email = $setor = $secretaria = "";
$nome_err = $ramal_err = $email_err = $setor_err = $secretaria_err = "";

// Processamento de dados do formulário quando o formulário é enviado
if (isset($_POST["id_lista"]) && !empty($_POST["id_lista"])) {
    // Obtém o valor de entrada oculto
    $id_lista = $_POST["id_lista"];

    // Valida nome
    $input_nome = trim($_POST["nome"]);
    if (empty($input_nome)) {
        $nome_err = "Por favor entre um nome.";
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
    $input_email = trim($_POST["email"]);
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
        // Prepara uma instrução de atualização
        $sql = "UPDATE lista SET nome=?, ramal=?, email=?, setor=?, secretaria=? WHERE id_lista=?";

        if ($stmt = mysqli_prepare($link, $sql)) {
            // Vincula as variáveis à instrução preparada como parâmetros
            mysqli_stmt_bind_param($stmt, "sssssi", $param_nome, $param_ramal, $param_email, $param_setor, $param_secretaria, $param_id);

            // Configura Parâmetros
            $param_nome = $nome;
            $param_ramal = $ramal;
            $param_email = $email;
            $param_setor = $setor;
            $param_secretaria = $secretaria;
            $param_id = $id_lista;

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
        if (empty($nome_err) && empty($ramal_err) && empty($email_err) && empty($setor_err) && empty($secretaria_err)) {
            mysqli_close($link);
        }
} else {
    // Verifique a existência do parâmetro id antes de processar mais
    if (isset($_GET["id_lista"]) && !empty(trim($_GET["id_lista"]))) {
        // Obter parâmetro de URL
        $id_lista = trim($_GET["id_lista"]);

        // Prepara uma declaração de seleção
        $sql = "SELECT * FROM lista WHERE id_lista = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            // Vincula as variáveis à instrução preparada como parâmetros
            mysqli_stmt_bind_param($stmt, "i", $param_id);

            // Definir parâmetros
            $param_id = $id_lista;

            // Tentativa de executar a instrução preparada
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);

                if (mysqli_num_rows($result) == 1) {
                    /* Busca a linha do resultado como um array associativo. Como o conjunto de resultados
                     contém apenas uma linha */
                    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

                    // Recupera o valor do campo individual
                    $nome = $row["nome"];
                    $ramal = $row["ramal"];
                    $email = $row["email"];
                    $setor = $row["setor"];
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

        // Fechar conexão
        if (!empty($nome_err) && !empty($ramal_err) && !empty($email_err) && !empty($setor_err) && !empty($secretaria_err)) {
            mysqli_close($link);
        }
        
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
        #userTable th, #userTable td {
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
                    <h2 class="mt-5">Atualizar Registro</h2>
                    <p>Por favor edite as informações para atualizar o registro.</p>
                    <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
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
                            <input type="text" name="email"
                                class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>"
                                value="<?php if ($email == "-"){ echo ""; } else { echo $email; } ?>">
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
                        <?php
                            // Preparando a consulta SQL para selecionar todas as secretarias
                            $stmt_sec = $link->prepare("SELECT id_secretaria, secretaria FROM secretarias");
                            // Executando a consulta
                            $stmt_sec->execute();
                            // Obtendo o resultado
                            $result = $stmt_sec->get_result();
                        ?>
                        <div class="form-group">
                            <label for="secretaria">Secretaria</label>
                            <select class="form-control <?php echo (!empty($setor_err)) ? 'is-invalid' : ''; ?>" name="secretaria" id="secretaria">
                            <?php
                                if ($result->num_rows > 0) {
                                // Iterando sobre os resultados e criando as opções do dropdown
                                while($row = $result->fetch_assoc()) {
                                // Verificando se o valor atual é o selecionado
                                $selected = $row["id_secretaria"] == $secretaria ? ' selected' : '';
                                echo '<option value="' . htmlspecialchars($row["id_secretaria"]) . '"' . $selected . '>' . htmlspecialchars($row["secretaria"]) . '</option>';
                                }
                                } else {
                                    echo '<option value="">Nenhuma secretaria encontrada</option>';
                                }
                            ?>
                            </select>
                            <span class="invalid-feedback">
                                <?php echo $secretaria_err; ?>
                            </span>
                        </div>
                        <input type="hidden" name="id_lista" value="<?php echo $id_lista; ?>" />
                        <input type="submit" class="btn btn-primary" value="Salvar">
                        <a href="index.php" class="btn btn-secondary ml-2">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
