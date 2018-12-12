<?php

require_once '../../../config.php';

use setasign\SetaFpdf\SetaFpdf;
use setasign\tests\TestProxy;

$tester = new TestProxy([
    new FPDF(),
    new SetaFpdf()
]);

$tester->AddPage();
//$link = $tester->AddLink();
//$tester->SetLink($link, 0, 1);
$tester->Link(70, 70, 30, 30, 'https://google.de');

$tester->Link(20, 20, 30 ,30, 'https://google.de');
$tester->Rect(20, 20, 30, 30, 'F');


$tester->SetFont('courier', '', 10);
$tester->SetXY(60, 60);
$tester->Cell(20, 0, 'testen', 0, 0, '', false, 'facebook.com');

$tester->Image(__DIR__ . '/../../../../assets/images/logo.png', 20, 90, 100, 0, '', 'link.com');


$tester->SetTextColor(255, 0, 255);
foreach ($tester->getInstances() as $key => $instance) {
    /** @var FPDF $instance */
    $instance->SetFont('courier', '', 10);
    $instance->Text(10, 10, get_class($instance));
    $instance->Output('F', __DIR__ . '/../../pdfs/' . $key . '.pdf');
}

require __DIR__ .  '/../../output.php';