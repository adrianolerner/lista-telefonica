<?php
session_start();
include('config.php');

if (empty($_POST['usuario']) || empty($_POST['senha'])) {
    header('Location: acesso.php');
    exit();
}

// Checa se o formulário foi submetido:
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recaptcha_response'])) {

    // Cria uma requisição POST:
    $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptcha_secret = 'COLOCAR_SECRET_GOOGLE';
    $recaptcha_response = $_POST['recaptcha_response'];

    // Cria e decodifica a requisição:
    $recaptcha = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
    $recaptcha = json_decode($recaptcha);

    // Toma ação baseado na requisição:
    if ($recaptcha->score >= 0.5) {
        // Verificado
        $recaptcha_verified = true;
    } else {
        $recaptcha_verified = false;
    }

}

if ($recaptcha_verified = true) {

    $usuario = mysqli_real_escape_string($link, $_POST['usuario']);
    $senha = mysqli_real_escape_string($link, $_POST['senha']);

    $query = "select usuario from usuarios where usuario = '{$usuario}' and senha = md5('{$senha}')";

    $result = mysqli_query($link, $query);

    $row = mysqli_num_rows($result);

    if ($row == 1) {
        $_SESSION['usuario'] = $usuario;
        header('Location: index.php');
        exit();
    } else {
        $_SESSION['nao_autenticado'] = true;
        header('Location: acesso.php');
        exit();
    }

} else {

    $_SESSION['nao_autenticado'] = true;
    header('Location: acesso.php');
    exit();

}
//mysqli_close($link);
