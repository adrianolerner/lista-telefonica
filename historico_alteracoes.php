<?php
// Mecanismo de login
include('verifica_login.php');

// Inclui arquivo de configuração
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
    <html lang="pt-br" class="dark" data-bs-theme="dark">

    <head>
        <meta charset="UTF-8">
        <title>Histórico de Alterações</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="theme-color" content="#576b37" />
        <link rel="stylesheet" href="css/bootstrap.min.css">
        <link rel="stylesheet" href="css/font-awesome.min.css">
        <link rel="stylesheet" href="css/datatables.min.css">
        <style>
            body {
                background-color: #1C1C1C;
                color: white;
                margin: 0;
            }

            section {
                width: fit-content;
                margin: auto;
                padding: 10px;
            }

            #logTable {
                width: 100%;
                margin-top: 20px;
                border-collapse: collapse;
            }

            #logTable th,
            #logTable td {
                border: 1px solid #ccc;
                text-align: center;
                padding-left: 35px;
                padding-right: 35px;
                padding-top: 0;
                padding-bottom: 0;
            }

            #logTable thead {
                background: #4F4F4F;
            }

            .headcontainer {
                display: flex;
                justify-content: center;
                align-items: center;
                margin-bottom: 10px;
            }

            form.filter-form {
                display: flex;
                gap: 10px;
                justify-content: center;
                margin-bottom: 20px;
                flex-wrap: wrap;
            }
        </style>
    </head>

    <body>
        <header>
            <?php if (isset($_SESSION['mensagem'])): ?>
                <div class="alert alert-info text-center">
                    <?php echo $_SESSION['mensagem'];
                    unset($_SESSION['mensagem']); ?>
                </div>
            <?php endif; ?>

            <section>
                <div class="headcontainer">
                    <h2>HISTÓRICO DE ALTERAÇÕES</h2>
                </div>
                <div class="headcontainer">
                    <a href="index.php" class="btn btn-secondary ml-2">← Voltar</a>
                    <form method="POST" action="limpar_log.php"
                        onsubmit="return confirm('Tem certeza que deseja apagar TODO o histórico? Esta ação não pode ser desfeita.');"
                        style="margin-left: 10px;">
                        <button type="submit" class="btn btn-danger">🗑 Limpar Histórico</button>
                    </form>
                </div>

            </section>
        </header>

        <section>
            <div>
                <?php
                $sql = "SELECT id, acao, id_lista, ramal, usuario, ip, datahora
                    FROM log_alteracoes WHERE 1=1";

                if ($result = mysqli_query($link, $sql)) {
                    if (mysqli_num_rows($result) > 0) {
                        echo '<table id="logTable" class="table table-dark table-striped">';
                        echo '<thead>';
                        echo '<tr>';
                        echo '<th>ID</th>';
                        echo '<th>USUÁRIO</th>';
                        echo '<th>IP</th>';
                        echo '<th>AÇÃO</th>';
                        echo '<th>ID LISTA</th>';
                        echo '<th>RAMAL</th>';
                        echo '<th>Data/Hora</th>';
                        echo '</tr>';
                        echo '</thead>';
                        echo '<tbody>';
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo '<tr>';
                            echo '<td>' . $row['id'] . '</td>';
                            echo '<td>' . htmlspecialchars($row['usuario']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['ip']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['acao']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['id_lista']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['ramal']) . '</td>';
                            echo '<td>' . date("d/m/Y H:i:s", strtotime($row['datahora'])) . '</td>';
                            echo '</tr>';
                        }
                        echo '</tbody>';
                        echo '</table>';
                        mysqli_free_result($result);
                    } else {
                        echo '<div class="alert alert-warning"><em>Nenhum registro encontrado.</em></div>';
                    }
                } else {
                    echo '<div class="alert alert-danger"><em>Erro ao consultar o histórico.</em></div>';
                }

                mysqli_close($link);
                ?>
            </div>
        </section>

        <!-- Scripts do DataTables -->
        <script src="js/jquery-3.7.0.min.js"></script>
        <script src="js/datatables.min.js"></script>
        <script>
            $(document).ready(function () {
                $('#logTable').DataTable({
                    "language": {
                        "lengthMenu": "Mostrar _MENU_ registros por página",
                        "zeroRecords": "Nenhum registro encontrado",
                        "info": "Mostrando página _PAGE_ de _PAGES_",
                        "infoEmpty": "Sem registros disponíveis",
                        "infoFiltered": "(filtrado de _MAX_ registros no total)",
                        "search": "Buscar:",
                        "paginate": {
                            "first": "Primeira",
                            "last": "Última",
                            "next": "Próxima",
                            "previous": "Anterior"
                        }
                    },
                    "order": [[0, "desc"]]
                });
            });
        </script>
    </body>

    </html>
    <?php
} else {
    header("Location: index.php");
    exit;
}
?>