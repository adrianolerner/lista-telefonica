<?php

//Mecanismo de login
include('../verifica_login.php');

// Inclui arquivo de configuração
require_once "../config.php";

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
    $usuario = $senha = $admin = "";
    $usuario_err = $senha_err = $admin_err = "";

    // Processamento de dados do formulário quando o formulário é enviado
    if (isset($_POST["id"]) && !empty($_POST["id"])) {
        // Get hidden input value
        $id = $_POST["id"];

        // Valida o usuário
        $input_usuario = trim($_POST["usuario"]);
        if (empty($input_usuario)) {
            $usuario_err = "Por favor entre um usuario.";
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
            $admin_err = "Por favor entre se é admin.";
        } else {
            $admin = $input_admin;
        }

        // Verifica os erros de entrada antes de inserir no banco de dados
        if (empty($usuario_err) && empty($senha_err) && empty($admin_err)) {
            // Prepara uma instrução de atualização
            $sql = "UPDATE usuarios SET usuario=?, senha=?, admin=? WHERE id=?";

            if ($stmt = mysqli_prepare($link, $sql)) {
                // Vincula as variáveis à instrução preparada como parâmetros
                mysqli_stmt_bind_param($stmt, "sssi", $param_usuario, $param_senha, $param_admin, $param_id);

                // Definir parâmetros
                $param_usuario = $usuario;
                $param_senha = md5($senha);
                $param_admin = $admin;
                $param_id = $id;

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
        if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
            // Obter parâmetro de URL
            $id = trim($_GET["id"]);

            // Prepara uma declaração de seleção
            $sql = "SELECT * FROM usuarios WHERE id = ?";
            if ($stmt = mysqli_prepare($link, $sql)) {
                // Vincula as variáveis à instrução preparada como parâmetros
                mysqli_stmt_bind_param($stmt, "i", $param_id);

                // Definir parâmetros
                $param_id = $id;

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
                        $admin = $row["admin"];
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
                                <label>Admin</label>
                                <select name="admin" class="form-control">
                                    <option value="n">NÃO</option>
                                    <option value="s">SIM</option>
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
}
?>