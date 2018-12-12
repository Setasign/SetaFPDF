<?php

namespace setasign\tests\SetaFpdf\functional\SetaFpdf;

use setasign\SetaFpdf\SetaFpdf;
use setasign\tests\TestCase;

class ErrorHandlingTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage File not found
     */
    public function testAddFontWithInvalidArguments()
    {
        $pdf = new SetaFpdf();
        $pdf->AddFont('anything', '', 'a/path/that/does/not/exists.ttf');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Font "dejavu" with style "" was aready added.
     */
    public function testAddFontWithAlreadyAddedFont()
    {
        $pdf = new SetaFpdf();
        $pdf->AddFont('dejavu', '', __DIR__ . '/../../../assets/fonts/DejaVu/DejaVuSans.ttf');
        $pdf->AddFont('DeJavu', '', __DIR__ . '/../../../assets/fonts/DejaVu/DejaVuSans-BoldOblique.ttf');
    }
}