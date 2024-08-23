<?php
require('fpdf.php');
include('config.php');

// Preparar consulta SQL usando prepared statements
$querypdf = "SELECT l.id_lista, l.nome, l.ramal, l.email, l.setor, s.secretaria 
             FROM lista l 
             JOIN secretarias s ON l.secretaria = s.id_secretaria 
             ORDER BY s.secretaria, l.id_lista, l.setor, l.nome";

// Preparar e executar a consulta com prepared statements
$stmt = $link->prepare($querypdf);

if ($stmt) {
    $stmt->execute();
    $resultpdf = $stmt->get_result();
} else {
    die("Erro na preparação da consulta: " . $link->error);
}

class PDF extends FPDF
{
    function Header()
    {
        // Logo
        $this->Image('img/logo.png',10,6,20);
        // Fonte Helvetica em negrito 15
        $this->SetFont('Helvetica','B',15);
        // Mover para a direita
        $this->Cell(80);
        // Título
        $this->Cell(30,10,$this->convertText('Lista Telefônica da Prefeitura de Castro'),0,1,'C');
        // Linha de quebra
        $this->Ln(20);
        // Cabeçalho da tabela
        $this->SetFont('Helvetica','B',12);
        $this->Cell(40,10,$this->convertText('Secretaria'),1, 0, 'C');
        $this->Cell(40,10,$this->convertText('Setor'),1, 0, 'C');
        $this->Cell(40,10,$this->convertText('Nome'),1, 0, 'C');
        $this->Cell(30,10,$this->convertText('Ramal'),1, 0, 'C');
        $this->Cell(40,10,$this->convertText('Email'),1, 0, 'C');
        $this->Ln();
    }

    function Footer()
    {
        $ano = date("Y");
        // Posição a 1.5 cm da parte inferior
        $this->SetY(-25);
        // Fonte Helvetica itálico 8
        $this->SetFont('Helvetica','I',8);
        // Dados da empresa
        $this->Cell(0,10,$this->convertText('Prefeitura Municipal de Castro - Endereço: Praça Pedro Kaled, 22 - Castro - CEP: 84165-540 - Telefone: (42) 2122-5000'),0,1,'C');
        $this->Cell(0,5,$this->convertText('©'.$ano.' Prefeitura Municipal de Castro | Departamento de Tecnologia | Adriano Lerner Biesek'),0,1,'C');
        // Número da página
        $this->Cell(0,10,$this->convertText('Página ').$this->PageNo().'/{nb}',0,0,'C');
    }

    function Row($data)
    {
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
            // Modificação: Quebra de linha no caractere "@"
            if($i == 4) { // Considerando que a coluna 'Email' está na posição 4
                $data[$i] = $this->breakEmailLine($data[$i]);
            }
            $this->Rect($x, $y, $w, $h);
            $this->MultiCell($w, 5, $this->convertText($data[$i]), 0, $a);
            $this->SetXY($x + $w, $y);
        }
        $this->Ln($h);
    }

    function CheckPageBreak($h)
    {
        if($this->GetY() + $h > $this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);
    }

    function NbLines($w, $txt)
    {
        $cw = &$this->CurrentFont['cw'];
        if($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if($nb > 0 and $s[$nb - 1] == "\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while($i < $nb) {
            $c = $s[$i];
            if($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if($c == ' ')
                $sep = $i;
            $l += $cw[$c];
            if($l > $wmax) {
                if($sep == -1) {
                    if($i == $j)
                        $i++;
                } else
                    $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else
                $i++;
        }
        return $nl;
    }

    function breakEmailLine($email)
    {
        // Substitui o caractere "@" por "@\n" para quebrar a linha no caractere "@"
        return str_replace('@', "\n@", $email);
    }

    function convertText($text)
    {
        return mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

if ($resultpdf->num_rows > 0) {
    while($row = $resultpdf->fetch_assoc()) {
        // Sanitização dos dados antes de exibir
        $secretaria = htmlspecialchars($row['secretaria'], ENT_QUOTES, 'UTF-8');
        $setor = htmlspecialchars($row['setor'], ENT_QUOTES, 'UTF-8');
        $nome = htmlspecialchars($row['nome'], ENT_QUOTES, 'UTF-8');
        $ramal = htmlspecialchars($row['ramal'], ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8');

        $pdf->Row([$secretaria, $setor, $nome, $ramal, $email]);
    }
} else {
    $pdf->Cell(190,10,$pdf->convertText('Nenhum dado encontrado'),1,0,'C');
}

$pdf->Output("lista_telefonica_castro.pdf","I");

$stmt->close();
$link->close();
?>