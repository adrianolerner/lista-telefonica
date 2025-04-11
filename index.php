<?php
// Mecanismo de login
session_start();

//Verificação de IP
//$ip = $_SERVER['HTTP_X_REAL_IP'];
$ipaddress = "172.16.0.10";
//$ipaddress = strstr($ip, ',', true);

// Checagens de usuário
include('config.php');

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

// Prepared statement for the banner
if ($stmtBanner = mysqli_prepare($link, "SELECT banner FROM banner WHERE id_banner = ?")) {
    $id_banner = 1; // Id banner fixo
    
    // Bind parameters
    mysqli_stmt_bind_param($stmtBanner, "i", $id_banner);
    
    // Execute statement
    mysqli_stmt_execute($stmtBanner);
    
    // Bind result variables
    mysqli_stmt_bind_result($stmtBanner, $banner);
    
    // Fetch the result
    mysqli_stmt_fetch($stmtBanner);
    
    // Close the statement
    mysqli_stmt_close($stmtBanner);
}

$bannerarray = ['banner' => $banner];
?>
<!DOCTYPE html>
<html lang="pt-br" class="dark" data-bs-theme="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0" />
    <meta name="theme-color" content="#576b37" />
    <title>LISTA TELEFÔNICA PREFEITURA DE CASTRO</title>
    <link href="css/datatables.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">

    <style>
        body {
            background-color: #1C1C1C;
            color: white;
        }
        section {
            width: 150vh;
            margin: auto;
            padding: 10px;
        }
        #userTable th, #userTable td {
            border: 1px solid #ccc;
            text-align: center;
        }
        #userTable thead {
            background: #4F4F4F;
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
                <h2 class="h2">LISTA TELEFÔNICA PREFEITURA DE CASTRO</h2>
            </div>
            <div class="headcontainer">
                <p>Seja bem-vindo a lista telefônica <?php if (!empty($useradmin)) {echo $useradmin;} else {echo "visitante";} ?></p>
            </div>
            <div class="headcontainer">
                <p><?php if (!empty($useradmin)) {echo "Use as opções abaixo para gerenciar a lista telefônica.";}?></p>
            </div>
            <div class="headcontainer">
                <?php if (fnmatch("172.16.0.*", $ipaddress)) { ?>
                    <?php if (!empty($useradmin)) { ?>
                        <a href="logout.php" class="btn btn-secondary pull-center"><i class="fa fa-sign-out"></i> Sair</a>&nbsp;
                        <a href="senha.php?user=<?php echo htmlspecialchars($useradmin, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary pull-right"><i class="fa fa-lock"></i> Alterar Senha</a>&nbsp;
                        <?php if ($adminarray['admin'] == "s") { ?>
                            <a href="usuarios/index.php" class="btn btn-primary pull-center"><i class="fa fa-users"></i> Gerenciar Usuários</a>&nbsp;
                            <a href="secretarias/index.php" class="btn btn-primary pull-center"><i class="fa fa-home"></i> Gerenciar Secretarias</a>&nbsp;
                            <a href="update_banner.php?id_banner=1" class="btn btn-primary pull-center"><i class="fa fa-info-circle"></i> Atualizar Banner</a>&nbsp;
                            <a href="delete_all.php" class="btn btn-danger pull-center"><i class="fa fa-exclamation-triangle"></i> Apagar Tudo</a>&nbsp;
                            <a href="importar.php" class="btn btn-success pull-center"><i class="fa fa-upload"></i> Importar CSV</a>
                        <?php } ?>&nbsp;
                        <a href="create.php" class="btn btn-success pull-center"><i class="fa fa-plus"></i> Adicionar Ramal</a>
                    <?php } ?>
                    <?php if (empty($useradmin)) { ?>
                        <a href="login.php" class="btn btn-primary pull-center"><i class="fa fa-sign-in"></i> LOGIN</a>
                    <?php } ?>
                <?php } ?>
            </div>
        </section>
    </header>
    <section>
        <marquee><?php echo htmlspecialchars($bannerarray["banner"], ENT_QUOTES, 'UTF-8'); ?></marquee>
    </section>
    <section>
        <?php
        // Inclui config file
        require_once "config.php";

        // Executa a query de seleção
        $sql = "SELECT 
            l.id_lista,
            l.nome,
            l.ramal,
            l.email,
            l.setor,
            s.secretaria
        FROM 
            lista l
        JOIN 
            secretarias s ON l.secretaria = s.id_secretaria";

        if ($result = mysqli_query($link, $sql)) {
            if (mysqli_num_rows($result) > 0) {
                echo '<table id="userTable" width="100%">';
                echo "<thead>";
                echo "<tr>";
                echo '<th>Secretaria</th>';
                echo '<th>Setor</th>';
                echo '<th>Nome</th>';
                echo '<th>Ramal</th>';
                echo '<th>E-mail</th>';
                if (!empty($useradmin)) {
                    echo '<th>Ação</th>';
                }
                echo "</tr>";
                echo "</thead>";
                echo '<tbody class="tablebody">';
                while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['secretaria'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['setor'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['nome'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['ramal'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') . "</td>";

                    if (!empty($useradmin)) {
                        echo '<td width="8%">';
                        echo '<a href="read.php?id_lista=' . urlencode($row['id_lista']) . '" class="mr-3" title="Ver" data-toggle="tooltip"><span class="fa fa-eye"></span></a>';
                        echo '<a href="update.php?id_lista=' . urlencode($row['id_lista']) . '" class="mr-3" title="Atualizar" data-toggle="tooltip"><span class="fa fa-pencil"></span></a>';
                        echo '<a href="delete.php?id_lista=' . urlencode($row['id_lista']) . '" title="Apagar" data-toggle="tooltip"><span class="fa fa-trash"></span></a>';
                        echo "</td>";
                    }
                    echo "</tr>";
                }
                echo "</tbody>";
                echo "</table>";
                // Libera os resultados
                mysqli_free_result($result);
            } else {
                echo '<div class="alert alert-danger"><em>Não foram encontrados registros.</em></div>';
            }
        } else {
            echo "Oops! Algo saiu errado. Tente novamente mais tarde.";
        }

        // Fecha a conexão
        mysqli_close($link);
        ?>
    </section>
    <footer>
        <section>
            <div class="maincontainer">
                <br />
                <p align="center">
                    <?php if (!empty($useradmin)) {
                        echo "<a href='exportar_csv.php' class='btn btn-primary pull-center'><i class='fa fa-download'></i> GERAR LISTA EM CSV</a>&nbsp;";
                    } else {
                        echo "<a href='gerapdf.php' class='btn btn-primary pull-center'><i class='fa fa-download'></i> GERAR LISTA EM PDF</a>";
                    } ?>
                </p>
                <p align="center"><img src="img/logo2.png" width="150px" /> | <img src="img/logo3.png" width="180px" /></p>
                <p align="center">IP: <?php echo htmlspecialchars($ipaddress, ENT_QUOTES, 'UTF-8'); ?></p>
                <p align="center"><a href="https://github.com/adrianolerner/lista-telefonica">©<?php echo date("Y"); ?> Prefeitura Municipal de Castro | Departamento de Tecnologia | Adriano Lerner Biesek</a></p>
            </div>
        </section>
    </footer>
    <script src="js/jquery-3.7.0.min.js"></script>
    <script src="js/datatables.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#userTable').DataTable({
                language: {
                    "decimal": "decimal",
                    "emptyTable": "Sem dados disponíveis",
                    "info": "Mostrando _START_ até _END_ de _TOTAL_ registros",
                    "infoEmpty": "Mostrando 0 até 0 de 0 registros",
                    "infoFiltered": "(filtrados do total de _MAX_ registros)",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "Mostrando _MENU_ registros",
                    "loadingRecords": "Carregando...",
                    "processing": "",
                    "search": "Procurar:",
                    "zeroRecords": "Não foram encontrados registros",
                    "paginate": {
                        "first": "Primeiro",
                        "last": "Último",
                        "next": "Próximo",
                        "previous": "Anterior"
                    },
                    "aria": {
                        "orderable": "Organizar por esta coluna",
                        "orderableReverse": "Organizar inversamente por esta coluna"
                    }
                }
            });
        });
    </script>
</body>
</html>
