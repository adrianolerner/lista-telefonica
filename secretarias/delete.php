<?php
// ---------------------------------------------------------
// LÓGICA PHP ORIGINAL (MANTIDA)
// ---------------------------------------------------------
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
    header("Location: ../login.php"); // Caminho ajustado
    exit;
}

if ($adminarray['admin'] == "s") {

    // Processa a operação de exclusão após a confirmação
    if (isset($_POST["id_secretaria"]) && !empty($_POST["id_secretaria"])) {

        $sql = "DELETE FROM secretarias WHERE id_secretaria = ?";

        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $param_id);
            $param_id = trim($_POST["id_secretaria"]);

            if (mysqli_stmt_execute($stmt)) {
                header("location: index.php");
                exit();
            } else {
                echo "Oops! Algo saiu errado, tente novamente.";
            }
        }
        mysqli_stmt_close($stmt);
        mysqli_close($link);
    } else {
        // Verifica a existência do parâmetro id
        if (empty(trim($_GET["id_secretaria"]))) {
            header("location: ../error.php"); // Caminho ajustado
            exit();
        }
    }
?>

<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apagar Secretaria</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: var(--bs-body-bg); }
        .navbar-brand img { height: 40px; margin-right: 10px; }
        
        /* Animação suave para o ícone de alerta */
        .pulse-icon {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
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
            <div class="col-md-6 col-lg-5">
                
                <div class="card border-danger shadow text-center">
                    <div class="card-header bg-danger text-white py-3">
                        <h4 class="mb-0 fw-bold"><i class="fa fa-trash-alt me-2"></i> Confirmar Exclusão</h4>
                    </div>
                    
                    <div class="card-body p-5">
                        <div class="mb-4 text-danger pulse-icon">
                            <i class="fa fa-exclamation-triangle fa-5x"></i>
                        </div>
                        
                        <h5 class="card-title mb-3">Tem certeza que deseja apagar?</h5>
                        <p class="card-text text-muted mb-4">
                            Você está prestes a excluir uma Secretaria. <br>
                            <small class="text-danger fw-bold">Atenção: Se houver contatos vinculados a esta secretaria, eles podem perder a referência.</small>
                        </p>

                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <input type="hidden" name="id_secretaria" value="<?php echo trim($_GET["id_secretaria"]); ?>" />
                            
                            <div class="d-grid gap-2">
                                <input type="submit" value="Sim, Apagar Secretaria" class="btn btn-danger btn-lg">
                                <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
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