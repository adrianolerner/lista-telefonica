<?php
// Mecanismo de login
include('verifica_login.php');

//Verificação de IP (usado para inserção do IP no LOG)
$ip = $_SERVER['HTTP_X_REAL_IP'];
//$ipaddress = "172.16.0.10";
$ipaddress = strstr($ip, ',', true);

// Processa a requisição de delete após a confirmação
if (isset($_POST["id_lista"]) && !empty($_POST["id_lista"])) {
    // Inclui config file
    require_once "config.php";
    
    $id_lista = trim($_POST["id_lista"]);

    // ---------------------------------------------------------
    // 1. BUSCA DADOS COMPLETOS ANTES DE APAGAR (BACKUP PARA LOG)
    // ---------------------------------------------------------
    $nome = $ramal = $email = $setor = $secretaria = "";
    
    $sql_backup = "SELECT nome, ramal, email, setor, secretaria FROM lista WHERE id_lista = ?";
    if ($stmt_backup = mysqli_prepare($link, $sql_backup)) {
        mysqli_stmt_bind_param($stmt_backup, "i", $id_lista);
        if (mysqli_stmt_execute($stmt_backup)) {
            $result = mysqli_stmt_get_result($stmt_backup);
            if ($row = mysqli_fetch_assoc($result)) {
                $nome = $row['nome'];
                $ramal = $row['ramal'];
                $email = $row['email'];
                $setor = $row['setor'];
                $secretaria = $row['secretaria']; // ID da secretaria
            }
        }
        mysqli_stmt_close($stmt_backup);
    }
    // ---------------------------------------------------------

    // Prepara o statement de delete
    $sql = "DELETE FROM lista WHERE id_lista = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id_lista);

        // Tenta executar os parametros configurados
        if (mysqli_stmt_execute($stmt)) {

            // ---------------------------------------------------------
            // REGISTRA O LOG DE EXCLUSÃO (COM DETALHES)
            // ---------------------------------------------------------
            $acao = "Exclusão";
            $usuario = $_SESSION['usuario'];
            $datahora = date('Y-m-d H:i:s');
            
            // Monta string com dados riscados para indicar remoção
            $detalhes_log = "Registro Apagado:<br>" .
                            "Nome: <s>$nome</s><br>" .
                            "Ramal: <s>$ramal</s><br>" .
                            "Setor: <s>$setor</s><br>" .
                            "Email: <s>$email</s>";

            // Query atualizada com coluna 'detalhes'
            $sql_log = "INSERT INTO log_alteracoes (acao, id_lista, ramal, usuario, ip, datahora, detalhes) VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            if ($stmt_log = mysqli_prepare($link, $sql_log)) {
                // Bind atualizado para "sisssss"
                mysqli_stmt_bind_param($stmt_log, "sisssss", $acao, $id_lista, $ramal, $usuario, $ipaddress, $datahora, $detalhes_log);
                mysqli_stmt_execute($stmt_log);
                mysqli_stmt_close($stmt_log);
            }
            // ---------------------------------------------------------

            // Redireciona para o index após exclusão
            header("location: index.php");
            exit();
        } else {
            echo "Oops! Algo saiu errado. Tente novamente mais tarde.";
        }
    }

    // Fecha o statement
    mysqli_stmt_close($stmt);

    // Fecha a conexão
    mysqli_close($link);
} else {
    // Checa a existência de id de parâmetros
    if (empty(trim($_GET["id_lista"]))) {
        // URL não possui parâmetro, redireciona para a página de erro
        header("location: error.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apagar Registro</title>
    
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
                            Esta ação removerá permanentemente este registro da lista telefônica. Esta ação <strong>não pode</strong> ser desfeita.
                        </p>

                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <input type="hidden" name="id_lista" value="<?php echo trim($_GET["id_lista"]); ?>" />
                            
                            <div class="d-grid gap-2">
                                <input type="submit" value="Sim, Apagar Registro" class="btn btn-danger btn-lg">
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