<?php

namespace setasign\tests\visual\SetaFpdf\Special;

use setasign\tests\VisualTestCase;

class MarginTest extends VisualTestCase
{
    public function testSetTopMarginBeforeAddPage()
    {
        $proxy = $this->getProxy();

        $proxy->SetTopMargin(30);

        $proxy->AddPage();

        $proxy->SetFont('arial', '', 12);

        $proxy->Cell(20, 20, 'Test');

        $this->assertProxySame($proxy, 0.7);
    }

    public function testSetTopMarginAfterAddPage()
    {
        $proxy = $this->getProxy();

        $proxy->AddPage();

        $proxy->SetTopMargin(30);

        $proxy->SetFont('arial', '', 12);

        $proxy->Cell(20, 20, 'Test');

        $this->assertProxySame($proxy, 0.7);
    }
}