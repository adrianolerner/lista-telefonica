<?php
// O config já define a variável $acesso_rede_permitido e $user_ip
// Se este arquivo for chamado diretamente sem o config, incluímos ele.
if (!isset($link)) {
    require_once 'config.php';
}

// 1. Verifica IP (Rede)
if (!$acesso_rede_permitido) {
    // Se não estiver na rede permitida, manda para o index (visualização pública)
    // Opcional: Adicionar mensagem de erro na sessão
    header('Location: index.php');
    exit();
}

// 2. Verifica Autenticação (Sessão)
if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
    // Se não estiver logado, manda para a tela de login
    header('Location: acesso.php');
    exit();
}

// Se passou pelos dois if's, o usuário é Admin e está na Rede correta.
?>