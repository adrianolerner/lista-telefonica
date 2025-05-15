<?php
    // Versão atual do seu aplicativo
    function getCurrentVersion()
    {
        return '0.12.1';
    }
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créditos - Lista Telefônica</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3a0ca3;
	    --back: #A9A9A9;
            --accent: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --success: #4cc9f0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--dark);
        }

        .container {
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 800px;
            padding: 40px;
            margin: 40px 0;
            position: relative;
            overflow: hidden;
        }

        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 8px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
        }

        h1 {
            color: var(--primary);
            margin-top: 0;
            font-weight: 600;
            font-size: clamp(1.8rem, 5vw, 2.5rem);
            line-height: 1.2;
        }

        .author {
            display: flex;
            align-items: center;
            margin: 20px 0;
            flex-wrap: wrap;
        }

        .author-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 20px;
            border: 4px solid var(--primary);
        }

        .author-info h2 {
            margin: 0;
            color: var(--secondary);
            font-size: clamp(1.2rem, 4vw, 1.5rem);
        }

        .author-info p {
            margin: 5px 0 0;
            opacity: 0.8;
            font-size: clamp(0.9rem, 3vw, 1rem);
        }

        .details {
            margin: 30px 0;
        }

        .detail-item {
            display: flex;
            margin-bottom: 15px;
            align-items: flex-start;
        }

        .detail-icon {
            min-width: 40px;
            height: 40px;
            background-color: rgba(67, 97, 238, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: var(--primary);
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .detail-item h3 {
            margin: 0 0 8px 0;
            font-size: clamp(1rem, 4vw, 1.2rem);
        }

        .detail-item p {
            margin: 0;
            font-size: clamp(0.9rem, 3vw, 1rem);
            line-height: 1.5;
        }

        .version {
            display: inline-block;
            background-color: var(--primary);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            margin: 10px 0;
            font-size: clamp(0.9rem, 3vw, 1rem);
        }

        .update-available {
            background-color: var(--accent);
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            display: flex;
            flex-direction: column;
            gap: 15px;
            animation: pulse 2s infinite;
        }

        .update-available-content {
            flex: 1;
        }

        .update-available strong {
            display: block;
            margin-bottom: 5px;
            font-size: clamp(1rem, 4vw, 1.2rem);
        }

        .update-available p {
            margin: 0;
            font-size: clamp(0.9rem, 3vw, 1rem);
        }

        .update-btn {
            background-color: white;
            color: var(--accent);
            border: none;
            padding: 10px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: block;
            text-align: center;
            font-size: clamp(0.9rem, 3vw, 1rem);
            width: 100%;
            box-sizing: border-box;
        }

        .update-btn:hover {
            background-color: rgba(255, 255, 255, 0.9);
            transform: translateY(-2px);
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(247, 37, 133, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(247, 37, 133, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(247, 37, 133, 0);
            }
        }

        .btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
            text-align: center;
            font-size: clamp(0.9rem, 3vw, 1rem);
            width: 90%;
        }

        .btn:hover {
            background-color: var(--secondary);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-ghost {
            background-color: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
	    width: 89%;
        }

	.btn-back {
            background-color: transparent;
	    border: 2px solid var(--back);
            color: var(--back);
            width: 89%;
        }

        .btn-ghost:hover {
            background-color: var(--primary);
            color: white;
        }

	.btn-back:hover {
	   background-color: var(--back);
	   color: white;
	}

        .links {
            display: flex;
            gap: 10px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        footer {
            margin-top: 40px;
            text-align: center;
            opacity: 0.7;
            font-size: clamp(0.8rem, 3vw, 0.9rem);
            line-height: 1.5;
        }

        @media (min-width: 601px) {
            .update-available {
                flex-direction: row;
                align-items: center;
            }
            
            .update-btn {
                width: auto;
                min-width: 180px;
                margin-left: 15px;
            }

            .btn {
                width: auto;
                min-width: 150px;
            }
        }

        @media (max-width: 600px) {
            .container {
                padding: 25px 15px;
                margin: 20px 0;
            }

            .author {
                flex-direction: column;
                text-align: center;
            }

            .author-avatar {
                margin-right: 0;
                margin-bottom: 15px;
            }

            .links {
                flex-direction: column;
            }

            .detail-item {
                flex-direction: row;
                align-items: flex-start;
            }
        }

        @media (max-width: 400px) {
            .container {
                width: 95%;
                padding: 20px 10px;
            }

            .detail-item {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .detail-icon {
                margin-right: 0;
                margin-bottom: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Sobre a aplicação Lista Telefônica</h1>

        <div class="author">
            <img src="https://avatars.githubusercontent.com/u/11412428?v=4" alt="Adriano Lerner Biesek" class="author-avatar">
            <div class="author-info">
                <h2>Adriano Lerner Biesek</h2>
                <p>Autor e Desenvolvedor</p>
            </div>
        </div>

        <div class="version">Versão Atual: <?php echo getCurrentVersion(); ?></div>

        <?php if (isUpdateAvailable()) { ?>
            <div class="update-available">
                <div class="update-available-content">
                    <strong>Nova versão disponível!</strong>
                    <p>Uma atualização para a versão <?php echo getLatestVersion(); ?> está disponível no GitHub.</p>
                </div>
                <a href="https://github.com/adrianolerner/lista-telefonica/releases/latest" class="update-btn" target="_blank">Baixar Última Versão</a>
            </div>
        <?php } else {
		echo '<div class="version"><strong>Você tem a versão mais recente! </strong></div>' ;
	 }; ?>

        <div class="details">
            <div class="detail-item">
                <div class="detail-icon">📱</div>
                <div>
                    <h3>Aplicativo de Lista Telefônica para órgãos públicos</h3>
                    <p>Bem-vindo à aplicação de lista telefônica desenvolvida para um órgão público. Esta aplicação foi construída utilizando PHP, HTML, CSS, JavaScript e MariaDB. O objetivo desta aplicação é fornecer uma interface intuitiva para gerenciar contatos telefônicos do órgão.</p>
                </div>
            </div>

            <div class="detail-item">
                <div class="detail-icon">🔗</div>
                <div>
                    <h3>Repositório no GitHub</h3>
                    <p>Código-fonte aberto disponível para uso, colaboração e inspiração.</p>
                </div>
            </div>

            <div class="detail-item">
                <div class="detail-icon">📄</div>
                <div>
                    <h3>Licença MIT</h3>
                    <p>Software livre para uso, modificação e distribuição, com reconhecimento do autor.</p>
                </div>
            </div>
        </div>

        <div class="links">
	    <a href="index.php" class="btn btn-back">← Voltar</a>
            <a href="https://github.com/adrianolerner/lista-telefonica" class="btn" target="_blank">Visitar GitHub</a>
            <a href="https://github.com/adrianolerner/lista-telefonica/releases" class="btn btn-ghost" target="_blank">Ver Todas Versões</a>
        </div>
        <footer>
            © <?php echo date('Y'); ?> Adriano Lerner Biesek | Prefeitura Municipal de Castro (Paraná). Todos os direitos reservados.
        </footer>
    </div>

    <?php
    // Função para obter a última versão do GitHub
    function getLatestVersion()
    {
        $url = 'https://api.github.com/repos/adrianolerner/lista-telefonica/releases/latest';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'ListaTelefonica-App');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        return isset($data['tag_name']) ? $data['tag_name'] : '0.0.0';
    }

    // Função para verificar se há atualização disponível
    function isUpdateAvailable()
    {
        $current = getCurrentVersion();
        $latest = getLatestVersion();

        // Remove o 'v' inicial se existir para comparação
        $current = ltrim($current, 'v');
        $latest = ltrim($latest, 'v');

        return version_compare($latest, $current, '>');
    }
    ?>
</body>
</html>
