<?php
// Mecanismo de login
include('../verifica_login.php');

// Include config file
require_once "../config.php";

// Verificação de Admin
$useradmin = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : null;

if (!$useradmin) {
    header("Location: login.php");
    exit;
}

// Verifica se o usuário é admin
$admin = "n";
if ($stmt = mysqli_prepare($link, "SELECT admin FROM usuarios WHERE usuario = ?")) {
    mysqli_stmt_bind_param($stmt, "s", $useradmin);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $admin);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
}

if ($admin !== "s") {
    header("Location: /lista/index.php");
    exit;
}

// Definir variáveis e inicializar com valores vazios
$usuario = $senha = $admin = "";
$usuario_err = $senha_err = $admin_err = "";

// Processamento de dados do formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Valida usuário
    $usuario = trim($_POST["usuario"]);
    if (empty($usuario)) {
        $usuario_err = "Por favor entre um usuário.";
    }

    // Valida senha
    $senha = trim($_POST["senha"]);
    if (empty($senha)) {
        $senha_err = "Por favor entre uma senha.";
    }

    // Valida admin
    $admin = $_POST["admin"] ?? "";
    if ($admin !== "s" && $admin !== "n") {
        $admin_err = "Valor inválido para o campo admin.";
    }

    // Verifica erros antes de inserir
    if (empty($usuario_err) && empty($senha_err) && empty($admin_err)) {
        // Verifica se já existe
        $sql_duplicate = "SELECT id FROM usuarios WHERE usuario = ?";
        if ($stmt = mysqli_prepare($link, $sql_duplicate)) {
            mysqli_stmt_bind_param($stmt, "s", $usuario);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) > 0) {
                $usuario_err = "Este usuário já está cadastrado.";
            } else {
                // Insere novo usuário
                $sql_insert = "INSERT INTO usuarios (usuario, senha, admin) VALUES (?, ?, ?)";
                if ($stmt_insert = mysqli_prepare($link, $sql_insert)) {
                    $hash_senha = password_hash($senha, PASSWORD_DEFAULT);
                    mysqli_stmt_bind_param($stmt_insert, "sss", $usuario, $hash_senha, $admin);
                    if (mysqli_stmt_execute($stmt_insert)) {
                        header("location: index.php");
                        exit();
                    } else {
                        echo "Oops! Algo deu errado. Tente novamente mais tarde.";
                    }
                    mysqli_stmt_close($stmt_insert);
                }
            }
            mysqli_stmt_close($stmt);
        }
    }

    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="pt-br" class="dark" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Criar usuário</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <style>
        .wrapper { width: 800px; margin: 0 auto; }
        body { background-color: #1C1C1C; color: white; margin: 0; }
        .form-control:focus { box-shadow: none; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h2 class="mt-5">Criar usuário</h2>
                <p>Por favor preencha os campos para adicionar um novo usuário à lista.</p>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label>Usuário</label>
                        <input type="text" name="usuario"
                               class="form-control <?php echo (!empty($usuario_err)) ? 'is-invalid' : ''; ?>"
                               value="<?php echo htmlspecialchars($usuario); ?>">
                        <span class="invalid-feedback"><?php echo $usuario_err; ?></span>
                    </div>
                    <div class="form-group">
                        <label>Senha</label>
                        <input type="password" name="senha"
                               class="form-control <?php echo (!empty($senha_err)) ? 'is-invalid' : ''; ?>"
                               value="<?php echo htmlspecialchars($senha); ?>">
                        <span class="invalid-feedback"><?php echo $senha_err; ?></span>
                    </div>
                    <div class="form-group">
                        <label>Admin</label>
                        <select name="admin" class="form-control <?php echo (!empty($admin_err)) ? 'is-invalid' : ''; ?>">
                            <option value="s" <?php echo ($admin === "s") ? 'selected' : ''; ?>>SIM</option>
                            <option value="n" <?php echo ($admin === "n") ? 'selected' : ''; ?>>NÃO</option>
                        </select>
                        <span class="invalid-feedback"><?php echo $admin_err; ?></span>
                    </div>
                    <br>
                    <input type="submit" class="btn btn-primary" value="Salvar">
                    <a href="index.php" class="btn btn-secondary ml-2">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
