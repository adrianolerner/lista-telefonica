<?php
session_start();
include('config.php');

// Captura IP real
$ip = $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'];
$ipaddress = strstr($ip, ',', true) ?: $ip;

// Verifica se os campos foram enviados
if (empty($_POST['usuario']) || empty($_POST['senha'])) {
    header('Location: acesso.php');
    exit();
}

// ---------------------------------------------------------
// 1. RATE LIMIT (Proteção contra Brute Force)
// ---------------------------------------------------------
// Configuração: Máximo 5 tentativas em 15 minutos
$max_tentativas = 5;
$janela_tempo = 15; // minutos

// Conta quantas tentativas falhas este IP teve no intervalo de tempo
$sql_check = "SELECT COUNT(*) FROM login_tentativas WHERE ip = ? AND datahora > (NOW() - INTERVAL ? MINUTE)";
if ($stmt = $link->prepare($sql_check)) {
    $stmt->bind_param("si", $ipaddress, $janela_tempo);
    $stmt->execute();
    $stmt->bind_result($tentativas_recentes);
    $stmt->fetch();
    $stmt->close();

    if ($tentativas_recentes >= $max_tentativas) {
        // Bloqueia o acesso
        $link->close();
        $_SESSION['bloqueado'] = true;
        header('Location: acesso.php');
        exit();
    }
}

// ---------------------------------------------------------
// 2. LÓGICA DO CLOUDFLARE TURNSTILE (COM BYPASS DE DEV)
// ---------------------------------------------------------
$captcha_verified = false; // Começa como falso por segurança

// Lista de IPs que podem pular o Captcha (Dev / Localhost / Seu IP)
$ips_liberados = ['127.0.0.1', '::1'];

if (in_array($ipaddress, $ips_liberados)) {
    // MODO DESENVOLVIMENTO: Pula a checagem
    $captcha_verified = true;
} else {
    // MODO PRODUÇÃO: Valida com Cloudflare
    if (isset($_POST['cf-turnstile-response']) && !empty($_POST['cf-turnstile-response'])) {
        
        $secret_key = "SUA_SECRET_KEY_CLOUDFLARE"; //  Coloque sua Secret Key aqui
        $token = $_POST['cf-turnstile-response'];
        $remote_ip = $ipaddress;

        $url = "https://challenges.cloudflare.com/turnstile/v0/siteverify";
        
        $data = [
            'secret' => $secret_key,
            'response' => $token,
            'remoteip' => $remote_ip
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
    } else {
        // Se não for IP liberado e não enviou token (tentativa de burlar via Postman/Curl)
        $link->close();
        $_SESSION['nao_autenticado'] = true;
        header('Location: acesso.php');
        exit();
    }
}

// ---------------------------------------------------------
// 3. AUTENTICAÇÃO NO BANCO
// ---------------------------------------------------------

if ($captcha_verified) {
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
                // SUCESSO!
                
                // A. Limpa tentativas de erro deste IP (zera o contador)
                $sql_clean = "DELETE FROM login_tentativas WHERE ip = ?";
                if($stmt_clean = $link->prepare($sql_clean)) {
                    $stmt_clean->bind_param("s", $ipaddress);
                    $stmt_clean->execute();
                    $stmt_clean->close();
                }

                // B. Cria a sessão
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
}

// ---------------------------------------------------------
// 4. FALHA NO LOGIN (Registrar tentativa)
// ---------------------------------------------------------

// Insere a tentativa falha no banco
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