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
    
    <script src="https://www.google.com/recaptcha/enterprise.js?render=SUA_CHAVE_SITE_ENTERPRISE"></script>
    
    <script>
        // 2. O comando agora utiliza grecaptcha.enterprise
        grecaptcha.enterprise.ready(function () {
            grecaptcha.enterprise.execute('SUA_CHAVE_SITE_ENTERPRISE', { action: 'login' }).then(function (token) {
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
            margin: 0;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            border: none;
            box-shadow: 0 1rem 3rem rgba(0,0,0,.175);
            border-radius: 1rem;
            overflow: hidden;
        }
        .login-header {
            background-color: var(--bs-success);
            color: white;
            text-align: center;
            padding: 2.5rem 1rem;
        }
        .logo-img {
            max-width: 130px;
            margin-bottom: 15px;
            filter: drop-shadow(0px 4px 6px rgba(0,0,0,0.3));
        }
        .form-control:focus {
            border-color: #198754;
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
        }
        .hover-link:hover { color: var(--bs-success) !important; }
    </style>
</head>

<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 d-flex justify-content-center">
                
                <div class="card login-card">
                    <div class="login-header bg-gradient">
                        <img src="img/logo4.png" alt="Logo" class="logo-img">
                        <h4 class="mb-0 fw-bold">Área Administrativa</h4>
                        <small class="opacity-75 text-uppercase" style="letter-spacing: 1px;">Lista Telefônica</small>
                    </div>

                    <div class="card-body p-4">
                        
                        <?php if (isset($_SESSION['nao_autenticado'])): ?>
                            <div class="alert alert-danger d-flex align-items-center mb-4 border-0 shadow-sm" role="alert">
                                <i class="fa fa-exclamation-circle me-2"></i>
                                <div>Usuário ou senha inválidos.</div>
                            </div>
                            <?php unset($_SESSION['nao_autenticado']); ?>
                        <?php endif; ?>

                        <form action="login.php" method="POST" id="loginForm">
                            <div class="mb-3">
                                <label class="form-label text-muted small text-uppercase fw-bold">Usuário</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-body-tertiary border-end-0"><i class="fa fa-user text-secondary"></i></span>
                                    <input type="text" name="usuario" class="form-control border-start-0 ps-0" placeholder="Usuário" required autofocus>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label text-muted small text-uppercase fw-bold">Senha</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-body-tertiary border-end-0"><i class="fa fa-lock text-secondary"></i></span>
                                    <input type="password" name="senha" id="senha" class="form-control border-start-0 border-end-0 ps-0" placeholder="••••••••" required>
                                    <span class="input-group-text bg-body-tertiary border-start-0" onclick="togglePassword()">
                                        <i class="fa fa-eye" id="toggleIcon"></i>
                                    </span>
                                </div>
                            </div>

                            <input type="hidden" name="recaptcha_response" id="recaptchaResponse">

                            <div class="d-grid gap-2 mb-3">
                                <button type="submit" class="btn btn-success btn-lg fw-bold shadow-sm py-2">
                                    ENTRAR <i class="fa fa-sign-in-alt ms-2"></i>
                                </button>
                            </div>
                            
                            <div class="text-center">
                                <a href="index.php" class="text-decoration-none text-muted small hover-link transition-all">
                                    <i class="fa fa-arrow-left me-1"></i> Voltar para a Lista
                                </a>
                            </div>
                        </form>
                    </div>

                    <div class="card-footer bg-body-tertiary text-center py-3 border-0">
                        <div class="small text-muted mb-2">
                            IP: <span class="fw-bold"><?php echo htmlspecialchars($ipaddress); ?></span>
                        </div>
                        <div class="text-muted opacity-50" style="font-size: 0.65rem; line-height: 1.2;">
                            Este site é protegido pelo reCAPTCHA Enterprise e a<br>
                            <a href="https://policies.google.com/privacy" target="_blank" class="text-reset">Política de Privacidade</a> e
                            <a href="https://policies.google.com/terms" target="_blank" class="text-reset">Termos de Serviço</a> do Google se aplicam.
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const input = document.getElementById('senha');
            const icon = document.getElementById('toggleIcon');
            if (input.type === "password") {
                input.type = "text";
                icon.className = "fa fa-eye-slash";
            } else {
                input.type = "password";
                icon.className = "fa fa-eye";
            }
        }
    </script>
</body>
</html>