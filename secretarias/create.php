<?php
include('../verifica_login.php');
require_once "../config.php";

// Verificação de Admin
$useradmin = @$_SESSION['usuario'];

if ($stmt = mysqli_prepare($link, "SELECT admin FROM usuarios WHERE usuario = ?")) {
    mysqli_stmt_bind_param($stmt, "s", $useradmin);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $admin);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
}

$adminarray = ['admin' => $admin];

if (!$useradmin) {
    header("Location: ../login.php");
    exit;
}

if ($adminarray['admin'] == "s") {

    // Definir variáveis e inicializar com valores vazios
    $secretaria = "";
    $secretaria_err = "";

    // Processamento de dados do formulário quando o formulário é enviado
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Valida secretaria
        $input_secretaria = trim($_POST["secretaria"]);
        if (empty($input_secretaria)) {
            $secretaria_err = "Por favor entre uma secretaria";
        } else {
            $secretaria = $input_secretaria;
        }

        // Verifica os erros de entrada antes de inserir no banco de dados
        if (empty($secretaria_err)) {
            
            // 1. Verificação de Duplicidade (Segura com Prepared Statement)
            $sql_duplicate = "SELECT id_secretaria FROM secretarias WHERE secretaria = ?";
            if ($stmt_dup = mysqli_prepare($link, $sql_duplicate)) {
                mysqli_stmt_bind_param($stmt_dup, "s", $secretaria);
                mysqli_stmt_execute($stmt_dup);
                mysqli_stmt_store_result($stmt_dup);
                
                if (mysqli_stmt_num_rows($stmt_dup) > 0) {
                    // Já existe
                    header("location: ../error.php");
                    exit();
                }
                mysqli_stmt_close($stmt_dup);
            }

            // 2. Inserção
            $sql = "INSERT INTO secretarias (secretaria) VALUES (?)";

            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "s", $param_secretaria);
                $param_secretaria = $secretaria;

                if (mysqli_stmt_execute($stmt)) {
                    header("location: index.php");
                    exit();
                } else {
                    echo "Oops! Algo saiu errado. Tente novamente mais tarde.";
                }
                mysqli_stmt_close($stmt);
            }
        }
        mysqli_close($link);
    }
?>

<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Secretaria</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: var(--bs-body-bg); }
        .navbar-brand img { height: 40px; margin-right: 10px; }
        
        .input-group-text {
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
                            <i class="fa fa-plus-circle fa-2x text-success me-3"></i>
                            <div>
                                <h4 class="mb-0 fw-bold text-success-emphasis">Nova Secretaria</h4>
                                <small class="text-muted">Adicione um novo departamento ao sistema.</small>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Nome da Secretaria</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa fa-building"></i></span>
                                    <input type="text" name="secretaria" 
                                           class="form-control <?php echo (!empty($secretaria_err)) ? 'is-invalid' : ''; ?>" 
                                           value="<?php echo $secretaria; ?>" 
                                           placeholder="Ex: Secretaria de Saúde" autofocus>
                                    <span class="invalid-feedback"><?php echo $secretaria_err; ?></span>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="index.php" class="btn btn-outline-secondary me-md-2">
                                    <i class="fa fa-times me-1"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fa fa-save me-1"></i> Criar Secretaria
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
        // Script de Dark Mode (Persistente)
        const themeToggle = document.getElementById('themeToggle');
        const htmlElement = document.documentElement;
        const icon = themeToggle.querySelector('i');

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