<?php

namespace setasign\tests\visual\SetaFpdf\Tutorial\Six;

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
        $html = 'You can now easily print text mixing different styles: <b>bold</b>, <i>italic</i>,' . "\n" .
                '<u>underlined</u>, or <b><i><u>all at once</u></i></b>!<br><br>You can also insert links on' . "\n" .
                'text, such as <a href="http://www.fpdf.org">www.fpdf.org</a>, or on an image: click on the logo.';


        // Instanciation of inherited class
        /** @var FpdfCustom $pdf */
        $pdf = $this->getProxy();
        // First page
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 20);
        $pdf->Write(5, "To find out what's new in this tutorial, click ");
        $pdf->SetFont('', 'U');
        $link = $pdf->AddLink();
        $pdf->Write(5, 'here', $link);
        $pdf->SetFont('');
        // Second page
        $pdf->AddPage();
        $pdf->SetLink($link);
        $pdf->Image(__DIR__ . '/logo.png', 10, 12, 30, 0, '', 'http://www.fpdf.org');
        $pdf->SetLeftMargin(45);
        $pdf->SetFontSize(14);
        $pdf->WriteHTML($html);

        $this->assertProxySame($pdf, 22, 60);
    }
}