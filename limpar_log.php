<?php
// Verifica login e permissões
include('verifica_login.php');
require_once "config.php";

$useradmin = @$_SESSION['usuario'];

if (!$useradmin) {
    header("Location: login.php");
    exit;
}

// Verifica se é admin
if ($stmt = mysqli_prepare($link, "SELECT admin FROM usuarios WHERE usuario = ?")) {
    mysqli_stmt_bind_param($stmt, "s", $useradmin);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $admin);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
}

if ($admin === "s") {
    // Apaga todos os registros da tabela
    $sql1 = "TRUNCATE TABLE log_alteracoes";

    if (mysqli_query($link, $sql1)) {
        $_SESSION['mensagem'] = "Histórico apagado com sucesso.";
    } else {
        $_SESSION['mensagem'] = "Erro ao limpar o histórico.";
    }

    mysqli_close($link);
    header("Location: historico_alteracoes.php");
    exit;
} else {
    header("Location: index.php");
    exit;
}
