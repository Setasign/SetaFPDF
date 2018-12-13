<?php

use setasign\Fpdi\Fpdi;
use setasign\SetaFpdf\SetaFpdi;
use setasign\tests\TestProxy;

require_once '../config.php';


$tester = new TestProxy([
    new Fpdi(),
    new SetaFpdi()
]);


$tester->setSourceFile(__DIR__ . '/../../assets/pdfs/Boombastic-Box.pdf');
$pageId = $tester->importPage(1);

$tester->AddPage();

$tester->SetFont('Arial', '', 20);

$template = $tester->beginTemplate();

$tester->useImportedPage($pageId, 0, 0, 200, 100);
$tester->SetTextColor(255, 0, 0);
$tester->Text(10, 10, 'Just some example text. With some more words.');
$tester->endTemplate();

$tester->useTemplate($template);

for ($i = 10; $i > 0; $i--) {
    $tester->AddPage();
    $tester->useTemplate($template);
}

foreach ($tester->getInstances() as $key => $instance) {
    $instance->Output('F', __DIR__ . '/../pdfs/' . $key . '.pdf');
}

require '../output.php';