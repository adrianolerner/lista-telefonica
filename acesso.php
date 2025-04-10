<?php
session_start();

//Verificação de IP
//$ip = $_SERVER['HTTP_X_REAL_IP'];
$ipaddress = "172.16.0.10";
//$ipaddress = strstr($ip, ',', true);

if (!fnmatch("172.16.0.*", $ipaddress)) {
    header('Location: index.php');
    exit();
}

// ini_set('default_charset','iso-8859-1');
if (!empty($_POST['usuario'])) {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acesso ao Sistema</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,700" rel="stylesheet">
    <link rel="stylesheet" href="css/bulma.min.css" />
    <link rel="stylesheet" type="text/css" href="css/login.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <script src="https://www.google.com/recaptcha/api.js?render=COLOCAR_CODIGO_GOOGLE_RECAPCHA"></script>
    <script>
        grecaptcha.ready(function () {
            grecaptcha.execute('COLOCAR_CODIGO_GOOGLE_RECAPCHA', { action: 'contact' }).then(function (token) {
                var recaptchaResponse = document.getElementById('recaptchaResponse');
                recaptchaResponse.value = token;
            });
        });
    </script>
    <style>
        img {
            max-width: 100%;
        }
    </style>
</head>

<body>
    <section class="hero is-success is-fullheight">
        <div class="hero-body">
            <div class="container has-text-centered">
                <div class="column is-4 is-offset-4">
                    <img src="img/logo4.png" width="200px"></img>
                    <h3 class="title has-text-black">Administrador Lista Telefônica</h3>
                </div>
                <br />
                <br />
                <div class="column is-4 is-offset-4">
                    <h3 class="title has-text-grey">LOGIN</h3>
                    <?php
                    if (isset($_SESSION['nao_autenticado'])):
                        ?>
                        <div class="notification is-danger">
                            <p>ERRO: Usuário ou senha inválidos.</p>
                        </div>
                        <?php
                    endif;
                    unset($_SESSION['nao_autenticado']);
                    ?>
                    <div class="box">
                        <form action="login.php" method="POST">
                            <div class="field">
                                <div class="control">
                                    <input name="usuario" name="text" class="input is-large" placeholder="Seu usuário"
                                        autofocus="">
                                </div>
                            </div>
                            <div class="field">
                                <div class="control">
                                    <input name="senha" class="input is-large" type="password" placeholder="Sua senha">
                                </div>
                            </div>
                            <button type="submit" class="button is-block is-link is-large is-fullwidth"><i
                                    class="fa fa-sign-in"></i> Entrar</button>
                        </form>
                    </div>
                </div>
                <div class="column is-4 is-offset-4">
                    <div class="box">
                        <p align="center">Por favor entre com seu usuário e senha para acessar a administração da lista
                            telefônica.</p>
                    </div>
                    <div class="box">
                        <p align="center">IP: <?php echo htmlspecialchars($ipaddress, ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>

</html>