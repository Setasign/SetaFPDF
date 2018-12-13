<?php

use setasign\SetaFpdf\SetaFpdf;
use setasign\tests\TestProxy;

require_once '../../config.php';

$tester = new TestProxy([
    new FPDF(),
    new SetaFpdf()
]);


$tester->AddPage();


$tester->SetFont('Arial', '', 20);

$tester->Text(20, 20, 'hallo');
$tester->Text(90, 40, 'mehr text');
$tester->Text(40, 90, 'ausprobieren');

$tester->SetTextColor(255 ,0 , 255);

$tester->Text(100, 20, 'farbig.');

foreach ($tester->getInstances() as $key => $instance) {
    $instance->Output('F', __DIR__ . '/../../pdfs/' . $key . '.pdf');
}

require __DIR__ .  '/../../output.php';