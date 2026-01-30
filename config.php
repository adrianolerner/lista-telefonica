<?php
// ====================================================================
// 1. CONTROLE DE ACESSO E REDE (CENTRALIZADO)
// ====================================================================

// Define se o sistema deve restringir o acesso administrativo por IP
// true = Só libera login/painel para os IPs abaixo.
// false = Libera login/painel para qualquer IP (Cuidado!)
define('RESTRITO_POR_IP', false);

// Padrão de IP permitido (Aceita curingas como *)
define('FAIXA_IP_PERMITIDA', '127.0.0.*'); 

// Detecção robusta do IP do visitante
$raw_ip = $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'];
$user_ip = trim(explode(',', $raw_ip)[0]);

if (RESTRITO_POR_IP === false) {
    $acesso_rede_permitido = true;
} else {
    $acesso_rede_permitido = fnmatch(FAIXA_IP_PERMITIDA, $user_ip);
}

// ====================================================================
// 2. BANCO DE DADOS (COM AUTO-INSTALAÇÃO)
// ====================================================================

// Configurações via Variáveis de Ambiente (com fallback para local)
$DB_SERVER   = getenv('DB_SERVER') ?: 'localhost';
$DB_USERNAME = getenv('DB_USERNAME') ?: 'root';
$DB_PASSWORD = getenv('DB_PASSWORD') ?: '';
$DB_NAME     = getenv('DB_NAME') ?: 'agenda';

// Arquivo de instalação
$setupFile = __DIR__ . '/setup.sql';

// Desativa report de erros automáticos
mysqli_report(MYSQLI_REPORT_OFF);

try {
    $link = @new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_NAME);

    if ($link->connect_error) {
        if ($link->connect_errno === 1049) {
            
            // --- AUTO-INSTALAÇÃO ---
            $link = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD);
            if ($link->connect_error) throw new Exception("Falha ao conectar no MySQL (Root): " . $link->connect_error);

            if (!$link->query("CREATE DATABASE IF NOT EXISTS `$DB_NAME` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci")) 
                throw new Exception("Erro ao criar banco: " . $link->error);

            $link->select_db($DB_NAME);

            if (file_exists($setupFile)) {
                $sqlContent = file_get_contents($setupFile);
                if ($link->multi_query($sqlContent)) {
                    do { if ($res = $link->store_result()) $res->free(); } while ($link->more_results() && $link->next_result());
                    if ($link->errno) throw new Exception("Erro SQL: " . $link->error);
                    if (@unlink($setupFile)) error_log("Setup removido.");
                } else throw new Exception("Erro importação SQL: " . $link->error);
            } else error_log("Aviso: Setup.sql não encontrado.");
            // --- FIM AUTO-INSTALAÇÃO ---

        } else {
            throw new Exception("Erro conexão MySQL (" . $link->connect_errno . "): " . $link->connect_error);
        }
    }
    $link->set_charset("utf8mb4");

} catch (Exception $e) {
    error_log($e->getMessage());
    if (strpos($e->getMessage(), 'Connection refused') !== false) {
        die("<div style='text-align:center; padding:50px; font-family:sans-serif;'><h2>Banco de dados inicializando...</h2><p>Recarregue em 10 segundos.</p></div>");
    }
    $msg = ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['REMOTE_ADDR'] == '127.0.0.1') ? $e->getMessage() : "Erro ao conectar ao banco de dados.";
    die("<div style='color:red; font-family:sans-serif; padding:20px; border:1px solid red;'>Erro Crítico: $msg</div>");
}

// ====================================================================
// 3. CONFIGURAÇÃO DE SEGURANÇA (CAPTCHA)
// ====================================================================

// Define as chaves do Cloudflare Turnstile
// Se deixado vazio (''), o sistema desativa automaticamente o Captcha.
$cf_site_key   = getenv('CF_SITE_KEY') ?: '';   // Chave do Site (Pública)
$cf_secret_key = getenv('CF_SECRET_KEY') ?: ''; // Chave Secreta

// Determina se o Captcha deve ser exigido
// Só ativa se AMBAS as chaves estiverem preenchidas
$captcha_ativo = (!empty($cf_site_key) && !empty($cf_secret_key));
?>