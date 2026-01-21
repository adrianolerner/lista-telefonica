<?php
include('verifica_login.php');
require_once "config.php";

//Verificação de Admin
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
    header("Location: login.php");
    exit;
}

if ($adminarray['admin'] == "s") {

    // Definir variáveis e inicializar com valores vazios
    $banner = "";
    $banner_err = "";

    //Carrega os dados do banner
    if ($stmtBanner = mysqli_prepare($link, "SELECT banner FROM banner WHERE id_banner = ?")) {
        $id_banner = 1;
        mysqli_stmt_bind_param($stmtBanner, "i", $id_banner);
        mysqli_stmt_execute($stmtBanner);
        mysqli_stmt_bind_result($stmtBanner, $banner);
        mysqli_stmt_fetch($stmtBanner);
        mysqli_stmt_close($stmtBanner);
    }

    $bannerarray = ['banner' => $banner];

    // Processamento de dados do formulário quando o formulário é enviado
    if (isset($_POST["id_banner"]) && !empty($_POST["id_banner"])) {
        $id_banner = $_POST["id_banner"];

        // Valida banner
        $input_banner = trim($_POST["banner"]);
        if (empty($input_banner)) {
            $banner_err = "Por favor entre um banner.";
        } else {
            $banner = $input_banner;
        }

        // Verifica os erros de entrada antes de inserir no banco de dados
        if (empty($banner_err)) {
            $sql = "UPDATE banner SET banner=? WHERE id_banner=?";

            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "si", $param_banner, $param_id);
                $param_banner = $banner;
                $param_id = $id_banner;

                if (mysqli_stmt_execute($stmt)) {
                    header("location: index.php");
                    exit();
                } else {
                    echo "Oops! Algo saiu errado. Tente novamente mais tarde.";
                }
            }
            mysqli_stmt_close($stmt);
        }
        mysqli_close($link);
    } else {
        // Lógica de GET original mantida para carregar dados iniciais
        if (isset($_GET["id_banner"]) && !empty(trim($_GET["id_banner"]))) {
            $id_banner = trim($_GET["id_banner"]);
            // ... (O código original de carregamento já foi executado no início do script para preencher $banner, então podemos seguir)
        }
        // Nota: O bloco original de GET estava redundante com o bloco inicial de carregamento, 
        // mas mantive a estrutura lógica para garantir que $banner esteja preenchido.
    }
?>

<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atualizar Banner</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: var(--bs-body-bg); }
        .navbar-brand img { height: 40px; margin-right: 10px; }
        
        /* Ajuste para textarea */
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
            <div class="col-lg-8 col-md-10">
                
                <div class="card shadow border-0">
                    <div class="card-header bg-success bg-opacity-10 border-bottom border-success border-opacity-25 py-3">
                        <div class="d-flex align-items-center">
                            <i class="fa fa-bullhorn fa-2x text-success me-3"></i>
                            <div>
                                <h4 class="mb-0 fw-bold text-success-emphasis">Atualizar Banner</h4>
                                <small class="text-muted">Edite o texto que aparece na rolagem do topo da página.</small>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
                            
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Texto do Banner</label>
                                <textarea name="banner" 
                                    class="form-control <?php echo (!empty($banner_err)) ? 'is-invalid' : ''; ?>" 
                                    rows="5" 
                                    placeholder="Digite o aviso aqui..."><?php echo htmlspecialchars($bannerarray["banner"]); ?></textarea>
                                <span class="invalid-feedback"><?php echo $banner_err; ?></span>
                                <div class="form-text mt-2">
                                    <i class="fa fa-info-circle me-1"></i> Este texto ficará passando da direita para a esquerda na página inicial.
                                </div>
                            </div>

                            <input type="hidden" name="id_banner" value="<?php echo $id_banner; ?>" />
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="index.php" class="btn btn-outline-secondary me-md-2">
                                    <i class="fa fa-times me-1"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fa fa-save me-1"></i> Salvar Banner
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
    // Redirecionamento caso não seja admin
    header("Location: index.php");
    exit;
}
?>