<?php

namespace setasign\tests\SetaFpdf\visual\Tutorial\Two;


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
        $pdf = $this->getProxy();
//        $pdf->AliasNbPages(); // NOT SUPPORTED
        $pdf->AddPage();
        $pdf->SetFont('Times','',12);
        for($i=1;$i<=40;$i++)
            $pdf->Cell(0,10,'Printing line number '.$i,0,1);
//        $pdf->Output(); // REPLACED BY ASSERTION

        $this->assertProxySame($pdf, 14.5);
    }
}