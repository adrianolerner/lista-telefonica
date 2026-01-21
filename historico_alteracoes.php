<?php
// ---------------------------------------------------------
// LÓGICA PHP ORIGINAL (MANTIDA)
// ---------------------------------------------------------
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

if ($admin === "s") {
?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Alterações</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: var(--bs-body-bg); }
        .navbar-brand img { height: 40px; margin-right: 10px; }
        
        /* Ajustes da Tabela */
        table.dataTable td, table.dataTable th { vertical-align: middle; }
        [data-bs-theme="dark"] .table thead th {
            background-color: #2b3035;
            color: #fff;
            border-bottom: 2px solid #495057;
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
        
        <?php if (isset($_SESSION['mensagem'])): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fa fa-info-circle me-2"></i> <?php echo $_SESSION['mensagem']; unset($_SESSION['mensagem']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow border-0">
            <div class="card-header bg-success bg-opacity-10 border-bottom border-success border-opacity-25 py-3 d-flex justify-content-between align-items-center flex-wrap">
                <div class="d-flex align-items-center">
                    <i class="fa fa-history fa-2x text-success me-3"></i>
                    <div>
                        <h4 class="mb-0 fw-bold text-success-emphasis">Histórico de Alterações</h4>
                        <small class="text-muted">Registro de todas as operações administrativas.</small>
                    </div>
                </div>
                
                <div class="mt-2 mt-md-0">
                    <a href="index.php" class="btn btn-outline-secondary me-2">
                        <i class="fa fa-arrow-left me-1"></i> Voltar
                    </a>
                    <form method="POST" action="limpar_log.php" class="d-inline"
                          onsubmit="return confirm('ATENÇÃO: Tem certeza que deseja apagar TODO o histórico? Esta ação é irreversível.');">
                        <button type="submit" class="btn btn-danger">
                            <i class="fa fa-trash me-1"></i> Limpar Histórico
                        </button>
                    </form>
                </div>
            </div>

            <div class="card-body p-4">
                <?php
                $sql = "SELECT id, acao, id_lista, ramal, usuario, ip, datahora FROM log_alteracoes";
                if ($result = mysqli_query($link, $sql)) {
                    if (mysqli_num_rows($result) > 0) {
                ?>
                    <table id="logTable" class="table table-hover w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>DATA/HORA</th>
                                <th>USUÁRIO</th>
                                <th>IP</th>
                                <th>AÇÃO</th>
                                <th>RAMAL</th>
                                <th>REF. ID</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($row = mysqli_fetch_assoc($result)) {
                                // Define cor do badge baseado na ação
                                $badgeClass = 'bg-secondary';
                                $icon = 'fa-info';
                                
                                if (stripos($row['acao'], 'Inclusão') !== false) {
                                    $badgeClass = 'bg-success';
                                    $icon = 'fa-plus';
                                } elseif (stripos($row['acao'], 'Exclusão') !== false) {
                                    $badgeClass = 'bg-danger';
                                    $icon = 'fa-trash';
                                } elseif (stripos($row['acao'], 'Atualização') !== false) {
                                    $badgeClass = 'bg-primary';
                                    $icon = 'fa-edit';
                                }

                                echo '<tr>';
                                echo '<td><span class="text-muted small">#' . $row['id'] . '</span></td>';
                                echo '<td>' . date("d/m/Y H:i", strtotime($row['datahora'])) . '</td>';
                                echo '<td class="fw-bold">' . htmlspecialchars($row['usuario']) . '</td>';
                                echo '<td class="small text-muted">' . htmlspecialchars($row['ip']) . '</td>';
                                echo '<td><span class="badge ' . $badgeClass . '"><i class="fa ' . $icon . ' me-1"></i>' . htmlspecialchars($row['acao']) . '</span></td>';
                                echo '<td class="font-monospace text-primary">' . htmlspecialchars($row['ramal']) . '</td>';
                                echo '<td class="small text-muted">#' . htmlspecialchars($row['id_lista']) . '</td>';
                                echo '</tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                <?php
                    mysqli_free_result($result);
                    } else {
                        echo '<div class="alert alert-info"><i class="fa fa-info-circle me-2"></i> Nenhum registro de histórico encontrado.</div>';
                    }
                } else {
                    echo '<div class="alert alert-danger">Erro ao consultar o banco de dados.</div>';
                }
                mysqli_close($link);
                ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function () {
            $('#logTable').DataTable({
                "language": {
                    "sEmptyTable":   "Nenhum registro encontrado",
                    "sInfo":         "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                    "sInfoEmpty":    "Mostrando 0 até 0 de 0 registros",
                    "sInfoFiltered": "(Filtrados de _MAX_ registros)",
                    "sLengthMenu":   "_MENU_ resultados por página",
                    "sLoadingRecords": "Carregando...",
                    "sProcessing":   "Processando...",
                    "sZeroRecords":  "Nenhum registro encontrado",
                    "sSearch":       "Pesquisar:",
                    "oPaginate": {
                        "sNext":     "Próximo",
                        "sPrevious": "Anterior",
                        "sFirst":    "Primeiro",
                        "sLast":     "Último"
                    }
                },
                "order": [[0, "desc"]] // Ordena por ID decrescente (mais recentes primeiro)
            });
        });

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
    header("Location: index.php");
    exit;
}
?>