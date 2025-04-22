<?php
// Mecanismo de login
session_start();

//Verificação de IP
//$ip = $_SERVER['HTTP_X_REAL_IP'];
$ipaddress = "172.16.0.10";
//$ipaddress = strstr($ip, ',', true);

//Nome do órgão (alterar com seu orgão)
$orgao = "DA PREFEITURA DE CASTRO";

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
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="theme-color" content="#576b37" />
    <title>LISTA TELEFÔNICA PREFEITURA DE CASTRO</title>
    <link href="css/datatables.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <style>
        :root {
            --bg-light: #FFFFFF;
            --bg-dark: #1C1C1C;
            --text-light: #000000;
            --text-dark: #FFFFFF;
            --dt-bg-light: #f8f9fa;
            --dt-bg-dark: #2d2d2d;
            --dt-text-light: #495057;
            --dt-text-dark: #e9ecef;
            --dt-border-light: #dee2e6;
            --dt-border-dark: #444;
            --dt-hover-light: #e2e2e2;
            --dt-hover-dark: #3a3a3a;
            --dt-header-light: #f0f0f0;
            --dt-header-dark: #4F4F4F;
            --dt-paginate-bg-light: #ffffff;
            --dt-paginate-bg-dark: #2d2d2d;
            --dt-select-bg-light: #ffffff;
            --dt-select-bg-dark: #2d2d2d;
            --dt-select-text-light: #495057;
            --dt-select-text-dark: #e9ecef;
            --dt-dropdown-bg-light: #ffffff;
            --dt-dropdown-bg-dark: #3a3a3a;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-dark);
            margin: 0;
            font-family: Arial, sans-serif;
            transition: background-color 0.3s, color 0.3s;
        }

        body.light-theme {
            background-color: var(--bg-light);
            color: var(--text-light);
        }

        section {
            width: 100%;
            max-width: 1200px;
            margin: auto;
            padding: 10px;
        }

        #userTable {
            width: 100% !important;
            table-layout: auto;
        }

        #userTable th,
        #userTable td {
            border: 1px solid var(--dt-border-dark);
            text-align: center;
            font-size: 14px;
            word-break: break-word;
            color: var(--dt-text-dark);
            background-color: var(--bg-dark);
        }

        body.light-theme #userTable th,
        body.light-theme #userTable td {
            color: var(--dt-text-light);
            background-color: var(--bg-light);
            border-color: var(--dt-border-light);
        }

        #userTable thead th {
            background-color: var(--dt-header-dark) !important;
            color: var(--text-dark) !important;
        }

        body.light-theme #userTable thead th {
            background-color: var(--dt-header-light) !important;
            color: var(--text-light) !important;
        }

        #userTable tbody tr:hover {
            background-color: var(--dt-hover-dark);
            transition: background-color 0.3s ease;
        }

        body.light-theme #userTable tbody tr:hover {
            background-color: var(--dt-hover-light);
        }

        /* DataTables Styles */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: var(--text-dark) !important;
            background-color: var(--dt-paginate-bg-dark) !important;
            border: 1px solid var(--dt-border-dark) !important;
            padding: 0.3em 0.65em;
            margin-left: 2px;
            border-radius: 4px;
        }

        body.light-theme .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: var(--text-light) !important;
            background-color: var(--dt-paginate-bg-light) !important;
            border: 1px solid var(--dt-border-light) !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background-color: var(--dt-hover-dark) !important;
            color: var(--text-dark) !important;
        }

        body.light-theme .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background-color: var(--dt-hover-light) !important;
            color: var(--text-light) !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background-color: #576b37 !important;
            color: white !important;
            border: none !important;
        }

        /* Seletor de quantidade de registros - Corrigido para tema escuro */
        .dataTables_wrapper .dt-input {
            color: var(--dt-select-text-dark) !important;
            background-color: var(--dt-select-bg-dark) !important;
            border-color: var(--dt-border-dark) !important;
        }

        body.light-theme .dataTables_wrapper .dt-input {
            color: var(--dt-select-text-light) !important;
            background-color: var(--dt-select-bg-light) !important;
            border-color: var(--dt-border-light) !important;
        }

        /* Estilo para as opções do dropdown no tema escuro */
        .dataTables_wrapper .dt-input {
            background-color: var(--dt-dropdown-bg-dark) !important;
            color: var(--dt-select-text-dark) !important;
        }

        body.light-theme .dataTables_wrapper .dt-input {
            background-color: var(--dt-dropdown-bg-light) !important;
            color: var(--dt-select-text-light) !important;
        }

        .headcontainer {
            text-align: center;
            margin: 10px 0;
        }

        h2.h2 {
            font-size: 1.5rem;
        }

        .scrolling-container {
            width: 100%;
            overflow: hidden;
        }

        .scrolling-text {
            display: inline-block;
            white-space: nowrap;
            padding-left: 100%;
            animation: scroll 12s linear infinite;
        }

        @keyframes scroll {
            from {
                transform: translateX(0);
            }

            to {
                transform: translateX(-100%);
            }
        }

        .btn {
            margin: 5px 2px;
            font-size: 0.9rem;
        }

        footer img {
            max-width: 100px;
            height: auto;
            margin: 5px;
        }

        .theme-toggle {
            position: fixed;
            top: 10px;
            right: 10px;
            background-color: #666;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            z-index: 9999;
        }

        @media screen and (max-width: 768px) {

            #userTable th,
            #userTable td {
                font-size: 12px;
                padding: 4px;
            }

            .btn {
                display: block;
                width: 100%;
                margin-bottom: 5px;
            }
        }
    </style>
</head>

<body>
    <button class="theme-toggle" onclick="toggleTheme()">🌙</button>
    <header>
	<section>
            <div class="headcontainer">
                <h2 class="h2">LISTA TELEFÔNICA <?php echo $orgao; ?></h2>
                <p>Seja bem-vindo a lista telefônica <?php echo !empty($useradmin) ? $useradmin : "visitante"; ?></p>
                <p><?php echo !empty($useradmin) ? "Use as opções abaixo para gerenciar a lista telefônica." : ""; ?>
                </p>
                <?php if (fnmatch("172.16.0.*", $ipaddress)) { ?>
                    <?php if (!empty($useradmin)) { ?>
                        <div>
                            <a href="logout.php" class="btn btn-secondary" title='Sair do Sistema'><i class="fa fa-sign-out"></i> Sair</a>
                            <a href="senha.php?user=<?php echo htmlspecialchars($useradmin); ?>" class="btn btn-primary" title='Alterar Senha'><i
                                    class="fa fa-lock"></i> Senha</a>
                            <?php if ($adminarray['admin'] == "s") { ?>
                                <a href="usuarios/index.php" class="btn btn-primary"><i class="fa fa-users" title='Gerenciar Usuarios'></i> Usuários</a>
                                <a href="secretarias/index.php" class="btn btn-primary"><i class="fa fa-home" title='Gerenciar Secretarias'></i> Secretarias</a>
                                <a href="update_banner.php?id_banner=1" class="btn btn-primary"><i class="fa fa-info-circle" title='Trocar Banner'></i>
                                    Banner</a>
                                <a href="delete_all.php" class="btn btn-danger"><i class="fa fa-exclamation-triangle" title='Limpar Lista'></i> Apagar
                                    Tudo</a>
                                <a href="importar.php" class="btn btn-success"><i class="fa fa-upload" title='Importar Lista'></i> Importar CSV</a>
                            <?php } ?>
                            <a href="create.php" class="btn btn-success"><i class="fa fa-plus" title='Incluir Ramal'></i> Adicionar Ramal</a>
                        </div>
                    <?php } else { ?>
                        <a href="login.php" class="btn btn-primary" title='Entrar no Sistema'><i class="fa fa-sign-in"></i> LOGIN</a>
                    <?php } ?>
                <?php } ?>
            </div>
        </section>
    </header>
    <section>
        <div class="scrolling-container">
            <div class="scrolling-text"><?php echo htmlspecialchars($bannerarray["banner"]); ?></div>
        </div>
    </section>
    <section>
        <?php
        require_once "config.php";
        $sql = "SELECT l.id_lista, l.nome, l.ramal, l.email, l.setor, s.secretaria FROM lista l JOIN secretarias s ON l.secretaria = s.id_secretaria";
        if ($result = mysqli_query($link, $sql)) {
            if (mysqli_num_rows($result) > 0) {
                echo '<div class="table-responsive"><table id="userTable" class="table table-bordered dt-input">';
                echo '<thead><tr><th><u>SECRETARIA</u></th><th><u>SETOR</u></th><th><u>NOME</u></th><th><u>RAMAL</u></th><th><u>E-MAIL</u></th><th><u>AÇÃO</u></th></tr></thead><tbody>';
                while ($row = mysqli_fetch_array($result)) {
                    echo "<tr><td>" . htmlspecialchars($row['secretaria']) . "</td><td>" . htmlspecialchars($row['setor']) . "</td><td>" . htmlspecialchars($row['nome']) . "</td><td>" . htmlspecialchars($row['ramal']) . "</td><td>" . htmlspecialchars($row['email']) . "</td>";
                    echo "<td><a href='read.php?id_lista=" . urlencode($row['id_lista']) . "'><span class='fa fa-eye' title='Ver Item'></span></a>&nbsp; ";
                    if (!empty($useradmin)) {
                        echo "<a href='update.php?id_lista=" . urlencode($row['id_lista']) . "'><span class='fa fa-pencil' title='Editar Item'></span></a>&nbsp; ";
                        echo "<a href='delete.php?id_lista=" . urlencode($row['id_lista']) . "'><span class='fa fa-trash' title='Excluir Item'></span></a></td>";
                    }
                    echo "</tr>";
                }
                echo "</tbody></table></div>";
                mysqli_free_result($result);
            } else {
                echo '<div class="alert alert-danger"><em>Não foram encontrados registros.</em></div>';
            }
        } else {
            echo "Oops! Algo saiu errado. Tente novamente mais tarde.";
        }
        mysqli_close($link);
        ?>
    </section>
    <footer>
        <section>
            <div class="maincontainer text-center">
                <br />
                <?php if (!empty($useradmin)) {
                    echo "<a href='exportar_csv.php' class='btn btn-primary' title='Exportar Lista Para CSV'><i class='fa fa-download'></i> GERAR CSV</a> ";
                    if ($admin === "s")
                        echo "<a href='historico_alteracoes.php' class='btn btn-primary' title='Ver Logs Do Sistema'><i class='fa fa-search'></i> VER LOGS</a> ";
                } else {
                    echo "<a href='gerapdf.php' class='btn btn-primary' title='Gerar Lista em PDF'><i class='fa fa-download'></i> GERAR PDF</a>";
                } ?>
		<p><br /><a href="https://castro.atende.net" title="Acessar o portal da Prefeitura"><img src="img/logo2.png" /></a></p>
                <p><a href="sobre.php" title="Seu IP é: <?php echo htmlspecialchars($ipaddress); ?>">©<?php echo date("Y"); ?> Prefeitura
                        Municipal de Castro | Adriano Lerner Biesek</a></p>
            </div>
        </section>
    </footer>
    <script src="js/jquery-3.7.0.min.js"></script>
    <script src="js/datatables.min.js"></script>
    <script>
        $(document).ready(function () {
            // Inicializar DataTables
            $('#userTable').DataTable({
                responsive: true,
                language: {
                    "emptyTable": "Sem dados disponíveis",
                    "info": "Mostrando _START_ até _END_ de _TOTAL_ registros",
                    "infoEmpty": "Mostrando 0 até 0 de 0 registros",
                    "lengthMenu": "Mostrando _MENU_ registros",
                    "loadingRecords": "Carregando...",
                    "search": "Procurar:",
                    "zeroRecords": "Nenhum registro encontrado",
                    "paginate": {
                        "first": "Primeiro",
                        "last": "Último",
                        "next": "Próximo",
                        "previous": "Anterior"
                    }
                }
            });

            // Aplicar tema quando o DataTables for inicializado
            applyDataTablesTheme();
        });

        function applyDataTablesTheme() {
            const isLightTheme = document.body.classList.contains('light-theme');

            // Forçar a atualização do select
            $('.dt-input').each(function () {
                const temp = this.style.display;
                this.style.display = 'none';
                setTimeout(() => {
                    this.style.display = temp;
                }, 10);
            });
        }

        function toggleTheme() {
            const body = document.body;
            const toggleBtn = document.querySelector('.theme-toggle');
            body.classList.toggle('light-theme');

            if (body.classList.contains('light-theme')) {
                toggleBtn.textContent = '🌞';
                localStorage.setItem('theme', 'light');
            } else {
                toggleBtn.textContent = '🌙';
                localStorage.setItem('theme', 'dark');
            }

            // Reaplicar tema do DataTables
            applyDataTablesTheme();
        }

        // Carregar tema salvo
        document.addEventListener('DOMContentLoaded', function () {
            const savedTheme = localStorage.getItem('theme');
            const toggleBtn = document.querySelector('.theme-toggle');

            if (savedTheme === 'light') {
                document.body.classList.add('light-theme');
                toggleBtn.textContent = '🌞';
            }
        });
    </script>
</body>

</html>
