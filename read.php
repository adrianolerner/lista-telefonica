<?php
// Verifique a existência do parâmetro id antes de processar mais
if (isset($_GET["id_lista"]) && !empty(trim($_GET["id_lista"]))) {
    // Inclui config file
    require_once "config.php";
    
    // Define o ID
    $param_id = trim($_GET["id_lista"]);

    // ---------------------------------------------------------
    // NOVO: ATUALIZA CONTADOR DE ACESSOS
    // ---------------------------------------------------------
    $sql_count = "UPDATE lista SET acessos = acessos + 1 WHERE id_lista = ?";
    if ($stmt_count = mysqli_prepare($link, $sql_count)) {
        mysqli_stmt_bind_param($stmt_count, "i", $param_id);
        mysqli_stmt_execute($stmt_count);
        mysqli_stmt_close($stmt_count);
    }
    // ---------------------------------------------------------

    // Prepara uma declaração de seleção (Código Original)
    $sql = "SELECT 
      l.id_lista,
      l.nome,
      l.ramal,
      l.email,
      l.setor,
      s.secretaria
        FROM 
          lista l
        JOIN 
        secretarias s ON l.secretaria = s.id_secretaria WHERE id_lista = ?";

    if ($stmt = mysqli_prepare($link, $sql)) {
        // Vincula as variáveis à instrução preparada como parâmetros
        mysqli_stmt_bind_param($stmt, "i", $param_id);

        // Tentativa de executar a instrução preparada
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) == 1) {
                $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

                // Recupera o valor do campo individual
                $nome = $row["nome"];
                $ramal = $row["ramal"];
                $email = $row["email"];
                $setor = $row["setor"];
                $secretaria = $row["secretaria"];
            } else {
                // URL não contém parâmetro id válido
                header("location: error.php");
                exit();
            }

        } else {
            echo "Oops! Algo saiu errado. Tente novamente mais tarde.";
        }
    }

    // Fecha declaração
    mysqli_stmt_close($stmt);

    // Fecha conexão
    mysqli_close($link);
} else {
    // URL não contém o parâmetro id
    header("location: error.php");
    exit();
}
// ---------------------------------------------------------
// FIM DO BLOCO PHP
// ---------------------------------------------------------
?>

<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Registro - <?php echo htmlspecialchars($nome); ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bs-body-bg);
        }

        .navbar-brand img {
            height: 40px;
            margin-right: 10px;
        }

        /* Cartão de Detalhes */
        .card-details {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        /* Destaque para os ícones */
        .icon-box {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background-color: var(--bs-success);
            color: white;
            margin-right: 15px;
        }

        .detail-label {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--bs-secondary-color);
            margin-bottom: 2px;
        }

        .detail-value {
            font-size: 1.1rem;
            font-weight: 600;
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
            <div class="col-lg-8 col-md-10">

                <div class="card card-details overflow-hidden">
                    <div
                        class="card-header bg-success bg-opacity-10 border-bottom border-success border-opacity-25 py-3">
                        <div class="d-flex align-items-center">
                            <i class="fa fa-id-card fa-2x text-success me-3"></i>
                            <div>
                                <h4 class="mb-0 fw-bold text-success-emphasis">Detalhes do Contato</h4>
                                <small class="text-muted">Visualizando informações do registro
                                    #<?php echo $_GET["id_lista"]; ?></small>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4 p-3 rounded bg-body-tertiary">
                            <div class="icon-box">
                                <i class="fa fa-user"></i>
                            </div>
                            <div>
                                <div class="detail-label">Nome Completo</div>
                                <div class="detail-value"><?php echo htmlspecialchars($nome); ?></div>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <div class="icon-box bg-primary">
                                        <i class="fa fa-phone"></i>
                                    </div>
                                    <div>
                                        <div class="detail-label">Ramal</div>
                                        <div class="detail-value text-primary fs-4"><i class="far fa-copy"
                                                style="cursor:pointer; margin-right:8px;"
                                                onclick="copyToClipboard('<?php echo htmlspecialchars(preg_replace('/\D/', '', $row['ramal'])) ?>')"
                                                title="Copiar ramal"></i><a
                                                href="tel:<?php echo htmlspecialchars(preg_replace('/\D/', '', $row['ramal'])) ?>"><?php echo htmlspecialchars($ramal); ?></a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <div class="icon-box bg-primary">
                                        <i class="fa fa-envelope"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="detail-label">E-mail</div>
                                        <div class="detail-value text-primary fs-4"><i class="far fa-copy"
                                                style="cursor:pointer; margin-right:8px;"
                                                onclick="copyToClipboard('<?php echo htmlspecialchars($email); ?>')"
                                                title="Copiar e-mail"></i><a
                                                href="mailto:<?php echo htmlspecialchars($email); ?>"><?php echo htmlspecialchars($email); ?></a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <hr class="opacity-25">
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <div class="icon-box bg-secondary">
                                        <i class="fa fa-building"></i>
                                    </div>
                                    <div>
                                        <div class="detail-label">Secretaria</div>
                                        <div class="detail-value"><?php echo htmlspecialchars($secretaria); ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <div class="icon-box bg-secondary">
                                        <i class="fa fa-sitemap"></i>
                                    </div>
                                    <div>
                                        <div class="detail-label">Setor</div>
                                        <div class="detail-value"><?php echo htmlspecialchars($setor); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-body-tertiary p-3 d-flex justify-content-between align-items-center">
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="fa fa-arrow-left me-2"></i> Voltar para a lista
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script de Dark Mode (Idêntico ao index.php para manter a persistência)
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
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert("Copiado para a área de transferência: " + text);
            }).catch(err => {
                console.error('Erro ao copiar: ', err);
            });
        }
    </script>
</body>

</html>