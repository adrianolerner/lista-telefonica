<?php

//Mecanismo de login
include('../verifica_login.php');
// Inclui arquivo de configuração
require_once "../config.php";

//Verificação de Admin
$useradmin = @$_SESSION['usuario'];

if ($stmt = mysqli_prepare($link, "SELECT admin FROM usuarios WHERE usuario = ?")) {
    // Bind parameters
    mysqli_stmt_bind_param($stmt, "s", $useradmin);

    // Execute statement
    mysqli_stmt_execute($stmt);

    // Bind result variables
    mysqli_stmt_bind_result($stmt, $admin);

    // Fetch the result
    mysqli_stmt_fetch($stmt);

    // Close the statement
    mysqli_stmt_close($stmt);
}

$adminarray = ['admin' => $admin];

if (!$useradmin) {
    header("Location: login.php");
    exit;
}

if ($adminarray['admin'] == "s") {

    ?>
    <!DOCTYPE html>
    <html lang="pt-br" class="dark" data-bs-theme="dark">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1.0" />
        <meta name="theme-color" content="#576b37" />
        <title>Secretarias Lista Telefônica</title>
        <link rel="stylesheet" href="../css/bootstrap.min.css">
        <link rel="stylesheet" href="../css/font-awesome.min.css">
        <link href="css/datatables.min.css" rel="stylesheet">
    </head>
    <style>
        body {
            background-color: #1C1C1C;
            color: white;
        }

        section {
            width: fit-content;
            margin: auto;
            padding: 10px;
        }

        #userTable th,
        #userTable td {
            border: 1px solid #ccc;
            text-align: center;
            padding-right: 10px;
            padding-left: 10px;
        }

        #userTable thead {
            background: #4F4F4F;
        }

        #userTable {
            margin: auto;
        }

        .headcontainer {
            width: auto;
            height: auto;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        body {
            margin: 0px;
        }

        .h2 {
            text-align: center;
        }
    </style>

    </head>

    <body>
        <header>
            <section>
                <div class="headcontainer">
                    <h2 class="pull-left">SECRETARIAS LISTA TELEFÔNICA</h2>
                </div>
                <div class="headcontainer">
                    <a href="../index.php" class="btn btn-secondary ml-2">← Voltar</a><b>&nbsp </b>
                    <a href="create.php" class="btn btn-success pull-right"><i class="fa fa-plus"></i> Adicionar
                        Secretaria</a>
                </div>
            </section>
        </header>
        <section>
            <div>
                <?php
                // Inclui arquivo de configuração
                require_once "../config.php";

                // Tenta selecionar a execução da consulta
                $sql = "SELECT * FROM secretarias";
                if ($result = mysqli_query($link, $sql)) {
                    if (mysqli_num_rows($result) > 0) {
                        echo '<table id="userTable">';
                        echo "<thead>";
                        echo "<tr>";
                        echo "<th>#</th>";
                        echo "<th>Secretaria</th>";
                        echo "<th>Ação</th>";
                        echo "</tr>";
                        echo "</thead>";
                        echo "<tbody>";
                        while ($row = mysqli_fetch_array($result)) {
                            echo "<tr>";
                            echo "<td>" . $row['id_secretaria'] . "</td>";
                            echo "<td>" . $row['secretaria'] . "</td>";
                            echo "<td>";
                            echo '<a href="read.php?id_secretaria=' . $row['id_secretaria'] . '" class="mr-3" title="Ver" data-toggle="tooltip"><span class="fa fa-eye"></span></a>';
                            echo '<a href="update.php?id_secretaria=' . $row['id_secretaria'] . '" class="mr-3" title="Atualizar" data-toggle="tooltip"><span class="fa fa-pencil"></span></a>';
                            echo '<a href="delete.php?id_secretaria=' . $row['id_secretaria'] . '" title="Apagar" data-toggle="tooltip"><span class="fa fa-trash"></span></a>';
                            echo "</td>";
                            echo "</tr>";
                        }
                        echo "</tbody>";
                        echo "</table>";
                        // Libera conjunto de resultados
                        mysqli_free_result($result);
                    } else {
                        echo '<div class="alert alert-danger"><em>Não foram encontrados registros</em></div>';
                    }
                } else {
                    echo "Oops! Algo saiu errado. Tente novamente mais tarde.";
                }

                // Fechar conexão
                mysqli_close($link);
                ?>
            </div>
        </section>
    </body>

    </html>
<?php } else {
    header("Location: /lista/index.php");
    exit;
}
?>