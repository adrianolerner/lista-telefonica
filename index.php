<?php
session_start();

//Verificação de IP
//$ip = $_SERVER['HTTP_X_REAL_IP'];
$ipaddress = "172.16.0.10";
//$ipaddress = strstr($ip, ',', true);

//Nome do órgão (alterar com seu orgão)
$orgao = "PREFEITURA DE CASTRO";

// Checagens de usuário
include('config.php');

$useradmin = @$_SESSION['usuario'];

if ($stmt = mysqli_prepare($link, "SELECT admin FROM usuarios WHERE usuario = ?")) {
    mysqli_stmt_bind_param($stmt, "s", $useradmin);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $admin);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
}

$adminarray = ['admin' => $admin];

if ($stmtBanner = mysqli_prepare($link, "SELECT banner FROM banner WHERE id_banner = ?")) {
    $id_banner = 1;
    mysqli_stmt_bind_param($stmtBanner, "i", $id_banner);
    mysqli_stmt_execute($stmtBanner);
    mysqli_stmt_bind_result($stmtBanner, $banner);
    mysqli_stmt_fetch($stmtBanner);
    mysqli_stmt_close($stmtBanner);
}

$bannerarray = ['banner' => $banner];
?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="dark">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="theme-color" content="#576b37" />
    <title>LISTA TELEFÔNICA - <?php echo $orgao; ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: var(--bs-body-bg); }
        .navbar-brand img { height: 40px; margin-right: 10px; }
        
        /* Banner Rolante */
        .news-ticker-container {
            background: var(--bs-tertiary-bg);
            border-bottom: 1px solid var(--bs-border-color);
            overflow: hidden;
            white-space: nowrap;
            height: 40px;
            display: flex;
            align-items: center;
        }
        .news-ticker-text {
            display: inline-block;
            padding-left: 100%;
            animation: ticker 25s linear infinite;
            font-weight: 500;
            color: var(--bs-emphasis-color);
        }
        @keyframes ticker {
            0% { transform: translate3d(0, 0, 0); }
            100% { transform: translate3d(-100%, 0, 0); }
        }

        /* --- AJUSTES DE ESTABILIDADE DA TABELA --- */
        
        [data-bs-theme="dark"] .table thead th {
            background-color: #2b3035;
            color: #fff;
            border-bottom: 2px solid #495057;
        }
        
        /* Define uma altura fixa para as células para evitar pulos */
        table.dataTable tbody td {
            vertical-align: middle;
            height: 60px; /* Altura suficiente para 2 linhas + padding */
        }

        /* CLASSE MÁGICA: Limita a 2 linhas e põe reticências (...) */
        .text-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2; /* Número máximo de linhas */
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.3; /* Espaçamento entre linhas */
            max-height: 2.6em; /* Garante o corte visual */
        }

        td a { text-decoration: none; }

        /* Botões +/- */
        table.dataTable.dtr-inline.collapsed > tbody > tr > td.dtr-control:before, 
        table.dataTable.dtr-inline.collapsed > tbody > tr > th.dtr-control:before {
            content: "+";
            font-family: "Courier New", Courier, monospace;
            font-weight: 900;
            font-size: 18px;
            background-color: transparent !important;
            border: 2px solid var(--bs-success);
            color: var(--bs-success);
            border-radius: 4px;
            box-shadow: none !important;
            width: 20px;
            height: 20px;
            line-height: 16px;
            text-align: center;
            top: 50%;
            transform: translateY(-50%);
            margin-right: 10px;
        }
        table.dataTable.dtr-inline.collapsed > tbody > tr.parent > td.dtr-control:before, 
        table.dataTable.dtr-inline.collapsed > tbody > tr.parent > th.dtr-control:before {
            content: "-";
            border-color: var(--bs-secondary);
            color: var(--bs-secondary);
            line-height: 14px;
        }

        /* Barra de pesquisa moderna */
        .modern-search-wrapper { transition: all 0.3s ease; }
        .modern-search-wrapper:focus-within { transform: translateY(-2px); box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important; }
        .search-icon-box { background-color: var(--bs-body-bg); border-color: var(--bs-border-color); }
        #customSearchBox { background-color: var(--bs-body-bg); border-color: var(--bs-border-color); font-size: 1.1rem; }
        #customSearchBox:focus { box-shadow: none; border-color: var(--bs-border-color); }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm mb-0">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <i class="fa fa-phone-square me-2"></i> LISTA TELEFÔNICA
            </a>
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-light btn-sm me-2" id="themeToggle" title="Alternar Tema">
                    <i class="fa fa-moon"></i>
                </button>
            </div>
        </div>
    </nav>

    <?php if(!empty($bannerarray["banner"])): ?>
    <div class="news-ticker-container">
        <div class="container-fluid">
            <div class="news-ticker-text">
                <i class="fa fa-bullhorn me-2 text-warning"></i> <?php echo htmlspecialchars($bannerarray["banner"]); ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="container my-4">
        
        <div class="row mb-4 align-items-center">
            <div class="col-md-8">
                <h2 class="fw-light text-uppercase mb-0 fs-4"><?php echo $orgao; ?></h2>
                <p class="text-muted mb-0 small">
                    Olá, <strong><?php echo !empty($useradmin) ? htmlspecialchars($useradmin) : "Visitante"; ?></strong>.
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-2 mt-md-0">
                <?php if (fnmatch("172.16.0.*", $ipaddress) && empty($useradmin)) { ?>
                    <a href="login.php" class="btn btn-primary btn-sm" title="Acesso para ajustes na lista"><i class="fa fa-sign-in-alt me-1"></i> Login Administrativo</a>
                <?php } ?>
            </div>
        </div>

        <?php if (fnmatch("172.16.0.*", $ipaddress) && !empty($useradmin)) { ?>
            <div class="card mb-4 border-primary border-opacity-25 bg-primary bg-opacity-10">
                <div class="card-body py-3">
                    <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-md-start align-items-center">
                        <span class="badge bg-primary me-2 p-2"><i class="fa fa-cogs"></i> Painel</span>
                        <a href="create.php" class="btn btn-success btn-sm" title="Adicionar novo ramal à lista"><i class="fa fa-plus me-1"></i> Novo Ramal</a>
                        <?php if ($adminarray['admin'] == "s") { ?>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="usuarios/index.php" class="btn btn-outline-primary" title="Gerenciar usuários do sistema"><i class="fa fa-users"></i> Usuários</a>
                                <a href="secretarias/index.php" class="btn btn-outline-primary" title="Gerenciar secretarias cadastradas"><i class="fa fa-building"></i> Secretarias</a>
                                <a href="update_banner.php?id_banner=1" class="btn btn-outline-primary" title="Gerenciar banner cadastrado"><i class="fa fa-bullhorn"></i> Banner</a>
                            </div>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="importar.php" class="btn btn-outline-secondary" title="Importar lista de ramais em CSV"><i class="fa fa-file-upload"></i></a>
                                <a href="exportar_csv.php" class="btn btn-outline-secondary" title="Exportar lista de ramais para CSV"><i class="fa fa-file-download"></i></a>
                                <a href="historico_alteracoes.php" class="btn btn-outline-secondary" title="Exibir histórico de alterações em ramais"><i class="fa fa-history"></i></a>
                            </div>
                            <a href="delete_all.php" class="btn btn-danger btn-sm" onclick="return confirm('Apagar TUDO?');" title="Apagar todos os registros da lista (Cuidado!)"><i class="fa fa-trash-alt"></i></a>
                        <?php } ?>
                        <div class="ms-auto border-start ps-2">
                            <a href="senha.php?user=<?php echo htmlspecialchars($useradmin); ?>" class="btn btn-warning btn-sm text-dark" title="Alterar senha do usuário"><i class="fa fa-key"></i></a>
                            <a href="logout.php" class="btn btn-secondary btn-sm" title="Sair do sistema"><i class="fa fa-sign-out-alt"></i> Sair</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>

        <div class="row justify-content-center mb-4 mt-2">
            <div class="col-md-8 col-lg-7">
                <div class="input-group input-group-lg shadow-sm rounded-pill overflow-hidden modern-search-wrapper border">
                    <span class="input-group-text border-0 ps-4 search-icon-box">
                        <i class="fa fa-search text-secondary"></i>
                    </span>
                    <input type="text" id="customSearchBox" class="form-control border-0 py-3" 
                           placeholder="Pesquise por nome, setor ou ramal..." aria-label="Pesquisar" title="Pesquisar informações da lista">
                    <span class="input-group-text border-0 pe-4 search-icon-box">
                        <i class="fa fa-filter text-muted opacity-50" style="font-size: 0.8em;"></i>
                    </span>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <?php
                $sql = "SELECT l.id_lista, l.nome, l.ramal, l.email, l.setor, s.secretaria FROM lista l JOIN secretarias s ON l.secretaria = s.id_secretaria";
                
                if ($result = mysqli_query($link, $sql)) {
                    if (mysqli_num_rows($result) > 0) {
                ?>
                    <div class="p-3">
                        <table id="userTable" class="table table-hover align-middle w-100 border-bottom">
                            <thead>
                                <tr>
                                    <th data-priority="4" style="width: 20%;">SECRETARIA</th>
                                    <th data-priority="5" style="width: 20%;">SETOR</th>
                                    <th data-priority="1" style="width: 25%;">NOME</th>
                                    <th data-priority="2" class="text-nowrap" style="width: 10%;">RAMAL</th>
                                    <th data-priority="6" style="width: 15%;">E-MAIL</th>
                                    <th data-priority="3" class="text-end" style="width: 10%;">AÇÃO</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                while ($row = mysqli_fetch_array($result)) {
                                    $secretaria = htmlspecialchars($row['secretaria']);
                                    $setor = htmlspecialchars($row['setor']);
                                    $nome = htmlspecialchars($row['nome']);
                                    $email = htmlspecialchars($row['email']);

                                    echo "<tr>";
                                    
                                    echo "<td class='fw-semibold small'>";
                                    echo "<div class='text-clamp-2' title='$secretaria'>$secretaria</div>";
                                    echo "</td>";
                                    
                                    echo "<td>";
                                    echo "<span class='badge bg-secondary bg-opacity-25 text-body border text-wrap text-start w-100'>";
                                    echo "<div class='text-clamp-2' title='$setor'>$setor</div>";
                                    echo "</span>";
                                    echo "</td>";
                                    
                                    echo "<td class='fw-bold'>";
                                    echo "<div class='text-clamp-2' title='$nome'>$nome</div>";
                                    echo "</td>";
                                    
                                    echo "<td class='text-primary fw-bold text-nowrap'><a href='tel:" . htmlspecialchars(preg_replace('/\D/', '', $row['ramal'])) . "'>" . htmlspecialchars($row['ramal']) . "</a></td>";
                                    
                                    echo "<td class='small text-muted'>";
                                    echo "<div class='text-clamp-2' title='$email'><a href='mailto:$email'>$email</div>";
                                    echo "</td>";
                                    
                                    echo "<td class='text-end text-nowrap'>";
                                    echo "<a href='read.php?id_lista=" . urlencode($row['id_lista']) . "' class='btn btn-sm btn-info text-white me-1' title='Ver detalhes deste ramal'><i class='fa fa-eye'></i></a>";
                                    
                                    if (!empty($useradmin)) {
                                        echo "<a href='update.php?id_lista=" . urlencode($row['id_lista']) . "' class='btn btn-sm btn-warning text-dark me-1' title='Atualizar informações deste ramal'><i class='fa fa-pen'></i></a>";
                                        echo "<a href='delete.php?id_lista=" . urlencode($row['id_lista']) . "' class='btn btn-sm btn-danger' title='Apagar este ramal'><i class='fa fa-trash'></i></a>";
                                    }
                                    echo "</td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                <?php
                    mysqli_free_result($result);
                    } else {
                        echo '<div class="alert alert-info m-3">Nenhum registro encontrado.</div>';
                    }
                } else {
                    echo '<div class="alert alert-danger m-3">Erro ao conectar com o banco de dados.</div>';
                }
                mysqli_close($link);
                ?>
            </div>
        </div>
    </div>

    <footer class="bg-body-tertiary text-center text-lg-start mt-5 border-top">
        <div class="container p-4">
            <div class="row align-items-center">
                <div class="col-lg-6 col-md-12 mb-4 mb-md-0 text-center text-lg-start">
                    <h6 class="text-uppercase fw-bold mb-2">Links Úteis</h6>
                    <div class="d-flex gap-2 flex-wrap justify-content-center justify-content-lg-start">
                        <?php if (empty($useradmin)) { ?>
                            <a href="gerapdf.php" class="btn btn-outline-secondary btn-sm"><i class="fa fa-file-pdf me-1"></i> Gerar PDF</a>
                            <a href="https://castro.pr.gov.br/pontos/" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="fa fa-map-marked-alt me-1"></i> Mapa</a>
                        <?php } ?>
                        <a href="https://castro.atende.net" target="_blank" class="btn btn-outline-primary btn-sm"><i class="fa fa-external-link-alt me-1"></i> Portal</a>
                        <a href="sobre.php" class="btn btn-outline-secondary btn-sm"><i class="fa fa-info-circle me-1"></i> Sobre</a>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12 mb-4 mb-md-0 text-center text-lg-end">
                    <img src="img/logo2.png" alt="Logo Prefeitura" style="max-height: 50px; opacity: 0.8;">
                    <p class="small text-muted mt-2 mb-0">
                        &copy; <?php echo date("Y"); ?> Prefeitura Municipal de Castro<br>
                        <span class="text-opacity-50">SMCTI - Adriano Lerner Biesek</span>
                    </p>
                    <small class="text-muted" style="font-size: 0.7em;">IP: <?php echo htmlspecialchars($ipaddress); ?></small>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function () {
            var table = $('#userTable').DataTable({
                responsive: true,
                order: [[2, 'asc']],
                columnDefs: [
                    { responsivePriority: 1, targets: 2 },
                    { responsivePriority: 2, targets: 3 },
                    { responsivePriority: 3, targets: -1 }
                ],
                language: {
                    "sEmptyTable":   "Nenhum registro encontrado",
                    "sInfo":         "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                    "sInfoEmpty":    "Mostrando 0 até 0 de 0 registros",
                    "sInfoFiltered": "(Filtrados de _MAX_ registros)",
                    "sInfoPostFix":  "",
                    "sInfoThousands": ".",
                    "sLengthMenu":   "_MENU_ resultados por página",
                    "sLoadingRecords": "Carregando...",
                    "sProcessing":   "Processando...",
                    "sZeroRecords":  "Nenhum registro encontrado",
                    "oPaginate": { "sNext": "Próximo", "sPrevious": "Anterior", "sFirst": "Primeiro", "sLast": "Último" }
                },
                dom: "<'row'<'col-sm-12'l>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            });
            // Evento disparado ao trocar de página
            table.on('page.dt', function () {
                // Verifica se a quantidade de registros por página é maior que 10
                var pageLength = table.page.len();
                
                if (pageLength > 10) {
                    $('html, body').animate({
                        scrollTop: $(".card").offset().top - 20
                    }, 'fast'); // 'fast' para uma subida suave, ou 0 para instantâneo
                }
            });
            $('#customSearchBox').on('keyup', function () {
                table.search(this.value).draw();
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