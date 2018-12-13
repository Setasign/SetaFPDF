<?php

namespace setasign\tests\visual\SetaFpdf\Single;

use setasign\tests\VisualTestCase;

/**
 * @covers \setasign\SetaFpdf\Modules\Cell::multiCell()
 */
class MultiCellTest extends VisualTestCase
{
    public function testMultiCell()
    {
        $proxy = $this->getProxy();

        $proxy->AddPage();
        $proxy->SetFont('courier', '', 20);

        $proxy->MultiCell(
            100,
            10,
            'hallo, das sollte das tun was es soll, wenn ich viel text schreibe sollte er irgendwann einfach ' .
            'so umbrechen.'
        );

        $this->assertProxySame($proxy);
    }

    public function testAlign()
    {
        $proxy = $this->getProxy('P', 'pt', 'A3');

        $proxy->AddPage();
        $proxy->SetFont('courier', '', 10);

        $proxy->MultiCell(100, 20, 'Testing is a good thing. Also line breaks should be tested.', 0, 'L');
        $proxy->MultiCell(100, 20, 'Testing is a good thing. Also line breaks should be tested.', 0, 'C');
        $proxy->MultiCell(100, 20, 'Testing is a good thing. Also line breaks should be tested.', 0, 'R');
        $proxy->MultiCell(100, 20, 'Testing is a good thing. Also line breaks should be tested.', 0, 'J');

        $this->assertProxySame($proxy, 0.001, 72);
    }

    public function testManualLineBreak()
    {
        $proxy = $this->getProxy();

        $proxy->AddPage();
        $proxy->SetFont('courier', '', 18);

        $proxy->MultiCell(
            100,
            20,
            'Testen da, hier und dort. Viele tests die einige dinge abdecken.' . "\n" .
            'Alles gut.'
        );

        $this->assertProxySame($proxy);
    }

    public function testBorder()
    {
        $proxy = $this->getProxy('P', 'pt', 'A3');

        $proxy->AddPage();
        $proxy->SetFont('courier', '', 10);

        $proxy->MultiCell(100, 20, 'A simple ', 1, 'R');
        $proxy->MultiCell(100, 20, 'Another test', 'R');
        $proxy->MultiCell(100, 20, 'Another test', 'L');
        $proxy->MultiCell(100, 20, 'Another test', 'B');
        $proxy->MultiCell(100, 20, 'Another test', 'T');
        $proxy->MultiCell(100, 20, 'Another test', 'LR');
        $proxy->MultiCell(100, 20, 'Another test', 'TB');
        $proxy->Ln();
        $proxy->MultiCell(100, 20, 'Another test', 'RLB');

        $proxy->Ln();
        $proxy->SetFillColor(255, 0, 0);
        $proxy->SetDrawColor(0, 255, 0);
        $proxy->SetLineWidth(5);
        $proxy->MultiCell(100, 20, 'Another test', 'RLB', 'J', true);

        $this->assertProxySame($proxy, 0.15);
    }

    public function testVeryLongWord()
    {
        $proxy = $this->getProxy('P', 'pt', 'A4');

        $proxy->AddPage();
        $proxy->SetFont('arial', '', 10);

        $proxy->MultiCell(0, 20, str_repeat('HelloWorld', 30));

        $this->assertProxySame($proxy, 2);
    }

    public function testToLessSpace()
    {
        $proxy = $this->getProxy('P', 'pt', 'A4');

        $proxy->AddPage();
        $proxy->SetFont('arial', '', 10);

        $proxy->SetLineWidth(0.1);
        $proxy->SetDrawColor(255, 0, 0);

        $proxy->MultiCell(3, 11, 'nnn', 1);
        $proxy->MultiCell(11, 11, 'nnn', 1);

        $proxy->MultiCell(22, 11, 'abcde', 1);

        $proxy->MultiCell(40, 11, 'aVeryLongWordWhichNeedsToBeDistributedToSeveralLines', 1);
        $proxy->MultiCell(40, 11, 'some short Words and aVeryLongWordWhichNeedsToBeDistributedToSeveralLines', 1);

        $this->assertProxySame($proxy, 0.15, 72);
    }
}