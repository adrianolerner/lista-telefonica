<?php
session_start();
include('config.php');

// Verifica se os campos foram enviados
if (empty($_POST['usuario']) || empty($_POST['senha'])) {
    header('Location: acesso.php');
    exit();
}

// ---------------------------------------------------------
// LÓGICA DO RECAPTCHA ENTERPRISE
// ---------------------------------------------------------
$recaptcha_verified = true; // Em produção, mude para false

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recaptcha_response'])) {
    
    if (!empty($_POST['recaptcha_response'])) {
        $token = $_POST['recaptcha_response'];
        
        // CONFIGURAÇÕES ENTERPRISE
        $api_key = "SUA_CHAVE_API_GOOGLE_CLOUD"; // Diferente da Chave do Site!
        $project_id = "ID_DO_SEU_PROJETO_GOOGLE_CLOUD";
        $site_key = "SUA_CHAVE_DO_SITE_ENTERPRISE";

        // URL da API Enterprise
        $url = "https://recaptchaenterprise.googleapis.com/v1/projects/{$project_id}/assessments?key={$api_key}";

        // Dados para a verificação
        $data = [
            'event' => [
                'token' => $token,
                'siteKey' => $site_key,
                'expectedAction' => 'login' // Deve ser igual ao que colocamos no acesso.php
            ]
        ];

        // Chamada via cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response);

        // Verificação Enterprise
        // O Enterprise retorna um objeto "tokenProperties" e uma pontuação "riskAnalysis"
        if (isset($result->tokenProperties->valid) && $result->tokenProperties->valid === true) {
            // Pontuação de risco (0.1 a 1.0). 1.0 é humano, 0.1 é robô.
            if ($result->riskAnalysis->score >= 0.5) {
                $recaptcha_verified = true;
            } else {
                $recaptcha_verified = false;
            }
        } else {
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

    if ($stmt = $link->prepare("SELECT senha FROM usuarios WHERE usuario = ?")) {
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($senha_hash);
            $stmt->fetch();

            if (password_verify($senha, $senha_hash)) {
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

// FALHA NO LOGIN
$link->close();
$_SESSION['nao_autenticado'] = true;
header('Location: acesso.php');
exit();
?>