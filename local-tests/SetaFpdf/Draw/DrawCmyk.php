<?php

use setasign\SetaFpdf\SetaFpdf;

require_once '../../config.php';

$tester = new SetaFpdf();


$tester->AddPage();

$tester->SetFillColor(100, 68, 39, 25);
//$tester->SetDrawColor(0);
$tester->Rect(20, 20, 50, 50, 'F');

//$tester->SetLineWidth(2);
//$tester->SetDrawColor(255, 0, 255);
//$tester->Rect(20, 60, 50, 50, '');
//
//
//$tester->Rect(70, 70, 50, 50, 'F');
//
//
//$tester->SetFillColor(255, 255, 0);
//$tester->Rect(70, 120, 50, 50, 'F');
//
//
//$tester->SetDrawColor(0, 255 ,255);
//$tester->Line(20, 20, 70, 70);
//
//
//$tester->Line(0, 0, $tester->GetPageWidth(), $tester->GetPageHeight());


$tester->Output('F', __DIR__ . '/../../pdfs/' . '0' . '.pdf');

$GLOBALS['single'] = true;

require __DIR__ .  '/../../output.php';