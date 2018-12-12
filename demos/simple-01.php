<?php
use \setasign\SetaFpdf\SetaFpdf;

require_once '../vendor/autoload.php';

$pdf = new SetaFpdf();
$pdf->AddPage();
$pdf->AddFont('DejaVuSans', '', __DIR__ . '/../assets/fonts/DejaVu/DejaVuSans.ttf');
$pdf->SetFont('DejaVuSans', '', 20);
$pdf->Write(20, 'Love & Ζω!'); // Write in UTF-8
$pdf->Output();