<?php

use \setasign\SetaFpdf\SetaFpdf;

require_once '../vendor/autoload.php';

class PdfA extends SetaFpdf
{
    private function makePdfA()
    {
        $document = $this->getManager()->getDocument()->get();

        \SetaPDF_Core_XmpHelper_PdfA::update($document, '3', 'B');

        $outputIntents = $document->getCatalog()->getOutputIntents();
        $iccStream = \SetaPDF_Core_IccProfile_Stream::create(
            $document, __DIR__ . '/../assets/icc/sRGB_ICC_v4_Appearance.icc'
        );
        $outputIntent = \SetaPDF_Core_OutputIntent::createByProfile(
            SetaPDF_Core_OutputIntent::SUBTYPE_GTS_PDFA1, $iccStream
        );
        $outputIntents->addOutputIntent($outputIntent);
    }

    public function Output($dest = '', $name = '')
    {
        $this->makePdfA();
        return parent::Output($dest, $name);
    }
}

$pdf = new PdfA();
$pdf->AddPage();
$pdf->AddFont('DejaVuSans', '', __DIR__ . '/../assets/fonts/DejaVu/DejaVuSans.ttf');
$pdf->SetFont('DejaVuSans', '', 20);
$pdf->Write(20, 'I am a PDF/A-3b document!');
$pdf->Output('D');
