<?php

use setasign\SetaFpdf\Modules\Document;
use setasign\SetaFpdf\SetaFpdf;

require_once '../../config.php';

$pdf = new SetaFpdf();

$pdf->AddPage();
$pdf->SetTextColor(0);
$pdf->SetFont('helvetica', 'bu', 10);
$pdf->Text(20, 20, 'Testen');

$document = new SetaPDF_Core_Document();
$page = $document->getCatalog()->getPages()->create(SetaPDF_Core_PageFormats::A4);

$textBlock = new SetaPDF_Core_Text_Block(
    SetaPDF_Core_Font_Standard_HelveticaBold::create($document),
    10
);
$textBlock->setText('Testen');
$textBlock->setTextColor([0]);
$textBlock->setUnderline(true);


$textBlock->draw(
    $page->getCanvas(),
    20 + 37,
    $page->getHeight() - (20 + 39)
);

foreach ([$document, $pdf->getManager()->getDocument()->get()] as $key => $documentI) {
    /** @var $documentI SetaPDF_Core_Document */
    $documentI->setWriter(new SetaPDF_Core_Writer_File(__DIR__ . '/../../pdfs/' . $key . '.pdf'))->save()->finish();
}

require __DIR__ . '/../../output.php';
