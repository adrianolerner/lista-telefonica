<?php
include('verifica_login.php');
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
        $sql = "UPDATE usuarios SET senha = ? WHERE usuario = ?";

        if ($stmt = mysqli_prepare($link, $sql)) {
            $param_senha = password_hash($senha, PASSWORD_DEFAULT);
            $param_usuario = $usuario;

            mysqli_stmt_bind_param($stmt, "ss", $param_senha, $param_usuario);

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
<html lang="pt-br" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atualizar Senha</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: var(--bs-body-bg); }
        .navbar-brand img { height: 40px; margin-right: 10px; }
        
        /* Ajuste para inputs com ícones */
        .input-group-text {
            cursor: pointer;
            background-color: var(--bs-tertiary-bg);
            border-color: var(--bs-border-color);
            color: var(--bs-secondary-color);
        }
        .form-control:focus {
            border-color: #198754;
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
        }
    </style>
</head>

<body>
    
    <nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm mb-5">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
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
            <div class="col-lg-5 col-md-7">
                
                <div class="card shadow border-0">
                    <div class="card-header bg-success bg-opacity-10 border-bottom border-success border-opacity-25 py-3">
                        <div class="d-flex align-items-center">
                            <i class="fa fa-key fa-2x text-success me-3"></i>
                            <div>
                                <h4 class="mb-0 fw-bold text-success-emphasis">Trocar Senha</h4>
                                <small class="text-muted">Usuário: <strong><?php echo htmlspecialchars($usuario); ?></strong></small>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
                            <input type="hidden" name="usuario" value="<?php echo htmlspecialchars($usuario); ?>">
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nova Senha</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa fa-lock"></i></span>
                                    <input type="password" name="senha" id="senha" 
                                           class="form-control <?php echo (!empty($senha_err)) ? 'is-invalid' : ''; ?>" 
                                           placeholder="Digite a nova senha">
                                    <span class="input-group-text" onclick="togglePassword('senha')">
                                        <i class="fa fa-eye"></i>
                                    </span>
                                    <span class="invalid-feedback"><?php echo $senha_err; ?></span>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Confirmar Senha</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa fa-lock"></i></span>
                                    <input type="password" name="confirma" id="confirma" 
                                           class="form-control <?php echo (!empty($confirma_err)) ? 'is-invalid' : ''; ?>" 
                                           placeholder="Confirme a nova senha">
                                    <span class="input-group-text" onclick="togglePassword('confirma')">
                                        <i class="fa fa-eye"></i>
                                    </span>
                                    <span class="invalid-feedback"><?php echo $confirma_err; ?></span>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="index.php" class="btn btn-outline-secondary me-md-2">
                                    <i class="fa fa-times me-1"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fa fa-save me-1"></i> Atualizar Senha
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
        // Função para mostrar/esconder senha
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling.querySelector('i');
            
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
        const icon = themeToggle.querySelector('#themeToggle i'); // Corrigido seletor

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