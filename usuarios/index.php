<?php

//Mecanismo de login
//session_start();
include('verifica_login.php');

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Usuários Lista Telefônica</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/font-awesome.min.css">
</head>
<style>
    .wrapper {
        width: 800px;
        margin: 0 auto;
    }

    table tr td:last-child {
        width: 120px;
    }

</style>

</head>

<body>
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="mt-5 mb-3 clearfix">
                        <h2 class="pull-left">USUÁRIOS LISTA TELEFÔNICA</h2>
                        <a href="../logout.php" class="btn btn-secondary pull-right">Sair</a><b>&nbsp </b>
                        <a href="create.php" class="btn btn-success pull-right"><i class="fa fa-plus"></i> Adicionar
                            Usuário</a>
                    </div>
                    <?php
                    // Inclui arquivo de configuração
                    require_once "../config.php";

                    // Tenta selecionar a execução da consulta
                    $sql = "SELECT * FROM usuarios";
                    if ($result = mysqli_query($link, $sql)) {
                        if (mysqli_num_rows($result) > 0) {
                            echo '<table class="sortable table table-bordered table-striped">';
                            echo "<thead>";
                            echo "<tr>";
                            echo "<th>#</th>";
                            echo "<th>Usuário</th>";
                            echo "<th>Senha</th>";
                            echo "<th>Admin</th>";
                            echo "<th>Ação</th>";
                            echo "</tr>";
                            echo "</thead>";
                            echo "<tbody>";
                            while ($row = mysqli_fetch_array($result)) {
                                echo "<tr>";
                                echo "<td>" . $row['id'] . "</td>";
                                echo "<td>" . $row['usuario'] . "</td>";
                                echo "<td> ********* </td>";
                                if ($row['admin'] == "s") {
                                    echo "<td>SIM</td>";
                                } else {
                                    echo "<td>NÃO</td>";
                                }
                                ;
                                echo "<td>";
                                echo '<a href="read.php?id=' . $row['id'] . '" class="mr-3" title="Ver" data-toggle="tooltip"><span class="fa fa-eye"></span></a>';
                                echo '<a href="update.php?id=' . $row['id'] . '" class="mr-3" title="Atualizar" data-toggle="tooltip"><span class="fa fa-pencil"></span></a>';
                                echo '<a href="delete.php?id=' . $row['id'] . '" title="Apagar" data-toggle="tooltip"><span class="fa fa-trash"></span></a>';
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
                    <a href="../index.php" class="btn btn-secondary ml-2">Voltar</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>