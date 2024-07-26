<?php

//Mecanismo de login
//session_start();
include('verifica_login.php');

// Inclui arquivo de configuração
require_once "config.php";

// Definir variáveis e inicializar com valores vazios
$usuario = $senha = $admin = "";
$usuario_err = $senha_err = $admin_err = "";

// Processamento de dados do formulário quando o formulário é enviado
if (isset($_POST["usuario"]) && !empty($_POST["usuario"])) {
    // Get hidden input value
    $usuario = $_POST["usuario"];

    // Valida senha
    $input_senha = trim($_POST["senha"]);
    if (empty($input_senha)) {
        $senha_err = "Por favor entre uma senha.";
    } else {
        $senha = $input_senha;
    }

    // Valida confirmação
    $input_confirma = trim($_POST["confirma"]);
    if (empty($input_confirma)) {
        $confirma_err = "Por favor entre uma senha.";
    } elseif ($input_confirma = $input_senha) {
        $confirma_err = "Senha não confere, por favor entre a confirmação da senha novamente.";
    } else {
        $confirma = $input_confirma;
    }

    // Verifica os erros de entrada antes de inserir no banco de dados
    if (empty($senha_err) && empty($confirma_err)) {
        // Prepara uma instrução de atualização
        $sql = "UPDATE usuarios SET senha=? WHERE usuario=?";

        if ($stmt = mysqli_prepare($link, $sql)) {
            // Vincula as variáveis à instrução preparada como parâmetros
            mysqli_stmt_bind_param($stmt, "ss", $param_senha, $param_usuario);

            // Definir parâmetros
            $param_usuario = $usuario;
            $param_senha = md5($senha);

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
    if (isset($_GET["user"]) && !empty(trim($_GET["user"]))) {
        // Obter parâmetro de URL
        $user = trim($_GET["user"]);

        // Prepara uma declaração de seleção
        $sql = "SELECT * FROM usuarios WHERE usuario = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            // Vincula as variáveis à instrução preparada como parâmetros
            mysqli_stmt_bind_param($stmt, "s", $param_user);

            // Definir parâmetros
            $param_user = $user;

            // Tentativa de executar a instrução preparada
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);

                if (mysqli_num_rows($result) == 1) {
                    /* Busca a linha do resultado como um array associativo. Como o conjunto de resultados
                     contém apenas uma linha */
                    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

                    // Recupera o valor do campo individual
                    $usuario = $row["usuario"];
                    $senha = $row["senha"];
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
    <title>Atualizar Usuário</title>
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
                    <h2 class="mt-5">Atualizar Senha Usuário "
                        <?php echo $usuario; ?>"
                    </h2>
                    <p>Por favor utilize o formulário para atualizar a senha para o usuário
                        <?php echo $usuario; ?>.
                    </p>
                    <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
                        <div class="form-group">
                            <input type="hidden" name="usuario"
                                class="form-control <?php echo (!empty($usuario_err)) ? 'is-invalid' : ''; ?>"
                                value="<?php echo $usuario; ?>">
                            <span class="invalid-feedback">
                                <?php echo $usuario_err; ?>
                            </span>
                        </div>
                        <div class="form-group">
                            <label>Senha</label>
                            <input type="password" name="senha"
                                class="form-control <?php echo (!empty($senha_err)) ? 'is-invalid' : ''; ?>"
                                placeholder="*********">
                            <span class="invalid-feedback">
                                <?php echo $senha_err; ?>
                            </span>
                        </div>
                        <div class="form-group">
                            <label>Confirmar Senha</label>
                            <input type="password" name="confirma"
                                class="form-control <?php echo (!empty($confirma_err)) ? 'is-invalid' : ''; ?>"
                                placeholder="*********">
                            <span class="invalid-feedback">
                                <?php echo $confirma_err; ?>
                            </span>
                        </div>
                        <input type="hidden" name="id" value="<?php echo $id; ?>" />
                        <input type="submit" class="btn btn-primary" value="Salvar">
                        <a href="index.php" class="btn btn-secondary ml-2">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>