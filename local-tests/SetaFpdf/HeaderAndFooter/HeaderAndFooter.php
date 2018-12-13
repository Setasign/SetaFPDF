<?php

use setasign\SetaFpdf\SetaFpdf;
use setasign\tests\TestProxy;

require_once '../../config.php';

trait HeaderAndFooter
{
    public function Header()
    {
        $this->SetY(10);
        $this->Cell(0, 10, 'Header ' . $this->PageNo(), 1, 0, 'C');
    }

    public function Footer()
    {
        $this->SetY(-20);
        $this->Cell(0, 10, 'Footer ' . $this->PageNo(), 1, 0, 'C');
    }
}

class A extends FPDF
{
     use HeaderAndFooter;
}

class B extends SetaFpdf
{
    use HeaderAndFooter;
}

$tester = new TestProxy([
    new A(),
    new B()
]);

$tester->SetFont('Arial', 'B', 30);
$tester->AddPage();
$tester->AddPage();
$tester->AddPage();

foreach ($tester->getInstances() as $key => $instance) {
    $instance->Output('F', __DIR__ . '/../../pdfs/' . $key . '.pdf');
}

require __DIR__ .  '/../../output.php';