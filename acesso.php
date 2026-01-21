<?php
session_start();

//Verificação de IP (Mantida a lógica original para testes)
//$ip = $_SERVER['HTTP_X_REAL_IP'];
$ipaddress = "172.16.0.10";
//$ipaddress = strstr($ip, ',', true);

// Se quiser bloquear acesso externo via PHP, descomente a lógica abaixo
/*
if (!fnmatch("172.16.0.*", $ipaddress)) {
    header('Location: index.php');
    exit();
}
*/
?>

<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Administrativo</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <script src="https://www.google.com/recaptcha/api.js"></script>
    <script src="https://www.google.com/recaptcha/api.js?render=COLOCAR_CODIGO_GOOGLE_RECAPCHA"></script>
    <script>
        grecaptcha.ready(function () {
            grecaptcha.execute('COLOCAR_CODIGO_GOOGLE_RECAPCHA', { action: 'contact' }).then(function (token) {
                var recaptchaResponse = document.getElementById('recaptchaResponse');
                recaptchaResponse.value = token;
            });
        });
    </script>

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bs-body-bg);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-card {
            width: 100%;
            max-width: 400px;
            border: none;
            box-shadow: 0 1rem 3rem rgba(0,0,0,.175);
        }

        .login-header {
            background-color: var(--bs-success); /* Verde tema */
            color: white;
            text-align: center;
            padding: 2rem 1rem;
            border-top-left-radius: var(--bs-border-radius);
            border-top-right-radius: var(--bs-border-radius);
        }

        .logo-img {
            max-width: 150px;
            margin-bottom: 15px;
            filter: drop-shadow(0px 2px 4px rgba(0,0,0,0.2));
        }

        .form-control:focus {
            border-color: #198754;
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
        }
        
        .input-group-text {
            cursor: pointer;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                
                <div class="card login-card">
                    <div class="login-header bg-gradient bg-success">
                        <img src="img/logo4.png" alt="Logo" class="logo-img">
                        <h4 class="mb-0 fw-bold">Área Administrativa</h4>
                        <small class="opacity-75">Lista Telefônica</small>
                    </div>

                    <div class="card-body p-4">
                        
                        <?php if (isset($_SESSION['nao_autenticado'])): ?>
                            <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                                <i class="fa fa-exclamation-circle me-2"></i>
                                <div>Usuário ou senha inválidos.</div>
                            </div>
                            <?php unset($_SESSION['nao_autenticado']); ?>
                        <?php endif; ?>

                        <form action="login.php" method="POST"> <div class="mb-3">
                                <label class="form-label text-muted small text-uppercase fw-bold">Usuário</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-body-tertiary"><i class="fa fa-user text-secondary"></i></span>
                                    <input type="text" name="usuario" class="form-control" placeholder="Seu usuário" required autofocus>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label text-muted small text-uppercase fw-bold">Senha</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-body-tertiary"><i class="fa fa-lock text-secondary"></i></span>
                                    <input type="password" name="senha" id="senha" class="form-control" placeholder="Sua senha" required>
                                    <span class="input-group-text bg-body-tertiary" onclick="togglePassword()">
                                        <i class="fa fa-eye" id="toggleIcon"></i>
                                    </span>
                                </div>
                            </div>

                            <input type="hidden" name="recaptcha_response" id="recaptchaResponse">

                            <div class="d-grid gap-2 mb-3">
                                <button type="submit" class="btn btn-success btn-lg fw-bold shadow-sm">
                                    ENTRAR <i class="fa fa-sign-in-alt ms-2"></i>
                                </button>
                            </div>
                            
                            <div class="text-center">
                                <a href="index.php" class="text-decoration-none text-muted small hover-link">
                                    <i class="fa fa-arrow-left me-1"></i> Voltar para a Lista
                                </a>
                            </div>

                        </form>
                    </div>

                    <div class="card-footer bg-body-tertiary text-center py-3">
                        <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">
                            IP: <?php echo htmlspecialchars($ipaddress); ?>
                        </small>
                        <small class="text-muted opacity-50" style="font-size: 0.65rem;">
                            Protegido por reCAPTCHA (
                            <a href="https://policies.google.com/privacy" target="_blank" class="text-reset">Privacidade</a> - 
                            <a href="https://policies.google.com/terms" target="_blank" class="text-reset">Termos</a>)
                        </small>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Função para mostrar/esconder senha
        function togglePassword() {
            const input = document.getElementById('senha');
            const icon = document.getElementById('toggleIcon');
            
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = "password";
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>