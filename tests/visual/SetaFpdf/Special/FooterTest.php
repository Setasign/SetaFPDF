<?php

namespace setasign\tests\visual\SetaFpdf\Special;

use setasign\SetaFpdf\SetaFpdf;
use setasign\tests\TestProxy;
use setasign\tests\visual\SetaFpdf\Special\Footer\FPDFCustom;
use setasign\tests\visual\SetaFpdf\Special\Footer\SetaFpdfCustom;
use setasign\tests\VisualTestCase;

class FooterTest extends VisualTestCase
{
    public function getProxy($orientation = 'P', $unit = 'mm', $size = 'A4')
    {
        return new TestProxy([
            new FPDFCustom($orientation, $unit, $size),
            new SetaFpdfCustom($orientation, $unit, $size)
        ]);
    }

    public function testSpecialFooter()
    {
        $proxy = $this->getProxy();

        $proxy->SetFont('arial', 'b', 10);

        $proxy->AddPage();

        $proxy->Cell(20, 0, 'hallo');
        $proxy->Cell(20, 0, 'abc');
        $proxy->Cell(20, 0, 'tes');
        $proxy->AddPage();

        $proxy->Cell(20, 0, 'testze');
        $proxy->Cell(20, 0, 'bob');
        $proxy->Cell(20, 0, 'blau');
        $proxy->Cell(20, 0, 'red');
        $proxy->Cell(20, 0, 'testdaten');

        $this->assertProxySame($proxy, 0.4, self::DPI);
    }

    public function testFooterWithCustomFont()
    {
        $setaFpdf = new class extends SetaFpdf {
            public function Footer()
            {
                $this->SetY(-15);
                $this->Cell(0, 10, 'This is a footer');
            }
        };

        $setaFpdf->AddPage();
        $setaFpdf->AddFont('DejaVuSans','', __DIR__ . '/../../../../assets/fonts/DejaVu/DejaVuSans.ttf');
        $setaFpdf->SetFont('DejaVuSans', '', 12);
        $setaFpdf->Cell(0, 10, 'Hello World');

        $testFile = __DIR__ . '/FooterWithCustomFontActualResult.pdf';
        $setaFpdf->Output('F', $testFile);
        $this->assertPdfsEqual([
            __DIR__ . '/FooterWithCustomFontExpectedResult.pdf',
            $testFile
        ], self::DPI, 1, false);
        \unlink($testFile);
    }
}