<?php

namespace setasign\tests\visual\SetaFpdf\Single;

use setasign\tests\VisualTestCase;

/**
 * @covers \setasign\SetaFpdf\Modules\Draw::rect()
 */
class RectTest extends VisualTestCase
{
    public function testDefaultRect()
    {
        $proxy = $this->getProxy();

        $proxy->AddPage();

        $proxy->Rect(10, 10, 10, 10);

        $this->assertProxySame($proxy, 1);
    }

    public function testFilledRect()
    {
        $proxy = $this->getProxy();

        $proxy->AddPage();

        $proxy->Rect(10, 10, 10, 10, 'F');

        $this->assertProxySame($proxy);
    }

    public function testBoth()
    {
        $proxy = $this->getProxy();

        $proxy->AddPage();

        $proxy->Rect(10, 10, 10, 10, 'DF');
        $proxy->Rect(30, 30, 10, 10, 'FD');

        $this->assertProxySame($proxy, 1);
    }
}