<?php
// Mecanismo de login
include('verifica_login.php');

// Inclui arquivo de configuração
require_once "config.php";

// Definir variáveis e inicializar com valores vazios
$usuario = $senha = "";
$usuario_err = $senha_err = $confirma_err = "";

// Processamento de dados do formulário quando o formulário é enviado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["usuario"])) {
    // Recupera o nome do usuário do campo oculto
    $usuario = trim($_POST["usuario"]);

    // Valida senha
    $input_senha = trim($_POST["senha"]);
    if (empty($input_senha)) {
        $senha_err = "Por favor, informe uma nova senha.";
    } else {
        $senha = $input_senha;
    }

    // Valida confirmação
    $input_confirma = trim($_POST["confirma"]);
    if (empty($input_confirma)) {
        $confirma_err = "Por favor, confirme a nova senha.";
    } elseif ($input_confirma !== $input_senha) {
        $confirma_err = "A confirmação da senha não confere.";
    }

    // Se não houver erros, atualiza a senha no banco
    if (empty($senha_err) && empty($confirma_err)) {
        // Prepara a instrução de atualização
        $sql = "UPDATE usuarios SET senha = ? WHERE usuario = ?";

        if ($stmt = mysqli_prepare($link, $sql)) {
            // Cria o hash da senha
            $param_senha = password_hash($senha, PASSWORD_DEFAULT);
            $param_usuario = $usuario;

            // Vincula os parâmetros
            mysqli_stmt_bind_param($stmt, "ss", $param_senha, $param_usuario);

            // Executa a query
            if (mysqli_stmt_execute($stmt)) {
                header("location: index.php");
                exit();
            } else {
                echo "Erro ao atualizar senha. Tente novamente mais tarde.";
            }

            mysqli_stmt_close($stmt);
        }
    }

    mysqli_close($link);

} elseif (isset($_GET["user"]) && !empty(trim($_GET["user"]))) {
    // Recupera o usuário pela URL
    $user = trim($_GET["user"]);

    // Busca o usuário no banco
    $sql = "SELECT usuario FROM usuarios WHERE usuario = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $param_user);
        $param_user = $user;

        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) == 1) {
                $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                $usuario = $row["usuario"];
            } else {
                header("location: error.php");
                exit();
            }
        } else {
            echo "Erro ao consultar usuário.";
        }

        mysqli_stmt_close($stmt);
    }

    mysqli_close($link);
} else {
    header("location: error.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="pt-br" class="dark" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <title>Atualizar Usuário</title>
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
                    <h2 class="mt-5">Atualizar Senha Usuário "
                        <?php echo $usuario; ?>"
                    </h2>
                    <p>Por favor utilize o formulário para atualizar a senha para o usuário
                        <?php echo $usuario; ?>.
                    </p>
                    <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
                        <div class="form-group">
                            <input type="hidden" name="usuario" value="<?php echo $usuario; ?>">
                        </div>

                        <div class="form-group">
                            <label>Senha</label>
                            <input type="password" name="senha"
                                class="form-control <?php echo (!empty($senha_err)) ? 'is-invalid' : ''; ?>"
                                placeholder="*********">
                            <span class="invalid-feedback"><?php echo $senha_err; ?></span>
                        </div>

                        <div class="form-group">
                            <label>Confirmar Senha</label>
                            <input type="password" name="confirma"
                                class="form-control <?php echo (!empty($confirma_err)) ? 'is-invalid' : ''; ?>"
                                placeholder="*********">
                            <span class="invalid-feedback"><?php echo $confirma_err; ?></span>
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