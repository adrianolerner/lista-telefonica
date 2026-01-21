<?php
// ---------------------------------------------------------
// SCRIPT DE EXPORTAÇÃO (SEM INTERFACE VISUAL)
// ---------------------------------------------------------
// Este arquivo não possui HTML para não corromper o CSV.
// ---------------------------------------------------------

// Mecanismo de login e Configurações
include('verifica_login.php');
require_once "config.php";

// Verificação de Admin
$useradmin = @$_SESSION['usuario'];

// Verifica se o usuário é admin no banco
if ($stmt = mysqli_prepare($link, "SELECT admin FROM usuarios WHERE usuario = ?")) {
    mysqli_stmt_bind_param($stmt, "s", $useradmin);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $admin);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
}

// Redireciona se não estiver logado ou não for admin
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
            secretarias s ON l.secretaria = s.id_secretaria
        ORDER BY l.nome ASC"; // Melhoria: Ordenar por nome facilita a leitura

$result = mysqli_query($link, $sql);

if(!$result) {
    die("Erro ao buscar dados: " . mysqli_error($link));
}

// Define o nome do arquivo com a data atual (Ex: lista_telefonica_2023-10-25.csv)
$filename = 'lista_telefonica_' . date('Y-m-d') . '.csv';

// Define os cabeçalhos HTTP para forçar o download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Abre o output buffer do PHP
$output = fopen('php://output', 'w');

// --- O TRUQUE DO EXCEL ---
// Adiciona o BOM (Byte Order Mark) para garantir que o Excel leia acentos (UTF-8) corretamente
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Cabeçalho das colunas (Igual ao formato de importação)
fputcsv($output, array('Nome', 'Setor', 'Ramal', 'E-mail', 'Secretaria'), ',');

// Escreve os dados no CSV
while ($row = mysqli_fetch_assoc($result)) {
    // Garante que não haja espaços extras no início/fim
    $row = array_map('trim', $row);
    fputcsv($output, $row, ',');
}

// Fecha o arquivo e a conexão
fclose($output);
mysqli_close($link);
exit;
?>