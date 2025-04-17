<?php

// Mecanismo de login
include('verifica_login.php');
// Configurações de banco de dados
require_once "config.php";

// Verificação de Admin
$useradmin = @$_SESSION['usuario'];

if ($stmt = mysqli_prepare($link, "SELECT admin FROM usuarios WHERE usuario = ?")) {
    mysqli_stmt_bind_param($stmt, "s", $useradmin);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $admin);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
}

if (!$useradmin) {
    header("Location: login.php");
    exit;
}

if ($admin !== "s") {
    header("Location: index.php");
    exit;
}

// Consulta os dados com JOIN na tabela secretarias
$sql = "SELECT 
l.nome,
l.setor,
l.ramal,
l.email,
s.secretaria
FROM 
lista l
JOIN 
secretarias s ON l.secretaria = s.id_secretaria";

$result = mysqli_query($link, $sql);

// Define os cabeçalhos do CSV para download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=lista_telefonica.csv');

// Abre o output buffer como um arquivo para escrita
$output = fopen('php://output', 'w');

// Cabeçalho no mesmo formato da importação
fputcsv($output, array('nome', 'setor', 'ramal', 'email', 'secretaria'), ',');

// Escreve os dados no CSV
while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, $row, ',');
}

fclose($output);
mysqli_close($link);
exit;