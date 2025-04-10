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
        <title>Histórico de Importações</title>
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

            #importTable th,
            #importTable td {
                border: 1px solid #ccc;
                text-align: center;
                padding: 8px;
            }

            #importTable thead {
                background: #4F4F4F;
            }

            .headcontainer {
                display: flex;
                justify-content: center;
                align-items: center;
                margin-bottom: 10px;
            }
        </style>
    </head>

    <body>
        <header>
            <section>
                <div class="headcontainer">
                    <h2 class="pull-left">HISTÓRICO DE IMPORTAÇÕES</h2>
                </div>
                <div class="headcontainer">
                    <a href="index.php" class="btn btn-secondary ml-2">← Voltar</a>
                </div>
            </section>
        </header>
        <section>
            <div>
                <?php
                $sql = "SELECT id_log, usuario, ip, inseridos, ignorados, data_hora FROM log_importacoes ORDER BY id_log DESC";
                if ($result = mysqli_query($link, $sql)) {
                    if (mysqli_num_rows($result) > 0) {
                        echo '<table id="importTable">';
                        echo '<thead>';
                        echo '<tr>';
                        echo '<th>ID</th>';
                        echo '<th>Usuário</th>';
                        echo '<th>IP</th>';
                        echo '<th>Inseridos</th>';
                        echo '<th>Ignorados</th>';
                        echo '<th>Data/Hora</th>';
                        echo '</tr>';
                        echo '</thead>';
                        echo '<tbody>';
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo '<tr>';
                            echo '<td>' . $row['id_log'] . '</td>';
                            echo '<td>' . htmlspecialchars($row['usuario']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['ip']) . '</td>';
                            echo '<td>' . (int) $row['inseridos'] . '</td>';
                            echo '<td>' . (int) $row['ignorados'] . '</td>';
                            echo '<td>' . date("d/m/Y H:i:s", strtotime($row['data_hora'])) . '</td>';
                            echo '</tr>';
                        }
                        echo '</tbody>';
                        echo '</table>';
                        mysqli_free_result($result);
                    } else {
                        echo '<div class="alert alert-warning"><em>Nenhum registro de importação encontrado.</em></div>';
                    }
                } else {
                    echo '<div class="alert alert-danger"><em>Erro ao consultar o histórico.</em></div>';
                }

                mysqli_close($link);
                ?>
            </div>
        </section>
    </body>

    </html>
    <?php
} else {
    header("Location: index.php");
    exit;
}
?>