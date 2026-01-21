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

    // Verifique a existência do parâmetro id
    if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
        
        $sql = "SELECT * FROM usuarios WHERE id = ?";

        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $param_id);
            $param_id = trim($_GET["id"]);

            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);

                if (mysqli_num_rows($result) == 1) {
                    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                    
                    // Armazena em variáveis para facilitar o uso no HTML
                    $usuario_nome = $row["usuario"];
                    $is_admin = $row["admin"];
                    $id_usuario = $row["id"];
                    
                } else {
                    header("location: ../error.php");
                    exit();
                }
            } else {
                echo "Oops! Algo saiu errado, tente novamente.";
            }
        }
        mysqli_stmt_close($stmt);
        mysqli_close($link);
    } else {
        header("location: ../error.php");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Usuário</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: var(--bs-body-bg); }
        .navbar-brand img { height: 40px; margin-right: 10px; }
        
        /* Destaque para o ícone */
        .icon-box {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background-color: var(--bs-success);
            color: white;
            font-size: 1.5rem;
            margin-right: 15px;
        }
        
        .detail-label {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--bs-secondary-color);
            margin-bottom: 2px;
        }
        
        .detail-value {
            font-size: 1.25rem;
            font-weight: 600;
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
                            <i class="fa fa-user-circle fa-2x text-success me-3"></i>
                            <div>
                                <h4 class="mb-0 fw-bold text-success-emphasis">Detalhes do Usuário</h4>
                                <small class="text-muted">Visualizando registro #<?php echo $id_usuario; ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body p-4">
                        
                        <div class="d-flex align-items-center p-3 rounded bg-body-tertiary mb-3">
                            <div class="icon-box">
                                <i class="fa fa-user"></i>
                            </div>
                            <div>
                                <div class="detail-label">Nome de Usuário</div>
                                <div class="detail-value"><?php echo htmlspecialchars($usuario_nome); ?></div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center p-3 rounded bg-body-tertiary mb-3">
                            <div class="icon-box bg-secondary">
                                <i class="fa fa-lock"></i>
                            </div>
                            <div>
                                <div class="detail-label">Senha</div>
                                <div class="detail-value" style="letter-spacing: 3px; font-family: monospace;">●●●●●●●●</div>
                                <small class="text-muted" style="font-size: 0.75rem;">(Criptografada)</small>
                            </div>
                        </div>

                        <div class="d-flex align-items-center p-3 rounded bg-body-tertiary">
                            <div class="icon-box <?php echo ($is_admin == 's') ? 'bg-success' : 'bg-secondary'; ?>">
                                <i class="fa <?php echo ($is_admin == 's') ? 'fa-shield-alt' : 'fa-user-tag'; ?>"></i>
                            </div>
                            <div>
                                <div class="detail-label">Perfil de Acesso</div>
                                <div class="detail-value">
                                    <?php if ($is_admin == 's'): ?>
                                        <span class="badge bg-success px-3 py-2 rounded-pill">
                                            <i class="fa fa-check me-1"></i> Administrador
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary px-3 py-2 rounded-pill">
                                            <i class="fa fa-user me-1"></i> Usuário Padrão
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="card-footer bg-body-tertiary p-3 d-flex justify-content-between align-items-center">
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="fa fa-arrow-left me-2"></i> Voltar
                        </a>
                        <a href="update.php?id=<?php echo $id_usuario; ?>" class="btn btn-warning">
                            <i class="fa fa-pen me-2"></i> Editar
                        </a>
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