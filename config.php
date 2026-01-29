<?php
// Configurações via Variáveis de Ambiente (com fallback para local)
$DB_SERVER   = getenv('DB_SERVER') ?: 'localhost';
$DB_USERNAME = getenv('DB_USERNAME') ?: 'root';
$DB_PASSWORD = getenv('DB_PASSWORD') ?: '';
$DB_NAME     = getenv('DB_NAME') ?: 'agenda';

// Arquivo de instalação
$setupFile = __DIR__ . '/setup.sql';

// Desativa report de erros automáticos para tratarmos o erro de conexão manualmente
mysqli_report(MYSQLI_REPORT_OFF);

try {
    // 1. Tenta conectar normalmente ao banco
    $link = @new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_NAME);

    // 2. Verifica se houve erro na conexão
    if ($link->connect_error) {
        
        // Erro 1049: Banco de dados desconhecido (Unknown database)
        if ($link->connect_errno === 1049) {
            
            // --- INÍCIO AUTO-INSTALAÇÃO ---
            
            // Conecta sem selecionar banco (apenas no servidor)
            $link = new mysqli($host, $user, $pass);
            if ($link->connect_error) {
                throw new Exception("Falha ao conectar no MySQL (Root): " . $link->connect_error);
            }

            // Cria o banco com utf8mb4
            if (!$link->query("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci")) {
                throw new Exception("Erro ao criar banco de dados: " . $link->error);
            }

            // Seleciona o banco criado
            $link->select_db($db);

            // Verifica e importa o setup.sql
            if (file_exists($setupFile)) {
                $sqlContent = file_get_contents($setupFile);

                // Executa o dump (multi_query para vários comandos)
                if ($link->multi_query($sqlContent)) {
                    
                    // Limpa os resultados do buffer (Obrigatório no mysqli)
                    do {
                        if ($res = $link->store_result()) {
                            $res->free();
                        }
                    } while ($link->more_results() && $link->next_result());

                    if ($link->errno) {
                        throw new Exception("Erro na execução do SQL: " . $link->error);
                    }

                    // Remove o arquivo setup.sql por segurança
                    if (@unlink($setupFile)) {
                        error_log("Instalação: setup.sql removido com sucesso.");
                    }

                } else {
                    throw new Exception("Erro ao iniciar importação do SQL: " . $link->error);
                }
            } else {
                // Banco criado, mas sem tabelas (arquivo não existe)
                // Não fazemos nada, apenas conecta no banco vazio
                error_log("Aviso: Banco criado, mas 'setup.sql' não encontrado.");
            }
            // --- FIM AUTO-INSTALAÇÃO ---

        } else {
            // Outro erro de conexão (senha errada, servidor offline)
            throw new Exception("Erro de conexão MySQL (" . $link->connect_errno . "): " . $link->connect_error);
        }
    }

    // Define charset correto
    $link->set_charset("utf8mb4");

} catch (Exception $e) {
    // Loga o erro real no servidor
    error_log($e->getMessage());
    
    // Se for conexão recusada (banco subindo), avisa para esperar
    if (strpos($e->getMessage(), 'Connection refused') !== false) {
        die("<div style='text-align:center; padding:50px; font-family:sans-serif;'>
                <h2>Banco de dados inicializando...</h2>
                <p>O sistema está aguardando o banco ficar pronto. Recarregue em 10 segundos.</p>
             </div>");
    }

    // Exibe erro genérico (ou detalhado se for localhost)
    $msg = ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['REMOTE_ADDR'] == '127.0.0.1') 
           ? $e->getMessage() 
           : "Erro ao conectar ao banco de dados.";
           
    die("<div style='color:red; font-family:sans-serif; padding:20px; border:1px solid red;'>Erro Crítico: $msg</div>");
}
?>