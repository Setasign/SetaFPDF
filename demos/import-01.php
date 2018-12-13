<?php
use \setasign\SetaFpdf\SetaFpdi;

require_once '../vendor/autoload.php';

$pdf = new SetaFpdi();

$pageCount = $pdf->setSourceFile(__DIR__ . '/../assets/pdfs/tektown/Brand-Guide.pdf');

for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
    $tpl = $pdf->importPage($pageNo);
    $pdf->AddPage();
    $pdf->useTemplate($tpl, ['adjustPageSize' => true]);
}

$pdf->Output();