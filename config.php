<?php
// Usando variáveis de ambiente para armazenar credenciais
$DB_SERVER = getenv('DB_SERVER');
$DB_USERNAME = getenv('DB_USERNAME');
$DB_PASSWORD = getenv('DB_PASSWORD');
$DB_NAME = getenv('DB_NAME');

try {
    // Tentativa de conectar ao banco de dados MySQL
    $link = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_NAME);
    
    if ($link->connect_error) {
        // Registrar erro sem expor detalhes
        error_log("Erro de conexão: " . $link->connect_error);
        throw new Exception("Erro ao conectar ao banco de dados. Tente novamente mais tarde.");
    }

    // Configurar charset para evitar problemas com codificação
    $link->set_charset("utf8mb4");

} catch (Exception $e) {
    // Mensagem de erro genérica para o usuário final
    die("Erro ao conectar ao banco de dados. Tente novamente mais tarde.");
}
?>