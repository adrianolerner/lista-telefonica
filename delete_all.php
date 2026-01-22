<?php
include('verifica_login.php');
require_once "config.php";

// Verificação de Admin
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

// Apenas Admin pode acessar
if ($admin === "s") {

    // Processamento do POST
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["confirm"]) && $_POST["confirm"] === "sim") {
        
        // Detecção de IP mais robusta
        $ip = $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'];
        $ipaddress = strstr($ip, ',', true) ?: $ip; // Se não houver vírgula, usa o IP inteiro

        // ---------------------------------------------------------
        // 1. CONTA REGISTROS ANTES DE APAGAR (PARA LOG)
        // ---------------------------------------------------------
        $total_registros = 0;
        $sqlCount = "SELECT COUNT(*) FROM lista";
        if ($resCount = mysqli_query($link, $sqlCount)) {
            $total_registros = mysqli_fetch_row($resCount)[0];
        }
        // ---------------------------------------------------------

        // Executa exclusão total e reseta o AUTO_INCREMENT
        $sqlDelete = "DELETE FROM lista";
        $sqlResetAI = "ALTER TABLE lista AUTO_INCREMENT = 1";

        if (mysqli_query($link, $sqlDelete)) {
            mysqli_query($link, $sqlResetAI);

            // ---------------------------------------------------------
            // REGISTRA O LOG DE LIMPEZA TOTAL
            // ---------------------------------------------------------
            $acao = "Limpeza Total"; // Ação destacada no log
            $ramal = "TODOS";
            $id_lista = 0;
            $usuario = $_SESSION['usuario'];
            $datahora = date('Y-m-d H:i:s');
            
            // Monta detalhes com a quantidade apagada
            $detalhes_log = "<strong class='text-danger'>OPERAÇÃO CRÍTICA:</strong><br>" .
                            "Tabela de contatos resetada.<br>" .
                            "Total de registros removidos: <strong>$total_registros</strong>";

            $sql_log = "INSERT INTO log_alteracoes (acao, id_lista, ramal, usuario, ip, datahora, detalhes) VALUES (?, ?, ?, ?, ?, ?, ?)";
            if ($stmt_log = mysqli_prepare($link, $sql_log)) {
                // Bind atualizado para "sisssss"
                mysqli_stmt_bind_param($stmt_log, "sisssss", $acao, $id_lista, $ramal, $usuario, $ipaddress, $datahora, $detalhes_log);
                mysqli_stmt_execute($stmt_log);
                mysqli_stmt_close($stmt_log);
            }
            // ---------------------------------------------------------

            header("Location: index.php");
            exit;
        } else {
            $erro = "Erro ao excluir os registros: " . mysqli_error($link);
        }
    }
?>

<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apagar TUDO - Zona de Perigo</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: var(--bs-body-bg); }
        .navbar-brand img { height: 40px; margin-right: 10px; }
        
        /* Animação de Alerta Crítico */
        .danger-icon {
            animation: shake 0.5s infinite;
            color: #dc3545;
        }
        @keyframes shake {
            0% { transform: translate(1px, 1px) rotate(0deg); }
            10% { transform: translate(-1px, -2px) rotate(-1deg); }
            20% { transform: translate(-3px, 0px) rotate(1deg); }
            30% { transform: translate(3px, 2px) rotate(0deg); }
            40% { transform: translate(1px, -1px) rotate(1deg); }
            50% { transform: translate(-1px, 2px) rotate(-1deg); }
            60% { transform: translate(-3px, 1px) rotate(0deg); }
            70% { transform: translate(3px, 1px) rotate(-1deg); }
            80% { transform: translate(-1px, -1px) rotate(1deg); }
            90% { transform: translate(1px, 2px) rotate(0deg); }
            100% { transform: translate(1px, -2px) rotate(-1deg); }
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-danger shadow-sm mb-5">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fa fa-exclamation-triangle me-2"></i> ZONA DE PERIGO
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
            <div class="col-md-8 col-lg-6">
                
                <?php if (isset($erro)): ?>
                    <div class="alert alert-warning"><?php echo $erro; ?></div>
                <?php endif; ?>

                <div class="card border-danger shadow-lg text-center">
                    <div class="card-header bg-danger text-white py-3">
                        <h3 class="mb-0 fw-bold">ATENÇÃO EXTREMA</h3>
                    </div>
                    
                    <div class="card-body p-5">
                        <div class="mb-4 danger-icon">
                            <i class="fa fa-radiation fa-6x"></i>
                        </div>
                        
                        <h2 class="card-title fw-bold text-danger mb-3">APAGAR TODOS OS REGISTROS?</h2>
                        
                        <div class="alert alert-secondary border-danger text-danger fw-bold" role="alert">
                            <i class="fa fa-skull-crossbones me-2"></i>
                            Esta ação irá excluir permanentemente todos os contatos da lista telefônica.
                        </div>

                        <p class="text-muted mb-4">
                            Isso não pode ser desfeito. O histórico de logs será mantido, mas todos os contatos e setores serão removidos.
                            Certifique-se de ter um backup (Exportar CSV) antes de continuar.
                        </p>

                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <input type="hidden" name="confirm" value="sim" />
                            
                            <div class="d-grid gap-3">
                                <button type="submit" class="btn btn-danger btn-lg py-3 fw-bold">
                                    <i class="fa fa-trash-alt me-2"></i> SIM, APAGAR TUDO
                                </button>
                                <a href="index.php" class="btn btn-outline-secondary btn-lg">
                                    <i class="fa fa-arrow-left me-2"></i> CANCELAR E VOLTAR
                                </a>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-muted small">
                        Operação registrada em log por segurança.
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
    // Se não for admin, chuta de volta
    header("Location: index.php");
    exit;
}
?>