<?php

include('../verifica_login.php');
require_once "../config.php";

$useradmin = @$_SESSION['usuario'];

if ($stmt = mysqli_prepare($link, "SELECT admin FROM usuarios WHERE usuario = ?")) {
    mysqli_stmt_bind_param($stmt, "s", $useradmin);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $admin);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
}

if (!$useradmin) {
    header("Location: login.php");
    exit;
}

if ($admin == "s") {

    $usuario = $senha = $admin = "";
    $usuario_err = $senha_err = $admin_err = "";

    if (isset($_POST["id"]) && !empty($_POST["id"])) {

        $id = $_POST["id"];

        // Validação
        $input_usuario = trim($_POST["usuario"]);
        if (empty($input_usuario)) {
            $usuario_err = "Por favor entre um usuário.";
        } else {
            $usuario = $input_usuario;
        }

        $input_senha = trim($_POST["senha"]);
        $senha = $input_senha;

        $input_admin = trim($_POST["admin"]);
        if (empty($input_admin)) {
            $admin_err = "Por favor selecione se é admin.";
        } else {
            $admin = $input_admin;
        }

        if (empty($usuario_err) && empty($admin_err)) {
            if (!empty($senha)) {
                // Atualiza com senha
                $sql = "UPDATE usuarios SET usuario=?, senha=?, admin=? WHERE id=?";
            } else {
                // Atualiza sem mexer na senha
                $sql = "UPDATE usuarios SET usuario=?, admin=? WHERE id=?";
            }

            if ($stmt = mysqli_prepare($link, $sql)) {
                if (!empty($senha)) {
                    $param_usuario = $usuario;
                    $param_senha = password_hash($senha, PASSWORD_DEFAULT);
                    $param_admin = $admin;
                    $param_id = $id;
                    mysqli_stmt_bind_param($stmt, "sssi", $param_usuario, $param_senha, $param_admin, $param_id);
                } else {
                    $param_usuario = $usuario;
                    $param_admin = $admin;
                    $param_id = $id;
                    mysqli_stmt_bind_param($stmt, "ssi", $param_usuario, $param_admin, $param_id);
                }

                if (mysqli_stmt_execute($stmt)) {
                    header("location: index.php");
                    exit();
                } else {
                    echo "Erro ao atualizar. Tente novamente mais tarde.";
                }

                mysqli_stmt_close($stmt);
            }
        }

        mysqli_close($link);
    } else {
        if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
            $id = trim($_GET["id"]);

            $sql = "SELECT * FROM usuarios WHERE id = ?";
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "i", $param_id);
                $param_id = $id;

                if (mysqli_stmt_execute($stmt)) {
                    $result = mysqli_stmt_get_result($stmt);

                    if (mysqli_num_rows($result) == 1) {
                        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                        $usuario = $row["usuario"];
                        $admin = $row["admin"];
                    } else {
                        header("location: error.php");
                        exit();
                    }
                } else {
                    echo "Erro ao carregar dados.";
                }

                mysqli_stmt_close($stmt);
            }

            mysqli_close($link);
        } else {
            header("location: error.php");
            exit();
        }
    }
?>

<!DOCTYPE html>
<html lang="pt-br" class="dark" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <title>Atualizar Usuário</title>
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
                    <h2 class="mt-5">Atualizar Usuário</h2>
                    <p>Por favor utilize o formulário para atualizar o usuário.</p>
                    <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
                        <div class="form-group">
                            <label>Usuário</label>
                            <input type="text" name="usuario"
                                class="form-control <?php echo (!empty($usuario_err)) ? 'is-invalid' : ''; ?>"
                                value="<?php echo htmlspecialchars($usuario); ?>">
                            <span class="invalid-feedback">
                                <?php echo $usuario_err; ?>
                            </span>
                        </div>
                        <div class="form-group">
                            <label>Senha (deixe em branco para manter a atual)</label>
                            <input type="password" name="senha"
                                class="form-control <?php echo (!empty($senha_err)) ? 'is-invalid' : ''; ?>"
                                placeholder="*********">
                            <span class="invalid-feedback">
                                <?php echo $senha_err; ?>
                            </span>
                        </div>
                        <div class="form-group">
                            <label>Admin</label>
                            <select name="admin" class="form-control">
                                <option value="n" <?php echo ($admin == "n") ? "selected" : ""; ?>>NÃO</option>
                                <option value="s" <?php echo ($admin == "s") ? "selected" : ""; ?>>SIM</option>
                            </select>
                            <span class="invalid-feedback">
                                <?php echo $admin_err; ?>
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

<?php } else {
    header("Location: /lista/index.php");
    exit;
} ?>