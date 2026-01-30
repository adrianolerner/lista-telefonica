<?php
// ====================================================================
// 1. CONTROLE DE ACESSO E REDE (CENTRALIZADO)
// ====================================================================

define('RESTRITO_POR_IP', false);
define('FAIXA_IP_PERMITIDA', '127.0.0.*');
//Nome do órgão (alterar com seu orgão)
$orgao = getenv('NOME_ORGAO') ?: 'NOME DA PREFEITURA';

$raw_ip = $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'];
$user_ip = trim(explode(',', $raw_ip)[0]);

if (RESTRITO_POR_IP === false) {
    $acesso_rede_permitido = true;
} else {
    $acesso_rede_permitido = fnmatch(FAIXA_IP_PERMITIDA, $user_ip);
}

// ====================================================================
// 2. BANCO DE DADOS (COM AUTO-INSTALAÇÃO DOCKER-FRIENDLY)
// ====================================================================

// Configurações via Variáveis de Ambiente (com fallback para local)
// ATENÇÃO: No Docker Compose, o host geralmente é o nome do serviço (ex: db_agenda)
$DB_SERVER   = getenv('DB_SERVER') ?: '127.0.0.1';
$DB_USERNAME = getenv('DB_USERNAME') ?: 'admin';
$DB_PASSWORD = getenv('DB_PASSWORD') ?: '';
$DB_NAME     = getenv('DB_NAME') ?: 'agenda';

// Arquivo de instalação
$setupFile = __DIR__ . '/setup.sql';

// Desativa report de erros automáticos
mysqli_report(MYSQLI_REPORT_OFF);

try {
    // Tenta conectar ao banco
    $link = @new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_NAME);
    $precisa_instalar = false;

    // CENÁRIO 1: Erro de Conexão
    if ($link->connect_error) {
        
        // Erro 1049: Banco não existe (Comum em XAMPP/Localhost sem Docker)
        if ($link->connect_errno === 1049) {
            $precisa_instalar = true;
            
            // Conecta sem selecionar banco para poder criar
            $link = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD);
            if ($link->connect_error) throw new Exception("Falha ao conectar no MySQL (Root): " . $link->connect_error);

            // Cria o banco
            if (!$link->query("CREATE DATABASE IF NOT EXISTS `$DB_NAME` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci")) 
                throw new Exception("Erro ao criar banco: " . $link->error);
            
            $link->select_db($DB_NAME);
        } else {
            // Outro erro (senha, host, etc)
            throw new Exception("Erro conexão MySQL (" . $link->connect_errno . "): " . $link->connect_error);
        }
    } 
    // CENÁRIO 2: Conectou, mas pode estar vazio (Comum no Docker)
    else {
        // Verifica se existe pelo menos uma tabela chave (ex: 'lista')
        $check = $link->query("SHOW TABLES LIKE 'lista'");
        if ($check && $check->num_rows == 0) {
            $precisa_instalar = true;
        }
    }

    // --- ROTINA DE IMPORTAÇÃO ---
    if ($precisa_instalar) {
        if (file_exists($setupFile)) {
            $sqlContent = file_get_contents($setupFile);
            
            // Executa o dump
            if ($link->multi_query($sqlContent)) {
                do { if ($res = $link->store_result()) $res->free(); } while ($link->more_results() && $link->next_result());
                
                if ($link->errno) throw new Exception("Erro SQL na importação: " . $link->error);
                
                // Tenta remover o arquivo (pode falhar no Docker se for volume montado, então usamos @)
                @unlink($setupFile);
                
                error_log("Banco de dados instalado/populado com sucesso.");
            } else {
                throw new Exception("Erro ao iniciar importação do SQL: " . $link->error);
            }
        } else {
            // Se precisa instalar mas não tem arquivo, é erro crítico
            throw new Exception("Banco de dados vazio/inexistente e arquivo 'setup.sql' não encontrado em: " . $setupFile);
        }
    }
    // --- FIM AUTO-INSTALAÇÃO ---

    $link->set_charset("utf8mb4");

} catch (Exception $e) {
    error_log($e->getMessage());
    
    // Tratamento específico para quando o container do banco ainda está subindo
    if (strpos($e->getMessage(), 'Connection refused') !== false || strpos($e->getMessage(), 'Can\'t connect') !== false) {
        die("<div style='text-align:center; padding:50px; font-family:sans-serif;'>
                <h2>Banco de dados inicializando...</h2>
                <p>O sistema está aguardando o banco de dados ficar pronto.</p>
                <p>Por favor, recarregue a página em 10 segundos.</p>
             </div>");
    }

    $msg = ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['REMOTE_ADDR'] == '127.0.0.1') ? $e->getMessage() : "Erro ao conectar ao banco de dados.";
    die("<div style='color:red; font-family:sans-serif; padding:20px; border:1px solid red;'>Erro Crítico: $msg</div>");
}

// ====================================================================
// 3. CONFIGURAÇÃO DE SEGURANÇA (CAPTCHA)
// ====================================================================

$cf_site_key   = getenv('CF_SITE_KEY') ?: '';   
$cf_secret_key = getenv('CF_SECRET_KEY') ?: ''; 

$captcha_ativo = (!empty($cf_site_key) && !empty($cf_secret_key));
?>