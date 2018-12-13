<?php

use setasign\SetaFpdf\SetaFpdf;
use setasign\tests\TestProxy;

require_once '../../config.php';

$tester = new TestProxy([
    new FPDF(),
    new SetaFpdf()
]);

$tester->SetDrawColor(255, 55, 77);


$tester->AddPage();
$tester->SetFont('Arial', '', 20);

/*
// wir unterstützen auch umlaute.
$tester->MultiCell(90, 10, 'hallo, wir sollen hier wirkich die umbrüche testen.', 1);

$tester->SetTextColor(255, 0, 255);
$tester->MultiCell(90, 10, 'farbig.', 1);

$tester->SetTextColor(0);
$tester->MultiCell(90, 10, 'mehr text', 1, 'C');

$tester->SetDrawColor(255, 255, 0);
$tester->MultiCell(90, 10, 'mehr text', 1, 'C');

$tester->SetTextColor(0, 255,  255);
$tester->MultiCell(90, 10, 'mehr text', 1, 'C');

$tester->SetTextColor(0);

*/
$tester->MultiCell(
    null,
    10,
    'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et' .
    'dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita' .
    'kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur' .
    'sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam' .
    'voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata' .
    'sanctus est Lorem ipsum dolor sit amet.');

$tester->Ln(20);

$tester->MultiCell(
    null,
    10,
    'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et' .
    'dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita' .
    'kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur' .
    'sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam' .
    'voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata' .
    'sanctus est Lorem ipsum dolor sit amet.');


foreach ($tester->getInstances() as $key => $instance) {
    $instance->Output('F', __DIR__ . '/../../pdfs/' . $key . '.pdf');
}

require __DIR__ .  '/../../output.php';