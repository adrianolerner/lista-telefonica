<?php
//Mecanismo de login
session_start();

//Verificação de IP
$ip = $_SERVER['HTTP_X_REAL_IP'];
//$ipaddress = "172.16.0.10";
$ipaddress = strstr($ip, ',', true);

//Checagens de usuário
include('config.php');
$useradmin = @$_SESSION['usuario'];
$useradminL = mysqli_real_escape_string($link, $useradmin);
$queryadmin = "SELECT admin FROM usuarios WHERE usuario = '{$useradminL}'";
$resultadmin = mysqli_query($link, $queryadmin);
$adminarray = mysqli_fetch_array($resultadmin);
$querybanner = "SELECT banner FROM banner WHERE id_banner = 1";
$resultbanner = mysqli_query($link, $querybanner);
$bannerarray = mysqli_fetch_array($resultbanner);
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
section{
  width: 150vh;
  margin: auto;
  padding: 10px;
}
#userTable th, #userTable td{
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
.h2{
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
<?php if (fnmatch("172.16.0.*", $ipaddress)) { ?>
                  <?php if (!empty($useradmin)) { ?><a href="logout.php" class="btn btn-secondary pull-right">Sair</a>&nbsp<a href="senha.php?user=<?php echo $useradmin; ?>" class="btn btn-primary pull-right"> Alterar Senha</a>&nbsp<?php if ($adminarray['admin'] == "s") { ?><a href="usuarios/index.php" class="btn btn-primary pull-right"> Gerencia Usuários</a>&nbsp<a href="update_banner.php?id_banner=1" class="btn btn-primary pull-right"> Atualizar Banner</a><?php } ?>&nbsp
                                    <a href="create.php" class="btn btn-success pull-right"><i class="fa fa-plus"></i> Adicionar Ramal</a><?php } ?>
                  <?php if (empty($useradmin)) { ?><a href="login.php" class="btn btn-primary pull-right"> LOGIN</a><?php } ?>
<?php } ?>
</div>
</section>
</header>
<section>
<marquee><?php echo $bannerarray["banner"]; ?></marquee>
</section>
<section>
    <?php
    // Inclui config file
    require_once "config.php";

    // Executa a query de seleção
    $sql = "SELECT * FROM lista";
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
        while ($row = mysqli_fetch_array($result)) {
          echo "<tr>";
          echo "<td>" . $row['secretaria'] . "</td>";
          echo "<td>" . $row['setor'] . "</td>";
          echo "<td>" . $row['nome'] . "</td>";
          echo "<td>" . $row['ramal'] . "</td>";
          echo "<td>" . $row['email'] . "</td>";

          if (!empty($useradmin)) {
            echo '<td width="8%">';
            echo '<a href="read.php?id_lista=' . $row['id_lista'] . '" class="mr-3" title="Ver" data-toggle="tooltip"><span class="fa fa-eye"></span></a>';
            echo '<a href="update.php?id_lista=' . $row['id_lista'] . '" class="mr-3" title="Atualizar" data-toggle="tooltip"><span class="fa fa-pencil"></span></a>';
            echo '<a href="delete.php?id_lista=' . $row['id_lista'] . '" title="Apagar" data-toggle="tooltip"><span class="fa fa-trash"></span></a>';
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
					<p align="center"><a href="gerapdf.php" class="btn btn-primary pull-center">GERAR LISTA EM PDF</a></p>
                    <p align="center"><img src="logo2.png" width="200px" /> | <img src="logo3.png" width="180px"/></p>
                    <p align="center">IP: <?php echo $ipaddress; ?></p>
                    <p align="center"><a href="https://github.com/adrianolerner/lista-telefonica">©<?php echo date("Y"); ?> Prefeitura Municipal de Castro | Departamento de Tecnologia | Adriano Lerner Biesek</a></p>
                </div>
</section>
</footer>
<script src="js/jquery-3.7.0.min.js"></script>
<script src="js/datatables.min.js"></script>
<script>
  $(document).ready(function() {
    $('#userTable').DataTable(
	{language: {
			"decimal":        "decimal",
			"emptyTable":     "Sem dados disponíveis",
			"info":           "Mostrando _START_ até _END_ de _TOTAL_ registros",
			"infoEmpty":      "Mostrando 0 até 0 de 0 registros",
			"infoFiltered":   "(filtrados do total de _MAX_ registros)",
			"infoPostFix":    "",
			"thousands":      ",",
			"lengthMenu":     "Mostrando _MENU_ registros",
			"loadingRecords": "Carregando...",
			"processing":     "",
			"search":         "Procurar:",
			"zeroRecords":    "Não foram encontrados registros",
			"paginate": {
				"first":      "Primeiro",
				"last":       "Último",
				"next":       "Próximo",
				"previous":   "Anterior"
			},
			"aria": {
				"orderable":  "Organizar por esta coluna",
				"orderableReverse": "Organizar inversamente por esta coluna"
			}
		}
	})
  });
</script>
</body>
</html>
