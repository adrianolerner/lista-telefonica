<?php

//Mecanismo de login
//session_start();
include('verifica_login.php');

// Processa a requisição de delete após a confirmação
if(isset($_POST["id_lista"]) && !empty($_POST["id_lista"])){
    // Inclui config file
    require_once "config.php";
    
    // Prepara o statement de delete
    $sql = "DELETE FROM lista WHERE id_lista = ?";
    
    if($stmt = mysqli_prepare($link, $sql)){

        mysqli_stmt_bind_param($stmt, "i", $param_id);
        
        // Configura os paramentros
        $param_id = trim($_POST["id_lista"]);
        
        // Tenta executar os parametros configurados
        if(mysqli_stmt_execute($stmt)){
            // Registros apagados. Redireciona para o index
            header("location: index.php");
            exit();
        } else{
            echo "Oops! Algo saiu errado. Tente novamente mais tarde.";
        }
    }
     
    // Fecha o statement
    mysqli_stmt_close($stmt);
    
    // Fecha a conexão
    mysqli_close($link);
} else{
    // Checa a existencia de id de parametros
    if(empty(trim($_GET["id_lista"]))){
        // URL não possui paramentro, redireciona para a pagina de erro
        header("location: error.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Apagar registro</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .wrapper{
            width: 800px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="mt-5 mb-3">Apagar registro</h2>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="alert alert-danger">
                            <input type="hidden" name="id_lista" value="<?php echo trim($_GET["id_lista"]); ?>"/>
                            <p>Tem certeza que deseja apagar este registro da lista?</p>
                            <p>
                                <input type="submit" value="Sim" class="btn btn-danger">
                                <a href="index.php" class="btn btn-secondary">Não</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>        
        </div>
    </div>
</body>
</html>