<?php

//Mecanismo de login
//session_start();
//include('verifica_login.php');

// Verifique a existência do parâmetro id antes de processar mais
if (isset($_GET["id_lista"]) && !empty(trim($_GET["id_lista"]))) {
    // Inclui config file
    require_once "config.php";

    // Prepara uma declaração de seleção
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
        secretarias s ON l.secretaria = s.id_secretaria WHERE id_lista = ?";

    if ($stmt = mysqli_prepare($link, $sql)) {
        // Vincula as variáveis à instrução preparada como parâmetros
        mysqli_stmt_bind_param($stmt, "i", $param_id);

        // Definir parâmetros
        $param_id = trim($_GET["id_lista"]);

        // Tentativa de executar a instrução preparada
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) == 1) {
                /* Busca a linha do resultado como um array associativo. Como o conjunto de resultados
                 contém apenas uma linha */
                $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

                // Recupera o valor do campo individual
                $nome = $row["nome"];
                $ramal = $row["ramal"];
                $email = $row["email"];
                $setor = $row["setor"];
                $secretaria = $row["secretaria"];
            } else {
                // URL não contém parâmetro id válido. Redirecionar para a página de erro
                header("location: error.php");
                exit();
            }

        } else {
            echo "Oops! Algo saiu errado. Tente novamente mais tarde.";
        }
    }

    // Fecha declaração
    mysqli_stmt_close($stmt);

    // Fecha conexão
    mysqli_close($link);
} else {
    // URL não contém o parâmetro id. Redirecionar para a página de erro
    header("location: error.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br" class="dark" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Ver registro</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <style>
        .wrapper {
            width: 800px;
            margin: 0 auto;
        }
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
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <h1 class="mt-5 mb-3">Ver Registro</h1>
                    <div class="form-group">
                        <label>Nome</label>
                        <p><b>
                                <?php echo $row["nome"]; ?>
                            </b></p>
                    </div>
                    <div class="form-group">
                        <label>Ramal</label>
                        <p><b>
                                <?php echo $row["ramal"]; ?>
                            </b></p>
                    </div>
                    <div class="form-group">
                        <label>E-mail</label>
                        <p><b>
                                <?php echo $row["email"]; ?>
                            </b></p>
                    </div>
                    <div class="form-group">
                        <label>Setor</label>
                        <p><b>
                                <?php echo $row["setor"]; ?>
                            </b></p>
                    </div>
                    <div class="form-group">
                        <label>Secretaria</label>
                        <p><b>
                                <?php echo $row["secretaria"]; ?>
                            </b></p>
                    </div>
                    <p><a href="index.php" class="btn btn-secondary">← Voltar</a></p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
