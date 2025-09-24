<?php

namespace setasign\tests\functional\SetaFpdf;

use PHPUnit\Framework\TestCase;
use setasign\SetaFpdf\Modules\Document;
use setasign\SetaFpdf\SetaFpdf;
use setasign\SetaPDF2\Core\Document\ObjectStreamCompressor;

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

    public function testFontNotFound()
    {
        $pdf = new SetaFpdf();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Font "testen" with style "b" not found.');
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

        $font = \SetaPDF_Core_Font_Standard_Courier::create($pdf->getManager()->getDocument()->get());
        $pdf->AddFont('testen', 'b', $font);

        $pdf->SetFont('testen', 'b', 120);
        $this->assertInstanceOf(SetaFpdf::class, $pdf);
    }

    public function testCompressedObjectStreams()
    {
        $pdf = new SetaFpdf();
        $pdf->AddPage();
        $pdf->AddFont('DejaVuSans', 'B', __DIR__ . '/../../../assets/fonts/DejaVu/DejaVuSans-Bold.ttf');
        $pdf->SetFont('DejaVuSans', 'B', 20);
        $pdf->Cell(0, 10, 'TESTING FONT SUBSETTING');

        $document = $pdf->getManager()->getDocument()->get();
        $compressor = new ObjectStreamCompressor($document);
        $compressor->register();

        $pdfString = $pdf->Output('S');

        $this->assertStringNotContainsString('/Font', $pdfString);
        $this->assertStringNotContainsString('/Page ', $pdfString);
    }
}