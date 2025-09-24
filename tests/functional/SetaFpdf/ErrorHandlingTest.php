<?php

namespace setasign\tests\functional\SetaFpdf;

use setasign\SetaFpdf\SetaFpdf;
use setasign\tests\TestCase;

class ErrorHandlingTest extends TestCase
{
    public function testAddFontWithInvalidArguments()
    {
        $pdf = new SetaFpdf();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('File not found');
        $pdf->AddFont('anything', '', 'a/path/that/does/not/exists.ttf');
    }

    public function testAddFontWithAlreadyAddedFont()
    {
        $pdf = new SetaFpdf();
        $pdf->AddFont('dejavu', '', $this->getAssetsDir() . '/fonts/DejaVu/DejaVuSans.ttf');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Font "dejavu" with style "" was aready added.');
        $pdf->AddFont('DeJavu', '', $this->getAssetsDir() . '/fonts/DejaVu/DejaVuSans-BoldOblique.ttf');
    }
}