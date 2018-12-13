<?php

namespace setasign\tests\visual\SetaFpdf\Special;

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

        $this->assertProxySame($proxy, 0.3, self::DPI);
    }
}