<?php
include('verifica_login.php');
require_once "config.php";

// Verificação de Admin (Segurança)
$useradmin = @$_SESSION['usuario'];
if ($stmt = mysqli_prepare($link, "SELECT admin FROM usuarios WHERE usuario = ?")) {
    mysqli_stmt_bind_param($stmt, "s", $useradmin);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $admin_flag);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
}

if (!$useradmin || $admin_flag !== 's') {
    header("Location: index.php");
    exit;
}

// --- COLETA DE DADOS ---

// 1. Totais Gerais
$total_ramais = $link->query("SELECT COUNT(*) FROM lista")->fetch_row()[0];
$total_secretarias = $link->query("SELECT COUNT(*) FROM secretarias")->fetch_row()[0];
$total_usuarios = $link->query("SELECT COUNT(*) FROM usuarios")->fetch_row()[0];

// 2. Uso do Disco (Linux/Windows safe)
$path = "."; 
$disk_total = disk_total_space($path);
$disk_free = disk_free_space($path);
$disk_used = $disk_total - $disk_free;
$disk_percent = round(($disk_used / $disk_total) * 100);

// 3. Dados para Gráfico: Ramais por Secretaria
$sql_sec = "SELECT s.secretaria, COUNT(l.id_lista) as qtd 
            FROM lista l 
            JOIN secretarias s ON l.secretaria = s.id_secretaria 
            GROUP BY s.id_secretaria 
            ORDER BY qtd DESC";
$res_sec = $link->query($sql_sec);
$labels_sec = [];
$data_sec = [];
while($row = $res_sec->fetch_assoc()) {
    $labels_sec[] = $row['secretaria']; 
    $data_sec[] = $row['qtd'];
}

// 4. Dados para Gráfico: Top 5 Ramais Mais Acessados
$sql_top = "SELECT nome, acessos FROM lista ORDER BY acessos DESC LIMIT 5";
$res_top = $link->query($sql_top);
$labels_top = [];
$data_top = [];
while($row = $res_top->fetch_assoc()) {
    $labels_top[] = explode(' ', $row['nome'])[0]; 
    $data_top[] = $row['acessos'];
}

// 5. NOVO: Dados para Gráfico de Linha (Acessos Últimos 5 Dias)
// Precisamos garantir que os ultimos 5 dias apareçam, mesmo que zerados.
$labels_line = [];
$data_line = [];

// Busca dados do banco
$sql_line = "SELECT data, acessos FROM stats_diario WHERE data >= DATE_SUB(CURDATE(), INTERVAL 4 DAY)";
$res_line = $link->query($sql_line);
$db_stats = [];
if($res_line) {
    while($row = $res_line->fetch_assoc()) {
        $db_stats[$row['data']] = $row['acessos'];
    }
}

// Loop para gerar os últimos 5 dias corretamente
for ($i = 4; $i >= 0; $i--) {
    $dia_loop = date('Y-m-d', strtotime("-$i days"));
    $labels_line[] = date('d/m', strtotime($dia_loop)); // Formato dia/mês
    $data_line[] = isset($db_stats[$dia_loop]) ? $db_stats[$dia_loop] : 0; // Se não tiver no banco, põe 0
}

// 6. Info do Sistema
$php_version = phpversion();
$db_version = $link->server_info;

?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Lista Telefônica</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: var(--bs-body-bg); }
        .card-stats { border-left: 5px solid var(--bs-success); transition: transform 0.2s; }
        .card-stats:hover { transform: translateY(-5px); }
        .chart-container { position: relative; height: 300px; width: 100%; }
        /* Altura menor para o grafico de linha para caber bem */
        .chart-container-sm { position: relative; height: 200px; width: 100%; } 
        .system-badge { font-family: monospace; font-size: 0.9em; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fa fa-phone-square me-2"></i> LISTA TELEFÔNICA
            </a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3 small d-none d-md-block">Painel de Controle Admin</span>
                <a href="index.php" class="btn btn-outline-light btn-sm"><i class="fa fa-arrow-left me-1"></i> Voltar</a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <h2 class="mb-4 fw-light"><i class="fa fa-chart-line me-2 text-success"></i> Dashboard Administrativo</h2>

        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm border-0 bg-body-tertiary card-stats h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted text-uppercase mb-1">Ramais Ativos</h6>
                                <h2 class="fw-bold mb-0"><?php echo $total_ramais; ?></h2>
                            </div>
                            <i class="fa fa-address-book fa-3x text-success opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0 bg-body-tertiary card-stats h-100" style="border-left-color: #0d6efd;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted text-uppercase mb-1">Secretarias</h6>
                                <h2 class="fw-bold mb-0"><?php echo $total_secretarias; ?></h2>
                            </div>
                            <i class="fa fa-building fa-3x text-primary opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>

            <?php 
                // Soma total de acessos da tabela lista (acumulado)
                $total_cliques = $link->query("SELECT SUM(acessos) FROM lista")->fetch_row()[0] ?? 0;
            ?>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 bg-body-tertiary card-stats h-100" style="border-left-color: #ffc107;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted text-uppercase mb-1">Total Consultas</h6>
                                <h2 class="fw-bold mb-0"><?php echo $total_cliques; ?></h2>
                            </div>
                            <i class="fa fa-mouse-pointer fa-3x text-warning opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0 bg-body-tertiary card-stats h-100" style="border-left-color: #dc3545;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="w-100">
                                <h6 class="text-muted text-uppercase mb-1">Uso de Disco</h6>
                                <div class="d-flex align-items-end justify-content-between">
                                    <h4 class="fw-bold mb-0"><?php echo $disk_percent; ?>%</h4>
                                    <small class="text-muted"><?php echo round($disk_free/1024/1024/1024, 2); ?> GB Livres</small>
                                </div>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo $disk_percent; ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 bg-body-tertiary mb-4">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="mb-0"><i class="fa fa-trophy me-2 text-warning"></i> Ramais Mais Consultados</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="chartTopAccess"></canvas>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 bg-body-tertiary">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="mb-0"><i class="fa fa-calendar-check me-2 text-info"></i> Acessos ao Sistema (Últimos 5 dias)</h5>
                    </div>
                    
                    <div class="card-body">
                        <div class="chart-container-sm">
                            <canvas id="chartDailyAccess"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 bg-body-tertiary h-100">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="mb-0"><i class="fa fa-pie-chart me-2 text-primary"></i> Distribuição dos ramais</h5>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div style="height: 400px; width: 100%;">
                            <canvas id="chartSecretarias"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 bg-body-tertiary">
            <div class="card-header bg-transparent border-0 pt-3 px-4">
                <h6 class="text-uppercase fw-bold text-muted mb-0"><i class="fa fa-server me-2"></i> Status do Servidor</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4 mb-3 mb-md-0 border-end border-secondary border-opacity-25">
                        <div class="text-muted small mb-1">Versão PHP</div>
                        <span class="badge bg-primary system-badge p-2">v<?php echo $php_version; ?></span>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0 border-end border-secondary border-opacity-25">
                        <div class="text-muted small mb-1">Banco de Dados</div>
                        <span class="badge bg-info text-dark system-badge p-2"><?php echo $db_version; ?></span>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Servidor Web</div>
                        <span class="badge bg-secondary system-badge p-2"><?php echo $_SERVER['SERVER_SOFTWARE']; ?></span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // CONFIGURAÇÃO DOS GRÁFICOS
        
        // Dados vindos do PHP
        const dataSec = <?php echo json_encode($data_sec); ?>;
        const labelsSec = <?php echo json_encode($labels_sec); ?>;
        const dataTop = <?php echo json_encode($data_top); ?>;
        const labelsTop = <?php echo json_encode($labels_top); ?>;
        
        // Novos dados para o gráfico de linha
        const dataLine = <?php echo json_encode($data_line); ?>;
        const labelsLine = <?php echo json_encode($labels_line); ?>;

        const colors = ['#198754', '#0d6efd', '#ffc107', '#dc3545', '#6610f2', '#fd7e14', '#20c997'];

        // 1. Gráfico de Rosca (Secretarias)
        new Chart(document.getElementById('chartSecretarias'), {
            type: 'doughnut',
            data: {
                labels: labelsSec,
                datasets: [{
                    data: dataSec,
                    backgroundColor: colors,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { color: '#adb5bd', usePointStyle: true } }
                }
            }
        });

        // 2. Gráfico de Barras (Ramais mais acessados)
        new Chart(document.getElementById('chartTopAccess'), {
            type: 'bar',
            data: {
                labels: labelsTop,
                datasets: [{
                    label: 'Visualizações Totais',
                    data: dataTop,
                    backgroundColor: '#ffc107',
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#adb5bd' } },
                    x: { grid: { display: false }, ticks: { color: '#adb5bd' } }
                },
                plugins: { legend: { display: false } }
            }
        });

        // 3. NOVO: Gráfico de Linha (Acessos Diários - Últimos 5 dias)
        new Chart(document.getElementById('chartDailyAccess'), {
            type: 'line',
            data: {
                labels: labelsLine,
                datasets: [{
                    label: 'Visitas ao Site',
                    data: dataLine,
                    borderColor: '#0dcaf0', // Azul ciano
                    backgroundColor: 'rgba(13, 202, 240, 0.1)', // Fundo transparente
                    borderWidth: 3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#0dcaf0',
                    pointRadius: 5,
                    fill: true, // Preenche abaixo da linha
                    tension: 0.4 // Curva suave na linha
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { color: 'rgba(255,255,255,0.05)' }, 
                        ticks: { color: '#adb5bd', precision: 0 } // Precision 0 para não mostrar 1.5 acessos
                    },
                    x: { 
                        grid: { display: false }, 
                        ticks: { color: '#adb5bd' } 
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                }
            }
        });
    </script>
</body>
</html>