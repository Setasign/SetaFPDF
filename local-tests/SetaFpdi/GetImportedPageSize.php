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

$tester->getImportedPageSize($pageId, 200);

foreach ($tester->getInstances() as $instance) {
    var_dump($instance->getImportedPageSize($pageId, 200));
}

$tester->getImportedPageSize($pageId);