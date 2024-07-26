<?php

//Mecanismo de login
//session_start();
include('verifica_login.php');

// Include config file
require_once "config.php";

//verifica admin
$useradmin = @$_SESSION['usuario'];
$useradminL = mysqli_real_escape_string($link, $useradmin);
$queryadmin = "SELECT admin FROM usuarios WHERE usuario = '{$useradminL}'";
$resultadmin = mysqli_query($link, $queryadmin);
$adminarray = mysqli_fetch_array($resultadmin);

// Definir variáveis e inicializar com valores vazios
$usuario = $senha = $admin = "";
$usuario_err = $senha_err = $admin_err = "";

// Processamento de dados do formulário quando o formulário é enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Valida usuario
    $input_usuario = trim($_POST["usuario"]);
    if (empty($input_usuario)) {
        $usuario_err = "Por favor entre um usuário";
    } else {
        $usuario = $input_usuario;
    }

    // Valida senha
    $input_senha = trim($_POST["senha"]);
    if (empty($input_senha)) {
        $senha_err = "Por favor entre uma senha.";
    } else {
        $senha = $input_senha;
    }

    // Valida admin
    $input_admin = trim($_POST["admin"]);
    if (empty($input_admin)) {
        $admin_err = "Por favor entre se é admin";
    } else {
        $admin = $input_admin;
    }

    // Verifica os erros de entrada antes de inserir no banco de dados
    if (empty($usuario_err) && empty($senha_err) && empty($admin_err)) {
        // Prepara uma instrução de inserção
        $sql = "INSERT INTO usuarios (usuario, senha, admin) VALUES (?, ?, ?)";

        if ($stmt = mysqli_prepare($link, $sql)) {
            // Vincula as variáveis à instrução preparada como parâmetros
            mysqli_stmt_bind_param($stmt, "sss", $param_usuario, $param_senha, $param_admin);

            // Definir parâmetros
            $param_usuario = $usuario;
            $param_senha = md5($senha);
            $param_admin = $admin;

            $sql_duplicate = "SELECT * FROM usuarios WHERE usuario = '{$usuario}'";
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
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Criar usuário</title>
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
                    <h2 class="mt-5">Criar usuário</h2>
                    <p>Por favor preencha os campos para adicionar um novo usuário a lista</p>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="form-group">
                            <label>Usuário</label>
                            <input type="text" name="usuario"
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
                                value="<?php echo $senha; ?>">
                            <span class="invalid-feedback">
                                <?php echo $senha_err; ?>
                            </span>
                        </div>
                        <div class="form-group">
                            <label>Admin</label>
                            <select name="admin" class="form-control">
                                <option value="s">SIM</option>
                                <option value="n">NÃO</option>
                            </select>
                            <span class="invalid-feedback">
                                <?php echo $admin_err; ?>
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