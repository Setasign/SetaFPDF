<?php
use \setasign\SetaFpdf\SetaFpdf;

require_once '../vendor/autoload.php';

class Pdf extends SetaFpdf
{
    public function Header()
    {
        $this->SetFillColor(255, 207, 42);
        $this->Rect(0, 0, 3, $this->h, 'F');

        $this->SetFont('', 'B', 24);
        $this->Cell(0, 12, 'Page ' . $this->PageNo(), 0, 1);
    }

    public function Footer()
    {
        $this->SetFontSize(10);
        $this->SetY(-15);
        $this->Cell(0, 10, '© Setasign - Jan Slabon');
    }

    public function writeFooters()
    {
        $this->SetAutoPageBreak(false);

        $pageCount = $this->getPageCount();
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $this->SetPage($pageNo);
            $this->SetY(-15);
            $this->SetFontSize(10);
            $this->Cell(0, 10, $pageNo . ' / ' . $pageCount, 0, 0, 'R');
        }
    }
}

$pdf = new Pdf();
$pdf->AddFont('DejaVuSans', '', __DIR__ . '/../assets/fonts/DejaVu/DejaVuSans.ttf');
$pdf->AddFont('DejaVuSans', 'B', __DIR__ . '/../assets/fonts/DejaVu/DejaVuSans-Bold.ttf');
$pdf->SetFont('DejaVuSans', '', 12);

$pdf->AddPage();

$pdf->Write(5, file_get_contents(__DIR__ . '/../assets/text/20k_c1.txt'));
$pdf->Write(5, file_get_contents(__DIR__ . '/../assets/text/20k_c2.txt'));

$pdf->writeFooters();

$pdf->SetDisplayMode('fullpage');
$pdf->Output();