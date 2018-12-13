<?php

namespace setasign\tests\visual\SetaFpdf\Single;

use setasign\tests\VisualTestCase;

/**
 * @covers \setasign\SetaFpdf\Modules\Cell::cell()
 */
class CellTest extends VisualTestCase
{
    /**
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function testSimple()
    {
        $proxy = $this->getProxy();

        $proxy->AddPage();
        $proxy->SetFont('courier', 'b', 10);

        $proxy->Cell(100, 20, 'testen ist wichtig.');
        $proxy->Cell(100, 20, 'random');
        $proxy->Cell(100, 20, 'text', 0, 1);
        $proxy->Cell(100, 20, 'with and without', 0, 1);
        $proxy->Cell(100, 20, ' ',0, 2);
        $proxy->Cell(100, 20, 'and more stuff', 1, 0, 2);
        $proxy->Cell(100, 20, 'hallo', 2);

        // Higher tolerance, due to slightly moving text. (Visisble at 800% Zoom)
        $this->assertProxySame($proxy, 5);
    }

    /**
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function testAlignment()
    {
        $proxy = $this->getProxy();

        $proxy->AddPage();

        $proxy->SetFont('courier', '', 10);

        $proxy->Cell(100, 20, 'test', 0, 2, 'R');
        $proxy->Cell(100, 20, 'test', 0, 2, 'L');
        $proxy->Cell(100, 20, 'test', 0, 2, 'C');

        $this->assertProxySame($proxy, 1);
    }

    /**
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function testBorder()
    {
        $proxy = $this->getProxy();

        $proxy->AddPage();
        $proxy->SetFont('arial');
        $proxy->SetFillColor(200, 0, 0);

        $proxy->Cell(50, 5, 'test', 1, 2, 'C', true);
        $proxy->Cell(0, 10, 'test', 1, 2, 'C');

        $proxy->Ln(5);
        $proxy->Cell(0, 5, 'Some text.', 'LR', 1, 'C');

        $proxy->SetLineWidth(3);
        $proxy->SetDrawColor(0, 200, 0);

        $proxy->Ln();
        $proxy->Cell(0, 5, 'Some text.', 'BT', 1, 'C');

        $this->assertProxySame($proxy, .17);
    }

    public function testLineBreak()
    {
        $proxy = $this->getProxy();

        $proxy->AddPage();
        $proxy->SetFont('arial');
        $proxy->SetFillColor(200, 0, 0);

        $proxy->Write(20, "test");
        $proxy->Cell(20, 10, "test", 0, true, '', false, '');
        $proxy->Cell(20, 10, "test", 0, true, '', false, '');
        $proxy->Cell(20, 10, "test", 0, true, '', false, '');
        $proxy->Cell(20, 10, "test", true, true, '', false, '');

        $this->assertProxySame($proxy, .1);
    }
}