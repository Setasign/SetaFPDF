<?php

use setasign\Fpdi\Fpdi;
use setasign\SetaFpdf\SetaFpdfTpl;
use setasign\tests\TestProxy;

require_once '../config.php';


$tester = new TestProxy([
    new Fpdi(),
    new SetaFpdfTpl()
]);

$tester->AddPage();


$template = $tester->beginTemplate();
$tester->SetFont('Helvetica', '', 10);
$tester->Text(10, 10, 'HEADING');

$tester->Text(20, 20, 'TEST');

$tester->endTemplate();

$tester->useTemplate($template);

//for ($i = 9; $i > 0; $i--) {
//    $tester->AddPage();
//    $tester->useTemplate($template);
//}

foreach ($tester->getInstances() as $key => $instance) {
    $instance->Output('F', __DIR__ . '/../pdfs/' . $key . '.pdf');
}
require '../output.php';