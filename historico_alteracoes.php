<?php
// ---------------------------------------------------------
// HISTÓRICO DE ALTERAÇÕES - VISUAL TIMELINE + DETALHES
// ---------------------------------------------------------
include('verifica_login.php');
require_once "config.php";

$useradmin = @$_SESSION['usuario'];

// Verificação Admin (Código mantido)
if ($stmt = mysqli_prepare($link, "SELECT admin FROM usuarios WHERE usuario = ?")) {
    mysqli_stmt_bind_param($stmt, "s", $useradmin);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $admin);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
}

if (!$useradmin || $admin !== "s") {
    header("Location: index.php");
    exit;
}
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
        
        /* Estilo Timeline na Tabela */
        .timeline-table {
            border-left: 3px solid var(--bs-border-color);
            margin-left: 10px;
        }
        
        .timeline-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 10px;
            position: relative;
            left: -18px; /* Alinha com a borda esquerda */
            border: 2px solid var(--bs-body-bg);
        }

        .dot-success { background-color: var(--bs-success); box-shadow: 0 0 5px var(--bs-success); }
        .dot-danger { background-color: var(--bs-danger); box-shadow: 0 0 5px var(--bs-danger); }
        .dot-primary { background-color: var(--bs-primary); box-shadow: 0 0 5px var(--bs-primary); }
        .dot-secondary { background-color: var(--bs-secondary); }

        /* Ajuste para células da tabela */
        table.dataTable td { vertical-align: top; padding-top: 15px; padding-bottom: 15px; }
        
        /* Estilo para o campo de Detalhes (Diff) */
        .diff-box {
            background-color: var(--bs-body-tertiary);
            border-radius: 6px;
            padding: 8px;
            font-size: 0.9em;
            border-left: 3px solid var(--bs-info);
        }
        
        /* Cores para o texto de diff (são inseridos via PHP no update.php) */
        s { color: var(--bs-danger); opacity: 0.7; text-decoration: line-through; }
        strong.text-success { color: #2ecc71 !important; }

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

        <div class="card shadow border-0 mb-5">
            <div class="card-header bg-success bg-opacity-10 border-bottom border-success border-opacity-25 py-3 d-flex justify-content-between align-items-center flex-wrap">
                <div class="d-flex align-items-center">
                    <i class="fa fa-history fa-2x text-success me-3"></i>
                    <div>
                        <h4 class="mb-0 fw-bold text-success-emphasis">Auditoria e Logs</h4>
                        <small class="text-muted">Rastreamento de alterações do sistema.</small>
                    </div>
                </div>
                
                <div class="mt-2 mt-md-0">
                    <a href="index.php" class="btn btn-outline-secondary me-2">
                        <i class="fa fa-arrow-left me-1"></i> Voltar
                    </a>
                    <form method="POST" action="limpar_log.php" class="d-inline"
                          onsubmit="return confirm('ATENÇÃO: Tem certeza que deseja apagar TODO o histórico? Esta ação é irreversível.');">
                        <button type="submit" class="btn btn-danger">
                            <i class="fa fa-trash me-1"></i> Limpar Logs
                        </button>
                    </form>
                </div>
            </div>

            <div class="card-body p-4">
                <?php
                // Query com o campo 'ramal' incluído
                $sql = "SELECT id, acao, id_lista, ramal, usuario, ip, datahora, detalhes FROM log_alteracoes ORDER BY id DESC";
                
                if ($result = mysqli_query($link, $sql)) {
                    if (mysqli_num_rows($result) > 0) {
                ?>
                    <div class="timeline-table ps-2">
                        <table id="logTable" class="table table-hover w-100 border-0">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">#ID</th>
                                    <th style="width: 15%;">Data/Hora</th>
                                    <th style="width: 15%;">Usuário</th>
                                    <th style="width: 10%;">Ação</th>
                                    <th style="width: 10%;">Ramal</th>
                                    <th style="width: 30%;">Detalhes da Alteração</th>
                                    <th style="width: 15%;">IP Origem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                while ($row = mysqli_fetch_assoc($result)) {
                                    // Configuração visual (Badges coloridos + Timeline Dots)
                                    $dotClass = 'dot-secondary';
                                    $badgeClass = 'bg-secondary';
                                    $icon = 'fa-info';
                                    
                                    if (stripos($row['acao'], 'Inclusão') !== false) {
                                        $dotClass = 'dot-success';
                                        $badgeClass = 'bg-success';
                                        $icon = 'fa-plus';
                                    } elseif (stripos($row['acao'], 'Exclusão') !== false) {
                                        $dotClass = 'dot-danger';
                                        $badgeClass = 'bg-danger';
                                        $icon = 'fa-trash';
                                    } elseif (stripos($row['acao'], 'Atualização') !== false) {
                                        $dotClass = 'dot-primary';
                                        $badgeClass = 'bg-primary';
                                        $icon = 'fa-edit';
                                    } elseif (stripos($row['acao'], 'Limpeza') !== false) {
                                        $dotClass = 'dot-danger';
                                        $badgeClass = 'bg-danger';
                                        $icon = 'fa-broom';
                                    }

                                    // Formatação da Data
                                    $dataObj = new DateTime($row['datahora']);
                                    $dataFmt = $dataObj->format('d/m/Y');
                                    $horaFmt = $dataObj->format('H:i');

                                    echo '<tr>';
                                    
                                    // Coluna ID (com bolinha da timeline)
                                    echo '<td>';
                                    echo '<span class="timeline-dot ' . $dotClass . '"></span>';
                                    echo '<span class="text-muted small">#' . $row['id'] . '</span>';
                                    echo '</td>';

                                    // Coluna Data
                                    echo '<td>';
                                    echo '<div class="fw-bold">' . $dataFmt . '</div>';
                                    echo '<div class="small text-muted">' . $horaFmt . '</div>';
                                    echo '</td>';

                                    // Coluna Usuário
                                    echo '<td>';
                                    echo '<div class="d-flex align-items-center">';
                                    echo '<div class="bg-secondary bg-opacity-25 rounded-circle p-1 me-2"><i class="fa fa-user text-secondary"></i></div>';
                                    echo '<div>' . htmlspecialchars($row['usuario']) . '</div>';
                                    echo '</div>';
                                    echo '</td>';

                                    // Coluna Ação (Badge colorido restaurado)
                                    echo '<td><span class="badge ' . $badgeClass . '"><i class="fa ' . $icon . ' me-1"></i>' . htmlspecialchars($row['acao']) . '</span></td>';

                                    // Coluna Ramal (Restaurada)
                                    echo '<td class="font-monospace text-primary fw-bold">' . htmlspecialchars($row['ramal']) . '</td>';

                                    // Coluna Detalhes
                                    echo '<td>';
                                    if (!empty($row['detalhes'])) {
                                        echo '<div class="diff-box">';
                                        echo $row['detalhes']; 
                                        echo '</div>';
                                    } else {
                                        echo '<span class="text-muted small opacity-50">Sem detalhes (Log antigo)</span>';
                                    }
                                    echo '</td>';

                                    // Coluna IP
                                    echo '<td class="small text-muted font-monospace">' . htmlspecialchars($row['ip']) . '</td>';

                                    echo '</tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                <?php
                    mysqli_free_result($result);
                    } else {
                        echo '<div class="text-center py-5 text-muted"><i class="fa fa-history fa-3x mb-3 opacity-25"></i><p>Nenhum registro de histórico encontrado.</p></div>';
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
                    "sEmptyTable":   "Nenhum registro",
                    "sInfo":         "_START_ a _END_ de _TOTAL_",
                    "sInfoEmpty":    "0 a 0 de 0",
                    "sInfoFiltered": "(filtro de _MAX_)",
                    "sLengthMenu":   "_MENU_",
                    "sLoadingRecords": "Carregando...",
                    "sProcessing":   "Processando...",
                    "sZeroRecords":  "Nenhum registro",
                    "sSearch":       "",
                    "sSearchPlaceholder": "Pesquisar log...",
                    "oPaginate": { "sNext": "Próx", "sPrevious": "Ant" }
                },
                "order": [[0, "desc"]], // Ordena por ID decrescente
                "pageLength": 25, // Mais registros por página facilita leitura
                "dom": "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
                       "<'row'<'col-sm-12'tr>>" +
                       "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            });
        });

        // Dark Mode Script
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