<?php

use setasign\SetaFpdf\SetaFpdf;
use setasign\tests\TestProxy;

require_once '../../config.php';

$tester = new TestProxy([
    new FPDF(),
    new SetaFpdf()
]);


$tester->AddPage();

$tester->SetFillColor(100, 68, 39);
$tester->SetDrawColor(0);
$tester->Rect(20, 20, 50, 50, 'F');

$tester->SetLineWidth(2);
$tester->SetDrawColor(255, 0, 255);
$tester->Rect(20, 60, 50, 50, '');


$tester->Rect(70, 70, 50, 50, 'F');


$tester->SetFillColor(255, 255, 0);
$tester->Rect(70, 120, 50, 50, 'F');


$tester->SetDrawColor(0, 255 ,255);
$tester->Line(20, 20, 70, 70);


$tester->Line(0, 0, $tester->GetPageWidth(), $tester->GetPageHeight());


foreach ($tester->getInstances() as $key => $instance) {
    $instance->Output('F', __DIR__ . '/../../pdfs/' . $key . '.pdf');
}

require __DIR__ .  '/../../output.php';