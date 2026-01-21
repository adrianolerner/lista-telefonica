<?php
include('../verifica_login.php');
require_once "../config.php";

$useradmin = @$_SESSION['usuario'];

if ($stmt = mysqli_prepare($link, "SELECT admin FROM usuarios WHERE usuario = ?")) {
    mysqli_stmt_bind_param($stmt, "s", $useradmin);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $admin_logged);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
}

if (!$useradmin) {
    header("Location: ../login.php");
    exit;
}

// Verifica se quem está logado é admin
if ($admin_logged == "s") {

    $usuario = $senha = $admin = "";
    $usuario_err = $senha_err = $admin_err = "";

    // PROCESSAMENTO POST
    if (isset($_POST["id"]) && !empty($_POST["id"])) {

        $id = $_POST["id"];

        // Validação Usuário
        $input_usuario = trim($_POST["usuario"]);
        if (empty($input_usuario)) {
            $usuario_err = "Por favor entre um usuário.";
        } else {
            $usuario = $input_usuario;
        }

        // Validação Senha (pode ser vazia se não quiser alterar)
        $input_senha = trim($_POST["senha"]);
        $senha = $input_senha;

        // Validação Admin
        $input_admin = trim($_POST["admin"]);
        if (empty($input_admin)) {
            $admin_err = "Por favor selecione se é admin.";
        } else {
            $admin = $input_admin;
        }

        if (empty($usuario_err) && empty($admin_err)) {
            if (!empty($senha)) {
                // Atualiza TUDO (incluindo senha nova)
                $sql = "UPDATE usuarios SET usuario=?, senha=?, admin=? WHERE id=?";
            } else {
                // Atualiza APENAS dados (mantém senha antiga)
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
        // PROCESSAMENTO GET (Carregar dados)
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
                        header("location: ../error.php");
                        exit();
                    }
                } else {
                    echo "Erro ao carregar dados.";
                }
                mysqli_stmt_close($stmt);
            }
            mysqli_close($link);
        } else {
            header("location: ../error.php");
            exit();
        }
    }
?>

<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atualizar Usuário</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: var(--bs-body-bg); }
        .navbar-brand img { height: 40px; margin-right: 10px; }
        
        .input-group-text {
            background-color: var(--bs-tertiary-bg);
            border-color: var(--bs-border-color);
            color: var(--bs-secondary-color);
            cursor: pointer;
        }
        .form-control:focus, .form-select:focus {
            border-color: #198754;
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
        }
    </style>
</head>

<body>
    
    <nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm mb-5">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../index.php">
                <i class="fa fa-phone-square me-2"></i> LISTA TELEFÔNICA
            </a>
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-light btn-sm" id="themeToggle" title="Alternar Tema">
                    <i class="fa fa-moon"></i>
                </button>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                
                <div class="card shadow border-0">
                    <div class="card-header bg-success bg-opacity-10 border-bottom border-success border-opacity-25 py-3">
                        <div class="d-flex align-items-center">
                            <i class="fa fa-user-edit fa-2x text-success me-3"></i>
                            <div>
                                <h4 class="mb-0 fw-bold text-success-emphasis">Editar Usuário</h4>
                                <small class="text-muted">Atualize as credenciais de acesso.</small>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nome de Usuário</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa fa-user"></i></span>
                                    <input type="text" name="usuario" class="form-control <?php echo (!empty($usuario_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($usuario); ?>">
                                    <span class="invalid-feedback"><?php echo $usuario_err; ?></span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Senha</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa fa-lock"></i></span>
                                    <input type="password" name="senha" id="senha" class="form-control <?php echo (!empty($senha_err)) ? 'is-invalid' : ''; ?>" placeholder="Deixe em branco para manter a atual">
                                    <span class="input-group-text" onclick="togglePassword()">
                                        <i class="fa fa-eye" id="toggleIcon"></i>
                                    </span>
                                    <span class="invalid-feedback"><?php echo $senha_err; ?></span>
                                </div>
                                <div class="form-text text-muted"><i class="fa fa-info-circle me-1"></i> Preencha apenas se desejar alterar a senha.</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Nível de Acesso</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa fa-shield-alt"></i></span>
                                    <select name="admin" class="form-select <?php echo (!empty($admin_err)) ? 'is-invalid' : ''; ?>">
                                        <option value="s" <?php echo ($admin == "s") ? "selected" : ""; ?>>Sim - Administrador</option>
                                        <option value="n" <?php echo ($admin == "n") ? "selected" : ""; ?>>Não - Usuário Padrão</option>
                                    </select>
                                    <span class="invalid-feedback"><?php echo $admin_err; ?></span>
                                </div>
                            </div>
                            
                            <input type="hidden" name="id" value="<?php echo $id; ?>" />
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="index.php" class="btn btn-outline-secondary me-md-2">
                                    <i class="fa fa-times me-1"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fa fa-save me-1"></i> Salvar Alterações
                                </button>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle Senha
        function togglePassword() {
            const input = document.getElementById('senha');
            const icon = document.getElementById('toggleIcon');
            
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = "password";
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Script de Dark Mode (Persistente)
        const themeToggle = document.getElementById('themeToggle');
        const htmlElement = document.documentElement;
        const icon = themeToggle.querySelector('#themeToggle i');

        const savedTheme = localStorage.getItem('theme') || 'dark';
        htmlElement.setAttribute('data-bs-theme', savedTheme);
        updateIcon(savedTheme);

        themeToggle.addEventListener('click', () => {
            const currentTheme = htmlElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            htmlElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateIcon(newTheme);
        });

        function updateIcon(theme) {
            if (theme === 'dark') {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            } else {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            }
        }
    </script>
</body>
</html>
<?php 
} else {
    header("Location: ../index.php");
    exit;
}
?>