<?php

namespace setasign\tests\SetaFpdf\visual\Single;

use setasign\tests\VisualTestCase;

/**
 * Class Text
 * @package setasign\SetaFpdf\visual\Single
 *
 * @covers \setasign\SetaFpdf\Modules\Text::text()
 */
class TextTest extends VisualTestCase
{
    public function testSimple()
    {
        $proxy = $this->getProxy();

        $proxy->AddPage();
        $proxy->SetFont('times', 'b', 11);

        $proxy->SetTextColor(255, 0, 255);
        $proxy->Text(5, 5, 'Hello, this is a simple test.');
        $proxy->SetTextColor(10, 15, 20);
        $proxy->Text(100, 5, 'More tests are sometimes better then less tests');

        $proxy->SetTextColor(144, 12, 233);
        $proxy->Text(5, 20, 'This is some text.');
        $proxy->SetFontSize(45);
        $proxy->Text(50, 120, 'More information.');

        $proxy->SetTextColor(255, 123, 13);
        $proxy->SetFontSize(55);
        $proxy->Text(10, 200, 'This is some more text.');

        $this->assertProxySame($proxy, 2);
    }
}