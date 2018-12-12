<?php

namespace setasign\tests\SetaFpdf\visual\Tutorial\Five;

use setasign\tests\TestProxy;
use setasign\tests\VisualTestCase;

class TutorialTest extends VisualTestCase
{
    public function getProxy($orientation = 'P', $unit = 'mm', $size = 'A4')
    {
        return new TestProxy([
            new FpdfCustom($orientation, $unit, $size),
            new SetaFpdfCustom($orientation, $unit, $size),
        ]);
    }

    public function testTutorial()
    {
        // Instanciation of inherited class
//        $pdf = new PDF(); // REPLACED BY MOCK!
        /** @var FpdfCustom $pdf */
        $pdf = $this->getProxy();
         // Column headings
        $header = array('Country', 'Capital', 'Area (sq km)', 'Pop. (thousands)');
        // Data loading
        $data = $pdf->LoadData(__DIR__ . '/countries.txt');
        $pdf->SetFont('Arial','',14);
        $pdf->AddPage();
        $pdf->BasicTable($header,$data);
        $pdf->AddPage();
        $pdf->ImprovedTable($header,$data);
        $pdf->AddPage();
        $pdf->FancyTable($header,$data);
//        $pdf->Output(); // REPLACED BY ASSERTION

        $this->assertProxySame($pdf, 10, 60);
    }
}