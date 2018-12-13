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

$tester->Image(__DIR__ . '/../../../assets/images/logo.png');
$tester->Image(__DIR__ . '/../../../assets/images/logo.png');
$tester->Image(__DIR__ . '/../../../assets/images/logo.png');


$tester->Image(__DIR__ . '/../../../assets/images/logo.png', null, 20, 600, 600);

foreach ($tester->getInstances() as $key => $instance) {
    $instance->Output('F', __DIR__ . '/../../pdfs/' . $key . '.pdf');
}

require __DIR__ .  '/../../output.php';