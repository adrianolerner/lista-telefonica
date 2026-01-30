<?php
// Versão atual do seu aplicativo
function getCurrentVersion() {
    return '0.16';
}

// Função para obter a última versão do GitHub
function getLatestVersion() {
    $url = 'https://api.github.com/repos/adrianolerner/lista-telefonica/releases/latest';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'ListaTelefonica-App');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2); // Timeout curto para não travar a página
    $response = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($response, true);
    return isset($data['tag_name']) ? $data['tag_name'] : '0.0.0';
}

// Função para verificar se há atualização disponível
function isUpdateAvailable() {
    $current = ltrim(getCurrentVersion(), 'v');
    $latest = ltrim(getLatestVersion(), 'v');
    return version_compare($latest, $current, '>');
}
?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobre - Lista Telefônica</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: var(--bs-body-bg); }
        .author-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 3px solid var(--bs-success);
            padding: 3px;
        }
        .card { border: none; }
        .update-banner {
            background: linear-gradient(45deg, #198754, #146c43);
            color: white;
        }
    </style>
</head>

<body>
    
    <nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm mb-5">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fa fa-phone-square me-2"></i> LISTA TELEFÔNICA
            </a>
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-light btn-sm" id="themeToggle" title="Alternar Tema">
                    <i class="fa fa-moon"></i>
                </button>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <div class="card shadow-lg mb-4">
                    <div class="card-body p-4 p-md-5">
                        
                        <div class="text-center mb-4">
                            <img src="https://avatars.githubusercontent.com/u/11412428?v=4" alt="Adriano Lerner Biesek" class="author-avatar mb-3">
                            <h2 class="fw-bold mb-0">Adriano Lerner Biesek</h2>
                            <p class="text-success fw-semibold">Autor e Desenvolvedor</p>
                            
                            <div class="d-flex justify-content-center gap-2 mt-2">
                                <span class="badge bg-body-secondary text-body border px-3 py-2">
                                    <i class="fa fa-code-branch me-1 text-success"></i> Versão <?php echo getCurrentVersion(); ?>
                                </span>
                                <?php if (!isUpdateAvailable()): ?>
                                    <span class="badge bg-success-subtle text-success border border-success px-3 py-2">
                                        <i class="fa fa-check-circle me-1"></i> Atualizado
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <hr class="my-4 opacity-25">

                        <?php if (isUpdateAvailable()): ?>
                            <div class="card update-banner mb-4 shadow-sm">
                                <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
                                    <div>
                                        <h5 class="mb-1 fw-bold"><i class="fa fa-rocket me-2"></i> Nova versão disponível!</h5>
                                        <p class="mb-0 small opacity-75">A versão <?php echo getLatestVersion(); ?> já está disponível no repositório.</p>
                                    </div>
                                    <a href="https://github.com/adrianolerner/lista-telefonica/releases/latest" target="_blank" class="btn btn-light btn-sm fw-bold px-4">Baixar Agora</a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <h4 class="fw-bold mb-4"><i class="fa fa-info-circle text-success me-2"></i> Sobre o Projeto</h4>
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="p-3 rounded bg-body-tertiary h-100">
                                    <h6 class="fw-bold"><i class="fa fa-university me-2 text-success"></i> Propósito</h6>
                                    <p class="small text-muted mb-0">Interface intuitiva para gestão de contatos e ramais em órgãos públicos, otimizando a comunicação interna e externa.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 rounded bg-body-tertiary h-100">
                                    <h6 class="fw-bold"><i class="fa fa-layer-group me-2 text-success"></i> Stack Técnica</h6>
                                    <p class="small text-muted mb-0">Desenvolvido com PHP 8, MariaDB, Bootstrap 5 e DataTables. Focado em performance e responsividade.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 rounded bg-body-tertiary h-100">
                                    <h6 class="fw-bold"><i class="fa fa-balance-scale me-2 text-success"></i> Licença MIT</h6>
                                    <p class="small text-muted mb-0">Software livre. Você pode usar, modificar e distribuir livremente, desde que mantidos os créditos originais.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 rounded bg-body-tertiary h-100">
                                    <h6 class="fw-bold"><i class="fa-brands fa-github me-2 text-success"></i> Open Source</h6>
                                    <p class="small text-muted mb-0">Código aberto disponível para a comunidade. Sinta-se à vontade para contribuir com melhorias.</p>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-5">
                            <a href="index.php" class="btn btn-outline-secondary px-4">
                                <i class="fa fa-arrow-left me-2"></i> Voltar
                            </a>
                            <a href="https://github.com/adrianolerner/lista-telefonica" target="_blank" class="btn btn-success px-4">
                                <i class="fab fa-github me-2"></i> Ver no GitHub
                            </a>
                            <a href="https://github.com/adrianolerner/lista-telefonica/releases" target="_blank" class="btn btn-outline-success px-4">
                                <i class="fa fa-history me-2"></i> Versões
                            </a>
                        </div>
                    </div>
                </div>

                <footer class="text-center mb-5 opacity-50">
                    <p class="small">
                        © <?php echo date('Y'); ?> Adriano Lerner Biesek | Prefeitura Municipal de Castro (PR)<br>
                        Feito com <i class="fa fa-heart text-danger"></i> para o serviço público.
                    </p>
                </footer>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script de Dark Mode (Persistente)
        const themeToggle = document.getElementById('themeToggle');
        const htmlElement = document.documentElement;
        const icon = themeToggle.querySelector('i');

        const savedTheme = localStorage.getItem('theme') || 'dark';
        htmlElement.setAttribute('data-bs-theme', savedTheme);
        updateIcon(savedTheme);

        themeToggle.addEventListener('click', () => {
            const currentTheme = htmlElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            htmlElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateIcon(newTheme);
        });

        function updateIcon(theme) {
            if (theme === 'dark') {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            } else {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            }
        }
    </script>
</body>
</html>