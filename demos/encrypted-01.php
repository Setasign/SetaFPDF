<?php
use \setasign\SetaFpdf\SetaFpdf;

require_once '../vendor/autoload.php';

$pdf = new SetaFpdf();
$pdf->AddPage();
$pdf->AddFont('DejaVuSans', '', __DIR__ . '/../assets/fonts/DejaVu/DejaVuSans.ttf');
$pdf->SetFont('DejaVuSans', '', 20);
$pdf->Write(20, 'This document is encrypted with AES256.');

$document = $pdf->getManager()->getDocument()->get();

$secHandler = SetaPDF_Core_SecHandler_Standard_Aes256::factory(
    $document,
    'owner',
    'user',
    SetaPDF_Core_SecHandler::PERM_PRINT
);

$document->setSecHandler($secHandler);

$pdf->Output();