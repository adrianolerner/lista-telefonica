<?php
include('verifica_login.php');
require_once "config.php";

//Verificação de IP (usado para inserção do IP no LOG)
$ip = $_SERVER['HTTP_X_REAL_IP'];
//$ipaddress = "172.16.0.10";
$ipaddress = strstr($ip, ',', true);

// Definir variáveis e inicializar com valores vazios
$nome = $ramal = $email = $setor = $secretaria = "";
$nome_err = $ramal_err = $email_err = $setor_err = $secretaria_err = "";

// Processamento de dados do formulário quando o formulário é enviado
if (isset($_POST["id_lista"]) && !empty($_POST["id_lista"])) {
    $id_lista = $_POST["id_lista"];
    
    // Valida nome
    $input_nome = trim($_POST["nome"]);
    if (empty($input_nome)) {
        $nome_err = "Por favor entre um nome.";
    } elseif (!filter_var($input_nome, FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => "/^[a-zA-Z0-9\s\-\(\)áàâãéèêíïóôõöúçñÁÀÂÃÉÈÊÍÏÓÔÕÖÚÇÑ]+$/u")))) {
        $nome_err = "Por favor entre um nome válido.";
    } else {
        $nome = $input_nome;
    }
    
    // Valida ramal
    $input_ramal = trim($_POST["ramal"]);
    if (empty($input_ramal)) {
        $ramal_err = "Por favor entre um ramal.";
    } elseif (!filter_var($input_ramal, FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => "/^([0-9]|-|\s)+$/")))) {
        $ramal_err = "Por favor entre ramal válido.";
    } else {
        $ramal = $input_ramal;
    }
    
    // Valida e-mail
    $input_email = trim($_POST["email"]);
    if (empty($input_email)) {
        $email = "-";
    } elseif (!filter_var($input_email, FILTER_VALIDATE_EMAIL)) {
        $email_err = "Por favor entre e-mail válido.";
    } else {
        $email = $input_email;
    }
    
    // Valida setor
    $input_setor = trim($_POST["setor"]);
    if (empty($input_setor)) {
        $setor_err = "Por favor entre um setor.";
    } else {
        $setor = $input_setor;
    }
    
    // Valida secretaria
    $input_secretaria = trim($_POST["secretaria"]);
    if (empty($input_secretaria)) {
        $secretaria_err = "Por favor entre uma secretaria.";
    } else {
        $secretaria = $input_secretaria;
    }

    // Verifica os erros de entrada antes de inserir no banco de dados
    if (empty($nome_err) && empty($ramal_err) && empty($email_err) && empty($setor_err) && empty($secretaria_err)) {
        
        // ---------------------------------------------------------
        // 1. BUSCA DADOS ANTIGOS PARA COMPARAR (AUDITORIA)
        // ---------------------------------------------------------
        $dados_antigos = [];
        $sql_old = "SELECT * FROM lista WHERE id_lista = ?";
        if($stmt_old = mysqli_prepare($link, $sql_old)){
            mysqli_stmt_bind_param($stmt_old, "i", $id_lista);
            mysqli_stmt_execute($stmt_old);
            $res_old = mysqli_stmt_get_result($stmt_old);
            $dados_antigos = mysqli_fetch_assoc($res_old);
            mysqli_stmt_close($stmt_old);
        }

        // 2. MONTA A STRING DE DETALHES DA MUDANÇA
        $mudancas = [];
        
        // Compara Nome
        if ($dados_antigos['nome'] != $nome) {
            $mudancas[] = "Registro Alterado: <br> Nome: <s>" . $dados_antigos['nome'] . "</s> &rarr; <strong class='text-success'>" . $nome . "</strong>";
        }
        // Compara Ramal
        if ($dados_antigos['ramal'] != $ramal) {
            $mudancas[] = "Registro Alterado: <br> Ramal: <s>" . $dados_antigos['ramal'] . "</s> &rarr; <strong class='text-success'>" . $ramal . "</strong>";
        }
        // Compara Email
        if ($dados_antigos['email'] != $email) {
            $mudancas[] = "Registro Alterado: <br> Email: <s>" . $dados_antigos['email'] . "</s> &rarr; <strong class='text-success'>" . $email . "</strong>";
        }
        // Compara Setor
        if ($dados_antigos['setor'] != $setor) {
            $mudancas[] = "Registro Alterado: <br> Setor: <s>" . $dados_antigos['setor'] . "</s> &rarr; <strong class='text-success'>" . $setor . "</strong>";
        }
        // Compara Secretaria (ID)
        if ($dados_antigos['secretaria'] != $secretaria) {
            // Nota: Compara o ID da secretaria. Para mostrar o nome seria necessário mais queries, 
            // mas o ID já serve para auditoria técnica.
            $mudancas[] = "Registro Alterado: <br> Secretaria (ID): <s>" . $dados_antigos['secretaria'] . "</s> &rarr; <strong class='text-success'>" . $secretaria . "</strong>";
        }

        $detalhes_log = implode("<br>", $mudancas);
        // ---------------------------------------------------------

        $sql = "UPDATE lista SET nome=?, ramal=?, email=?, setor=?, secretaria=? WHERE id_lista=?";
        
        // Prepara a instrução de atualização
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "sssssi", $param_nome, $param_ramal, $param_email, $param_setor, $param_secretaria, $param_id);

            $param_nome = $nome;
            $param_ramal = $ramal;
            $param_email = $email;
            $param_setor = $setor;
            $param_secretaria = $secretaria;
            $param_id = $id_lista;

            if (mysqli_stmt_execute($stmt)) {

                // REGISTRA O LOG DE ALTERAÇÃO (COM DETALHES)
                $acao = "Atualização";
                $usuario = $_SESSION['usuario'];
                $datahora = date('Y-m-d H:i:s');
                
                // Query atualizada para incluir 'detalhes'
                $sql_log = "INSERT INTO log_alteracoes (acao, id_lista, ramal, usuario, ip, datahora, detalhes) VALUES (?, ?, ?, ?, ?, ?, ?)";
                
                if ($stmt_log = mysqli_prepare($link, $sql_log)) {
                    // Note o "sisssss" (mais um 's' no final para o texto de detalhes)
                    mysqli_stmt_bind_param($stmt_log, "sisssss", $acao, $id_lista, $ramal, $usuario, $ipaddress, $datahora, $detalhes_log);
                    mysqli_stmt_execute($stmt_log);
                    mysqli_stmt_close($stmt_log);
                }

                header("location: index.php");
                exit();
            } else {
                echo "Oops! Algo saiu errado. Tente novamente mais tarde.";
            }
        }
        mysqli_stmt_close($stmt);
    }
} else {
    if (isset($_GET["id_lista"]) && !empty(trim($_GET["id_lista"]))) {
        $id_lista = trim($_GET["id_lista"]);

        $sql = "SELECT * FROM lista WHERE id_lista = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $param_id);
            $param_id = $id_lista;

            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);

                if (mysqli_num_rows($result) == 1) {
                    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                    $nome = $row["nome"];
                    $ramal = $row["ramal"];
                    $email = $row["email"];
                    $setor = $row["setor"];
                    $secretaria = $row["secretaria"];
                } else {
                    header("location: error.php");
                    exit();
                }
            } else {
                echo "Oops! Algo saiu errado. Tente novamente mais tarde.";
            }
        }

        mysqli_stmt_close($stmt);
    } else {
        header("location: error.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atualizar Registro</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: var(--bs-body-bg); }
        .navbar-brand img { height: 40px; margin-right: 10px; }
        
        /* Ajuste para inputs com ícones */
        .input-group-text {
            background-color: var(--bs-tertiary-bg);
            border-color: var(--bs-border-color);
            color: var(--bs-secondary-color);
        }
        .form-control:focus, .form-select:focus {
            border-color: #198754; /* Verde sucesso */
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
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
            <div class="col-lg-6 col-md-8">
                
                <div class="card shadow border-0">
                    <div class="card-header bg-success bg-opacity-10 border-bottom border-success border-opacity-25 py-3">
                        <div class="d-flex align-items-center">
                            <i class="fa fa-edit fa-2x text-success me-3"></i>
                            <div>
                                <h4 class="mb-0 fw-bold text-success-emphasis">Editar Registro</h4>
                                <small class="text-muted">Atualize as informações do contato abaixo.</small>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nome Completo</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa fa-user"></i></span>
                                    <input type="text" name="nome" class="form-control <?php echo (!empty($nome_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $nome; ?>">
                                    <span class="invalid-feedback"><?php echo $nome_err; ?></span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Ramal</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa fa-phone"></i></span>
                                        <input type="text" name="ramal" class="form-control <?php echo (!empty($ramal_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $ramal; ?>">
                                        <span class="invalid-feedback"><?php echo $ramal_err; ?></span>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">E-mail</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                                        <input type="text" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" 
                                               value="<?php echo ($email == "-") ? "" : $email; ?>">
                                        <span class="invalid-feedback"><?php echo $email_err; ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Setor</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa fa-sitemap"></i></span>
                                    <input type="text" name="setor" class="form-control <?php echo (!empty($setor_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $setor; ?>">
                                    <span class="invalid-feedback"><?php echo $setor_err; ?></span>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="secretaria" class="form-label fw-semibold">Secretaria</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa fa-building"></i></span>
                                    <?php
                                    // Preparando a consulta SQL para selecionar todas as secretarias
                                    $stmt_sec = $link->prepare("SELECT id_secretaria, secretaria FROM secretarias");
                                    $stmt_sec->execute();
                                    $result_sec = $stmt_sec->get_result();
                                    ?>
                                    <select class="form-select <?php echo (!empty($secretaria_err)) ? 'is-invalid' : ''; ?>" name="secretaria" id="secretaria">
                                        <?php
                                        if ($result_sec->num_rows > 0) {
                                            $first = true; // Inicializa variável de controle
                                            while ($row = $result_sec->fetch_assoc()) {
                                                // Lógica original mantida
                                                if (!empty($secretaria)) {
                                                    $selected = ($row["id_secretaria"] == $secretaria) ? 'selected' : '';
                                                } else {
                                                    $selected = $first ? 'selected' : '';
                                                }
                                                
                                                echo '<option value="' . htmlspecialchars($row["id_secretaria"]) . '" ' . $selected . '>' . htmlspecialchars($row["secretaria"]) . '</option>';
                                                $first = false;
                                            }
                                        } else {
                                            echo '<option value="">Nenhuma secretaria encontrada</option>';
                                        }
                                        ?>
                                    </select>
                                    <span class="invalid-feedback"><?php echo $secretaria_err; ?></span>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="index.php" class="btn btn-outline-secondary me-md-2">
                                    <i class="fa fa-times me-1"></i> Cancelar
                                </a>
                                <input type="hidden" name="id_lista" value="<?php echo $id_lista; ?>" />
                                <button type="submit" class="btn btn-success">
                                    <i class="fa fa-save me-1"></i> Salvar Alterações
                                </button>
                            </div>

                        </form>
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
<?php
// Fechar conexão no final de tudo
if (isset($link)) {
    mysqli_close($link);
}
?>