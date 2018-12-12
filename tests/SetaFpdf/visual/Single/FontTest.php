<?php

namespace setasign\tests\SetaFpdf\visual\Single;


use setasign\tests\VisualTestCase;

class FontTest extends VisualTestCase
{
    public function testStyles()
    {
        $proxy = $this->getProxy();

        $proxy->AddPage();
        // underlines
        $proxy->SetFont('arial', 'u', 12);
        $proxy->Text(20, 20, 'Testing.');
        $proxy->SetXY(20, 30);
        $proxy->Cell(0, 15, 'This should be underlined', 1);

        $proxy->Ln();
        $proxy->Ln();
        $proxy->MultiCell(50, 6, 'Another test with some autobreaks and align.', 1);
        $proxy->Ln();
        $proxy->MultiCell(50, 6, "abcd abcd abcd abcd abcd", 1);

        $proxy->SetTextColor(255, 100, 100);

        $proxy->Ln();
        $proxy->MultiCell(50, 6, 'Another test with some autobreaks and align.', 1, 'L');
        $proxy->Ln();
        $proxy->MultiCell(50, 6, "abcd abcd abcd abcd abcd", 1, 'L');

        $proxy->Ln();
        $proxy->MultiCell(50, 6, 'Another test with some autobreaks and align.', 1, 'C');
        $proxy->Ln();
        $proxy->MultiCell(50, 6, "abcd abcd abcd abcd abcd", 1, 'C');

        $proxy->Ln();
        $proxy->MultiCell(50, 6, 'Another test with some autobreaks and align.', 1, 'R');
        $proxy->Ln();
        $proxy->MultiCell(50, 6, "abcd abcd abcd abcd abcd", 1, 'R');

        $proxy->Ln();
        $proxy->SetFont('', 'B', 16);
        $proxy->MultiCell(50, 8, 'Bold text');
        $proxy->SetFont('', 'BI', 16);
        $proxy->MultiCell(50, 8, 'Bold and italic text');
        $proxy->SetFont('', 'BIU', 16);
        $proxy->MultiCell(50, 8, 'Bold, italic and underline text');

        $this->assertProxySame($proxy, 8.42);
    }

    public function testStylesWithTrueType()
    {
        $proxy = $this->getProxy();

        $proxy->AddPage();

        list($setaFpdf, $pdf) = $proxy->getInstances();
        $fontPath =  __DIR__ . '/../../../../assets/fonts/DejaVu';
        $setaFpdf->AddFont('DejaVuSans', '', $fontPath . '/DejaVuSans.ttf');
        $setaFpdf->AddFont('DejaVuSans', 'B', $fontPath . '/DejaVuSans-Bold.ttf');
        $setaFpdf->AddFont('DejaVuSans', 'I', $fontPath . '/DejaVuSans-Oblique.ttf');
        $setaFpdf->AddFont('DejaVuSans', 'BI', $fontPath . '/DejaVuSans-BoldOblique.ttf');

        $class = new \ReflectionClass($pdf);
        $property = $class->getProperty("fontpath");
        $property->setAccessible(true);
        $property->setValue($pdf, $fontPath . '/');

        $pdf->AddFont('DejaVuSans', '', 'DejaVuSans.php');
        $pdf->AddFont('DejaVuSans', 'B', 'DejaVuSans-Bold.php');
        $pdf->AddFont('DejaVuSans', 'I', 'DejaVuSans-Oblique.php');
        $pdf->AddFont('DejaVuSans', 'BI', 'DejaVuSans-BoldOblique.php');

        // underlines
        $proxy->SetFont('DejaVuSans', 'u', 12);
        $proxy->Text(20, 20, 'Testing.');
        $proxy->SetXY(20, 30);
        $proxy->Cell(0, 15, 'This should be underlined', 1);

        $proxy->Ln();
        $proxy->Ln();
        $proxy->MultiCell(50, 6, 'Another test with some autobreaks and align.', 1);
        $proxy->Ln();
        $proxy->MultiCell(50, 6, "abcd abcd abcd abcd abcd", 1);

        $proxy->SetTextColor(255, 100, 100);

        $proxy->Ln();
        $proxy->MultiCell(50, 6, 'Another test with some autobreaks and align.', 1, 'L');
        $proxy->Ln();
        $proxy->MultiCell(50, 6, "abcd abcd abcd abcd abcd", 1, 'L');

        $proxy->Ln();
        $proxy->MultiCell(50, 6, 'Another test with some autobreaks and align.', 1, 'C');
        $proxy->Ln();
        $proxy->MultiCell(50, 6, "abcd abcd abcd abcd abcd", 1, 'C');

        $proxy->Ln();
        $proxy->MultiCell(50, 6, 'Another test with some autobreaks and align.', 1, 'R');
        $proxy->Ln();
        $proxy->MultiCell(50, 6, "abcd abcd abcd abcd abcd", 1, 'R');

        $proxy->Ln();
        $proxy->SetFont('', 'B', 16);
        $proxy->MultiCell(50, 8, 'Bold text');
        $proxy->SetFont('', 'BI', 16);
        $proxy->MultiCell(50, 8, 'Bold and italic text');
        $proxy->SetFont('', 'BIU', 16);
        $proxy->MultiCell(50, 8, 'Bold, italic and underline text');

        $this->assertProxySame($proxy, 8.42);
    }
}