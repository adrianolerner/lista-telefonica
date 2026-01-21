<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro - Requisição Inválida</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: var(--bs-body-bg);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .navbar-brand img { height: 40px; margin-right: 10px; }
        
        /* Centralizar o conteúdo verticalmente */
        .content-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding-bottom: 5rem;
        }

        /* Animação do ícone */
        .error-icon {
            animation: bounce 2s infinite;
        }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
            40% {transform: translateY(-20px);}
            60% {transform: translateY(-10px);}
        }
    </style>
</head>

<body>
    
    <nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
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

    <div class="content-wrapper">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    
                    <div class="card shadow-lg border-danger text-center">
                        <div class="card-header bg-danger text-white py-3">
                            <h4 class="mb-0 fw-bold"><i class="fa fa-exclamation-triangle me-2"></i> Ops! Algo deu errado.</h4>
                        </div>
                        
                        <div class="card-body p-5">
                            <div class="mb-4 text-danger error-icon">
                                <i class="fa fa-ban fa-5x"></i>
                            </div>
                            
                            <h3 class="card-title fw-bold mb-3">Requisição Inválida</h3>
                            
                            <p class="card-text text-muted mb-4 fs-5">
                                Sinto muito, mas não conseguimos processar sua solicitação. O registro que você procura pode não existir ou o link está quebrado.
                            </p>

                            <div class="d-grid gap-2 col-8 mx-auto">
                                <a href="index.php" class="btn btn-outline-danger btn-lg">
                                    <i class="fa fa-arrow-left me-2"></i> Voltar ao Início
                                </a>
                            </div>
                        </div>
                        
                        <div class="card-footer bg-body-tertiary text-muted small py-3">
                            Se o erro persistir, contate o administrador do sistema.
                        </div>
                    </div>

                </div>
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