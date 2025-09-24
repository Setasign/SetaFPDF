<?php

namespace setasign\tests\visual\SetaFpdfTpl\One;


use setasign\tests\TestProxy;
use setasign\tests\VisualTestCase;

class SimpleTest extends VisualTestCase
{
    public function getProxy($orientation = 'P', $unit = 'mm', $size = 'A4')
    {
        return new TestProxy([
            new SetaFpdiCustom($orientation, $unit, $size),
            new FpdiCustom($orientation, $unit, $size)
        ]);
    }

    public function testSimple()
    {
        $proxy = $this->getProxy();

        $proxy->AddPage();

        $templateId = $proxy->beginTemplate();

        $proxy->SetFont('Arial', '', 20);

        $proxy->SetFillColor(255, 0, 255);
        $proxy->Rect(0, 0, $proxy->GetPageWidth(), $proxy->GetPageHeight(), 'F');
        $proxy->Cell(20 ,20, 'hallo, das ist ein test!');
        $proxy->endTemplate();

        $proxy->Rect(10, 10, 20, 20);

        $proxy->useTemplate($templateId, 20, 20 ,40, 40);

        $proxy->AddPage();
        $proxy->useTemplate($templateId, 20, 20, $proxy->GetPageWidth() - 40);

        $this->assertProxySame($proxy, 1.6, self::DPI);
    }
}