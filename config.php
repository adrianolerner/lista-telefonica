<?php
// Credenciais do banco de dados.
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'USUARIO-DB');
define('DB_PASSWORD', 'SENHA-DB');
define('DB_NAME', 'agenda');

/* Tentativa de conectar ao banco de dados MySQL */
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Chaca conexão
if ($link === false) {
    die("ERRO: Não foi possível conectar. " . mysqli_connect_error());
}
?>