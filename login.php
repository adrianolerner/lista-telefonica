<?php
session_start();
include('config.php');

$ipaddress = $user_ip;

// BLOQUEIO DE REDE
if (!$acesso_rede_permitido) {
    header('Location: index.php');
    exit();
}

if (empty($_POST['usuario']) || empty($_POST['senha'])) {
    header('Location: acesso.php');
    exit();
}

// 1. RATE LIMIT (Mantido igual)
$max_tentativas = 5;
$janela_tempo = 15;
$sql_check = "SELECT COUNT(*) FROM login_tentativas WHERE ip = ? AND datahora > (NOW() - INTERVAL ? MINUTE)";
if ($stmt = $link->prepare($sql_check)) {
    $stmt->bind_param("si", $ipaddress, $janela_tempo);
    $stmt->execute();
    $stmt->bind_result($tentativas_recentes);
    $stmt->fetch();
    $stmt->close();
    if ($tentativas_recentes >= $max_tentativas) {
        $link->close();
        $_SESSION['bloqueado'] = true;
        header('Location: acesso.php');
        exit();
    }
}

// 2. LÓGICA DO CAPTCHA (CONDICIONAL)
$captcha_verified = false;

// Se o captcha estiver DESATIVADO no config, liberamos automaticamente
if (!$captcha_ativo) {
    $captcha_verified = true;
} 
else {
    // Se estiver ATIVO, aplicamos as regras
    $ips_liberados = ['127.0.0.1', '::1'];

    if (in_array($ipaddress, $ips_liberados)) {
        // Bypass para Dev
        $captcha_verified = true;
    } else {
        // Validação Cloudflare
        if (isset($_POST['cf-turnstile-response']) && !empty($_POST['cf-turnstile-response'])) {
            $url = "https://challenges.cloudflare.com/turnstile/v0/siteverify";
            $data = [
                'secret' => $cf_secret_key, // Usa variável do config
                'response' => $_POST['cf-turnstile-response'],
                'remoteip' => $ipaddress
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);

            $response_data = json_decode($response);
            if ($response_data->success) {
                $captcha_verified = true;
            }
        }
    }
}

if (!$captcha_verified) {
    // Falha no Captcha
    $link->close();
    $_SESSION['nao_autenticado'] = true;
    header('Location: acesso.php');
    exit();
}

// 3. AUTENTICAÇÃO NO BANCO
$usuario = trim($_POST['usuario']);
$senha = $_POST['senha'];

if ($stmt = $link->prepare("SELECT senha FROM usuarios WHERE usuario = ?")) {
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($senha_hash);
        $stmt->fetch();

        if (password_verify($senha, $senha_hash)) {
            // Sucesso
            $sql_clean = "DELETE FROM login_tentativas WHERE ip = ?";
            if($stmt_clean = $link->prepare($sql_clean)) {
                $stmt_clean->bind_param("s", $ipaddress);
                $stmt_clean->execute();
                $stmt_clean->close();
            }

            session_regenerate_id(true);
            $_SESSION['usuario'] = $usuario;
            $stmt->close();
            $link->close();
            header('Location: index.php');
            exit();
        }
    }
    $stmt->close();
}

// 4. FALHA NO LOGIN (Log de Erro)
$sql_log = "INSERT INTO login_tentativas (ip, datahora) VALUES (?, NOW())";
if($stmt_log = $link->prepare($sql_log)) {
    $stmt_log->bind_param("s", $ipaddress);
    $stmt_log->execute();
    $stmt_log->close();
}

$link->close();
$_SESSION['nao_autenticado'] = true;
header('Location: acesso.php');
exit();
?>