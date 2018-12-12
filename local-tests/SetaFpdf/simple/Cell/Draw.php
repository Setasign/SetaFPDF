<?php

require_once '../../../config.php';

use setasign\SetaFpdf\SetaFpdf;
use setasign\tests\TestProxy;

$tester = new TestProxy([
    new FPDF(),
    new SetaFpdf()
]);


$tester->AddPage('', '', /*180 + 90*/0);

$fonts = [
    ['courier', ''],
    ['courier', 'b'],
    ['courier', 'bi'],
    ['courier', 'i'],
    ['helvetica', ''],
    ['helvetica', 'b'],
    ['helvetica', 'bi'],
    ['helvetica', 'i'],
    ['symbol', ''],
    ['times', ''],
    ['times', 'b'],
    ['times', 'bi'],
    ['times', 'i'],
    ['zapfdingbats', ''],
];

$tester->SetDrawColor(255, 0, 255);

foreach ($fonts as $font) {
    $tester->SetFont($font[0], $font[1], 20);
    $tester->Cell(null, 10, "hallo\ndas\nist ein\ntest!", 1, 2);
}

foreach ($tester->getInstances() as $key => $instance) {
    $instance->SetFont('courier');
    $instance->Text(10, 10, get_class($instance));
    $instance->Output('F', __DIR__ . '/../../pdfs/' . $key . '.pdf');
}

require __DIR__ .  '/../../output.php';