<?php

namespace setasign\tests\functional\SetaFpdf;


use setasign\SetaFpdf\SetaFpdf;
use setasign\tests\TestCase;
use setasign\tests\TestProxy;

class PropertiesTest extends TestCase
{
    /**
     * @return TestProxy
     * @throws \InvalidArgumentException
     */
    public function getProxy($orientation = 'P', $unit = 'mm', $size = 'A4')
    {
        return new TestProxy([
            new SetaFpdf($orientation, $unit, $size),
            new FpdfProperties($orientation, $unit, $size)
        ]);
    }

    public function testPage()
    {
        /** @var SetaFpdf $proxy */
        $proxy = $this->getProxy();
        $proxy->AddPage();
        $this->assertSame(1, $proxy->page);
        $proxy->AddPage();
        $this->assertSame(2, $proxy->page);
    }

    public function testPageCount()
    {
        /** @var SetaFpdf $proxy */
        $proxy = $this->getProxy();
        $proxy->AddPage();
        $proxy->AddPage();
        $this->assertSame(2, $proxy->pageCount);
        $proxy->AddPage();
        $proxy->AddPage();
        $this->assertSame(4, $proxy->pageCount);
    }

    public function testX()
    {
        /** @var SetaFpdf $proxy */
        $proxy = $this->getProxy();

        $x = $proxy->x;
        $this->assertSame($x, $proxy->GetX());

        $proxy->SetX(50);
        $this->assertSame(50, $proxy->x);
    }

    public function testY()
    {
        /** @var SetaFpdf $proxy */
        $proxy = $this->getProxy();

        $y = $proxy->y;
        $this->assertSame($y, $proxy->GetY());

        $proxy->SetY(50);
        $this->assertSame(50, $proxy->y);
    }

    public function testPageMargin()
    {
        /** @var SetaFpdf $proxy */
        $proxy = $this->getProxy();
        $left = 20;
        $top = 30;
        $right = 40;
        $bottom = 50;

        $proxy->SetMargins($left, $top);
        $this->assertSame($left, $proxy->lMargin);
        $this->assertSame($top, $proxy->tMargin);
        $this->assertSame($left, $proxy->rMargin); // default

        $proxy->SetMargins($left, $top, $right);
        $this->assertSame($left, $proxy->lMargin);
        $this->assertSame($top, $proxy->tMargin);
        $this->assertSame($right, $proxy->rMargin);

        $proxy->SetAutoPageBreak(true, $bottom);
        $this->assertSame($bottom, $proxy->bMargin);
    }
}