<?php

namespace setasign\tests\visual\SetaFpdf\Tutorial\Four;

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
        global $title;
        $title = '20000 Leagues Under the Seas';
        $pdf->SetTitle($title);
        $pdf->SetAuthor('Jules Verne');
        $pdf->PrintChapter(1, 'A RUNAWAY REEF', $this->getAssetsDir() .'/text/20k_c1.txt');
        $pdf->PrintChapter(2, 'THE PROS AND CONS', $this->getAssetsDir() . '/text/20k_c2.txt');
//        $pdf->Output(); // REPLACED BY ASSERTION

        $this->assertProxySame($pdf, 83);
    }
}