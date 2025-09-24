<?php

namespace setasign\tests\functional\SetaFpdf\Single;

use setasign\tests\TestCase;

class MarginTest extends TestCase
{
    public function testResetToMarginsOnPageBreak()
    {
        $proxy = $this->getProxy();

        $proxy->SetMargins(2, 5);
        $proxy->AddPage();
        $this->assertSame(2, $proxy->GetX());
        $this->assertSame(5, $proxy->GetY());

        $proxy->SetXY(50, 60);

        $proxy->AddPage();
        $this->assertSame(2, $proxy->GetX());
        $this->assertSame(5, $proxy->GetY());

        $proxy->SetMargins(4, 15);
        $proxy->AddPage();
        $this->assertSame(4, $proxy->GetX());
        $this->assertSame(15, $proxy->GetY());
    }
}