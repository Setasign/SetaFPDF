<?php
use \setasign\SetaFpdi\SetaFpdi;

require_once '../vendor/autoload.php';

class Pdf extends SetaFpdi
{
    protected $headerTpl;

    public function Header()
    {
        if ($this->headerTpl === null) {
            $this->setSourceFile(__DIR__ . '/../assets/pdfs/tektown/Letterhead.pdf');
            $this->headerTpl = $this->importPage(1);
        }

        $this->useTemplate($this->headerTpl);
    }
}

$pdf = new Pdf();
$pdf->SetMargins(10, 30, 10);
$pdf->AddFont('DejaVuSans', '', __DIR__ . '/../assets/fonts/DejaVu/DejaVuSans.ttf');
$pdf->AddFont('DejaVuSans', 'B', __DIR__ . '/../assets/fonts/DejaVu/DejaVuSans-Bold.ttf');
$pdf->SetFont('DejaVuSans', '', 12);

$pdf->AddPage();

$pdf->MultiCell(0, 5, file_get_contents(__DIR__ . '/../assets/text/20k_c1.txt'));
$pdf->MultiCell(0, 5, file_get_contents(__DIR__ . '/../assets/text/20k_c2.txt'));

$pdf->Output();