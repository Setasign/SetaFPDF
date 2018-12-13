<?php

namespace setasign\tests\visual\SetaFpdf\Single;

use setasign\tests\VisualTestCase;

/**
 * Class Line
 * @package setasign\FPDF\visual\Draw
 *
 * @covers \setasign\SetaFpdf\Modules\Draw::line()
 */
class LineTest extends VisualTestCase
{
    public function testSimple()
    {
        $proxy = $this->getProxy();

        $proxy->AddPage();

        $proxy->Line(0, 0, 20, 30);
        $proxy->Line(30, 30, 40, 50);

        $this->assertProxySame($proxy);
    }

    public function testRotated()
    {
        $proxy = $this->getProxy();

        $proxy->AddPage('', '', 90);

        $proxy->SetDrawColor(255, 0, 255);
        $proxy->Line(30, 30, 10, 20);

        $proxy->Line(0, 0, 90, 90);

        $this->assertProxySame($proxy);
    }
}