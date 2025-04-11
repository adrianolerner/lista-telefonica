<?php
session_start();
include('config.php');

if (empty($_POST['usuario']) || empty($_POST['senha'])) {
    header('Location: acesso.php');
    exit();
}

// Verificação do reCAPTCHA
// Em produção mudar a variavel recaptcha_verified para "false"
$recaptcha_verified = true;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recaptcha_response'])) {
    $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptcha_secret = 'COLOCAR_CODIGO_GOOGLE_RECAPCHA';
    $recaptcha_response = $_POST['recaptcha_response'];

    $recaptcha = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
    $recaptcha = json_decode($recaptcha);

    if ($recaptcha && $recaptcha->success && $recaptcha->score >= 0.5) {
        $recaptcha_verified = true;
    }
}

if ($recaptcha_verified) {
    $usuario = trim($_POST['usuario']);
    $senha = $_POST['senha'];

    // Busca o hash da senha no banco
    $stmt = $link->prepare("SELECT senha FROM usuarios WHERE usuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($senha_hash);
        $stmt->fetch();

        if (password_verify($senha, $senha_hash)) {
            // Sucesso no login
            $_SESSION['usuario'] = $usuario;
            header('Location: index.php');
            exit();
        }
    }

    // Falha no login
    $_SESSION['nao_autenticado'] = true;
    header('Location: acesso.php');
    exit();

} else {
    $_SESSION['nao_autenticado'] = true;
    header('Location: acesso.php');
    exit();
}
?>