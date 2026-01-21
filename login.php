<?php
session_start();
include('config.php');

// Verifica se os campos foram enviados
if (empty($_POST['usuario']) || empty($_POST['senha'])) {
    header('Location: acesso.php');
    exit();
}

// ---------------------------------------------------------
// LÓGICA DO RECAPTCHA
// ---------------------------------------------------------
// IMPORTANTE: Em produção, altere $recaptcha_verified para false
$recaptcha_verified = true; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recaptcha_response'])) {
    // Verifica se a chave foi enviada (se não estiver em modo de teste local)
    if (!empty($_POST['recaptcha_response'])) {
        $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
        $recaptcha_secret = 'COLOCAR_CODIGO_GOOGLE_RECAPCHA';
        $recaptcha_response = $_POST['recaptcha_response'];

        $recaptcha = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
        $recaptcha = json_decode($recaptcha);

        if ($recaptcha && $recaptcha->success && $recaptcha->score >= 0.5) {
            $recaptcha_verified = true;
        } else {
            // Se o Google rejeitou, garantimos que é falso
            $recaptcha_verified = false;
        }
    }
}

// ---------------------------------------------------------
// AUTENTICAÇÃO NO BANCO
// ---------------------------------------------------------

if ($recaptcha_verified) {
    $usuario = trim($_POST['usuario']);
    $senha = $_POST['senha'];

    // Busca o hash da senha no banco
    // Usamos Prepare Statement para evitar SQL Injection
    if ($stmt = $link->prepare("SELECT senha FROM usuarios WHERE usuario = ?")) {
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $stmt->store_result();

        // Verifica se o usuário existe
        if ($stmt->num_rows === 1) {
            $stmt->bind_result($senha_hash);
            $stmt->fetch();

            // Verifica se a senha bate com o hash
            if (password_verify($senha, $senha_hash)) {
                // SUCESSO NO LOGIN
                
                // SEGURANÇA: Regenera o ID da sessão para evitar Session Fixation
                session_regenerate_id(true);

                $_SESSION['usuario'] = $usuario;
                
                // Fecha conexões antes de redirecionar
                $stmt->close();
                $link->close();

                header('Location: index.php');
                exit();
            }
        }
        $stmt->close();
    }
}

// FALHA NO LOGIN (Senha errada, usuário inexistente ou Recaptcha falhou)
$link->close();
$_SESSION['nao_autenticado'] = true;
header('Location: acesso.php');
exit();
?>