<?php

namespace setasign\tests\functional\SetaFpdf;

use PHPUnit\Framework\TestCase;
use setasign\SetaFpdf\Modules\Document;
use setasign\SetaFpdf\SetaFpdf;

class OutputTest extends TestCase
{
    public function testOutputException()
    {
        $pdf = new SetaFpdf();

        $pdf->Output('F', __DIR__ . '/doc.pdf');
        $string = $pdf->Output('S', __DIR__ . '/doc.pdf');


        $this->assertStringEqualsFile(__DIR__ . '/doc.pdf', $string);
        unlink(__DIR__ . '/doc.pdf');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Font "testen" with style "b" not found.
     */
    public function testFontNotFound()
    {
        $pdf = new SetaFpdf();

        $pdf->SetFont('testen', 'b', 120);
    }

    public function testFont()
    {
        $pdf = new SetaFpdf();

        try {
            $pdf->SetFont('testen', 'b', 120);
            $this->fail('Font named "testen" exists.');
        } catch (\InvalidArgumentException $e) {
            // we expect a exception.
        }

        $pdf->AddFont('testen', 'b', \SetaPDF_Core_Font_Standard_Courier::create(
            $pdf->getManager()->getModule(Document::class)->get())
        );

        $pdf->SetFont('testen', 'b', 120);
    }
}