<?php
// =======================================================================
// LÓGICA DE ROTEAMENTO (HTML vs PDF)
// =======================================================================

// Se o parâmetro 'modo' for igual a 'pdf', geramos o binário do PDF.
if (isset($_GET['modo']) && $_GET['modo'] == 'pdf') {
    
    // --- INÍCIO DA GERAÇÃO DO PDF (SEU CÓDIGO ORIGINAL) ---
    require('fpdf.php');
    include('config.php');

    // Configurações
    $cfg_titulo = "Lista Telefônica da Prefeitura de Castro";
    $cfg_endereco = "Prefeitura Municipal de Castro - Endereço: Praça Pedro Kaled, 22 - Castro - CEP: 84165-540 - Telefone: (42) 2122-5000";
    $cfg_creditos = "Prefeitura Municipal de Castro | SMCTI | Adriano Lerner Biesek";
    $cfg_logo = "img/logo.png";

    // Consulta SQL
    $querypdf = "SELECT l.id_lista, l.nome, l.ramal, l.email, l.setor, s.secretaria 
                 FROM lista l 
                 JOIN secretarias s ON l.secretaria = s.id_secretaria 
                 ORDER BY s.secretaria, l.id_lista, l.setor, l.nome";

    $stmt = $link->prepare($querypdf);
    if ($stmt) {
        $stmt->execute();
        $resultpdf = $stmt->get_result();
    } else {
        die("Erro na preparação da consulta: " . $link->error);
    }

    class PDF extends FPDF {
        public $reportTitle;
        public $reportAddress;
        public $reportCredits;
        public $reportLogo;

        function Header() {
            if(file_exists($this->reportLogo)) {
                $this->Image($this->reportLogo, 10, 6, 20);
            }
            $this->SetFont('Helvetica','B',15);
            $this->Cell(80);
            $this->Cell(30,10,$this->convertText($this->reportTitle),0,1,'C');
            $this->Ln(20);
            
            $this->SetFont('Helvetica','B',12);
            $this->Cell(40,10,$this->convertText('Secretaria'),1, 0, 'C');
            $this->Cell(40,10,$this->convertText('Setor'),1, 0, 'C');
            $this->Cell(40,10,$this->convertText('Nome'),1, 0, 'C');
            $this->Cell(30,10,$this->convertText('Ramal'),1, 0, 'C');
            $this->Cell(40,10,$this->convertText('Email'),1, 0, 'C');
            $this->Ln();
        }

        function Footer() {
            $ano = date("Y");
            $this->SetY(-25);
            $this->SetFont('Helvetica','I',8);
            $this->Cell(0,10,$this->convertText($this->reportAddress),0,1,'C');
            $this->Cell(0,5,$this->convertText('©'.$ano.' '.$this->reportCredits),0,1,'C');
            $this->Cell(0,10,$this->convertText('Página ').$this->PageNo().'/{nb}',0,0,'C');
        }

        function Row($data) {
            $this->SetFont('Helvetica','',10);
            $nb = 0;
            for($i=0;$i<count($data);$i++)
                $nb = max($nb, $this->NbLines(40, $this->convertText($data[$i])));
            $h = 5 * $nb;
            $this->CheckPageBreak($h);
            for($i=0;$i<count($data);$i++) {
                $w = [40, 40, 40, 30, 40][$i];
                $a = 'C';
                $x = $this->GetX();
                $y = $this->GetY();
                if($i == 4) { 
                    $data[$i] = $this->breakEmailLine($data[$i]);
                }
                $this->Rect($x, $y, $w, $h);
                $this->MultiCell($w, 5, $this->convertText($data[$i]), 0, $a);
                $this->SetXY($x + $w, $y);
            }
            $this->Ln($h);
        }

        function CheckPageBreak($h) {
            if($this->GetY() + $h > $this->PageBreakTrigger)
                $this->AddPage($this->CurOrientation);
        }

        function NbLines($w, $txt) {
            $cw = &$this->CurrentFont['cw'];
            if($w == 0) $w = $this->w - $this->rMargin - $this->x;
            $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
            $s = str_replace("\r", '', $txt);
            $nb = strlen($s);
            if($nb > 0 and $s[$nb - 1] == "\n") $nb--;
            $sep = -1; $i = 0; $j = 0; $l = 0; $nl = 1;
            while($i < $nb) {
                $c = $s[$i];
                if($c == "\n") { $i++; $sep = -1; $j = $i; $l = 0; $nl++; continue; }
                if($c == ' ') $sep = $i;
                $l += $cw[$c];
                if($l > $wmax) {
                    if($sep == -1) { if($i == $j) $i++; } else $i = $sep + 1;
                    $sep = -1; $j = $i; $l = 0; $nl++;
                } else $i++;
            }
            return $nl;
        }

        function breakEmailLine($email) {
            return str_replace('@', "\n@", $email);
        }

        function convertText($text) {
            return mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
        }
    }

    $pdf = new PDF();
    $pdf->reportTitle   = $cfg_titulo;
    $pdf->reportAddress = $cfg_endereco;
    $pdf->reportCredits = $cfg_creditos;
    $pdf->reportLogo    = $cfg_logo;

    $pdf->AliasNbPages();
    $pdf->AddPage();

    if ($resultpdf->num_rows > 0) {
        while($row = $resultpdf->fetch_assoc()) {
            $secretaria = htmlspecialchars($row['secretaria'], ENT_QUOTES, 'UTF-8');
            $setor      = htmlspecialchars($row['setor'], ENT_QUOTES, 'UTF-8');
            $nome       = htmlspecialchars($row['nome'], ENT_QUOTES, 'UTF-8');
            $ramal      = htmlspecialchars($row['ramal'], ENT_QUOTES, 'UTF-8');
            $email      = htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8');
            $pdf->Row([$secretaria, $setor, $nome, $ramal, $email]);
        }
    } else {
        $pdf->Cell(190,10,$pdf->convertText('Nenhum dado encontrado'),1,0,'C');
    }

    $stmt->close();
    $link->close();

    // Limpa output buffer
    if (ob_get_length()) ob_clean();
    
    $pdf->Output("lista_telefonica_castro.pdf","I");
    exit;
}

// =======================================================================
// INTERFACE HTML (BOOTSTRAP 5)
// =======================================================================
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista Telefônica - PDF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        body, html {
            height: 100%;
            margin: 0;
            overflow: hidden;
            background-color: #f8f9fa;
        }
        
        /* Barra Verde Personalizada */
        .navbar-custom {
            background-color: #198754 !important; /* Verde solicitado */
            color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .navbar-custom .navbar-brand {
            color: white;
            font-weight: 500;
        }

        /* Botão Personalizado (Cinza) */
        .btn-custom-back {
            color: white;           /* Letras cinzas */
            border-color: #6c757d;    /* Borda cinza */
            background-color: transparent; /* Fundo transparente */
            /* Pequena sombra branca para garantir leitura sobre o fundo verde, se necessário */
            box-shadow: 0 0 2px rgba(255,255,255,0.8); 
            background-color: transparent; /* Um fundo bem clarinho para dar contraste com o verde */
        }

        .btn-custom-back:hover {
            background-color: #6c757d; /* Fundo cinza ao passar o mouse */
            color: white !important;   /* Letras brancas ao passar o mouse */
            border-color: #6c757d;
        }

        .pdf-container {
            width: 100%;
            height: calc(100vh - 60px); 
            border: none;
        }
        iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-custom px-3">
        <div class="container-fluid">
            <a href="javascript:history.back()" class="btn btn-custom-back me-3">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>

            <span class="navbar-brand mb-0 h1">Lista Telefônica - Prefeitura de Castro - Versão PDF para salvar e imprimir</span>
            
            <div class="ms-auto"></div>
        </div>
    </nav>

    <div class="pdf-container">
        <iframe src="<?php echo basename($_SERVER['PHP_SELF']); ?>?modo=pdf" title="Relatório PDF"></iframe>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>