<?php
/**
 * Can't be displayed in any browser, inspect with Acrobat/Reader or Foxit.
 */


use setasign\SetaFpdf\SetaFpdf;
use setasign\tests\TestProxy;

require_once '../../config.php';

$tester = new TestProxy([
    new FPDF(),
    new SetaFpdf()
]);

$tester->SetDisplayMode('fullpage', 'two');

$tester->AddPage();
$tester->AddPage();
//$tester->AddPage();
//$tester->AddPage();

foreach ($tester->getInstances() as $key => $instance) {
    $instance->Output('F', __DIR__ . '/../../pdfs/' . $key . '.pdf');
}

require __DIR__ .  '/../../output.php';