<?php

namespace setasign\tests\functional\SetaFpdf;

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
        $pdf->AddFont('dejavu', '', $this->getAssetsDir() . '/fonts/DejaVu/DejaVuSans.ttf');
        $pdf->AddFont('DeJavu', '', $this->getAssetsDir() . '/fonts/DejaVu/DejaVuSans-BoldOblique.ttf');
    }
}