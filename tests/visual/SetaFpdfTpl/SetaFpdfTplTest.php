<?php

namespace setasign\tests\visual\SetaFpdfTpl;

use setasign\Fpdi\Fpdi;
use setasign\SetaFpdf\SetaFpdfTpl;
use setasign\tests\TestProxy;
use setasign\tests\VisualTestCase;

class SetaFpdfTplTest extends VisualTestCase
{
    /**
     * @return TestProxy
     * @throws \InvalidArgumentException
     */
    public function getProxy($orientation = 'P', $unit = 'mm', $size = 'A4')
    {
        return new TestProxy([
            new SetaFpdfTpl($orientation, $unit, $size),
            new Fpdi($orientation, $unit, $size)
        ]);
    }

    public function testWritingSpecialTemplate()
    {
        $proxy = $this->getProxy();

        $proxy->SetFont('arial');

        $proxy->SetTextColor(255, 0, 255);

        $template = $proxy->beginTemplate();
        $proxy->SetTextColor(0, 255, 255);
        $proxy->SetFont(null, 'b', 10);
        $proxy->Text(10, 10, 'Simple test');
        $proxy->endTemplate();

        $proxy->AddPage();
        $proxy->useTemplate($template);

        $proxy->Text(20, 20, 'test 2');

        $this->assertProxySame($proxy);
    }

    public function testWriteTemplateInTemplate()
    {
        $proxy = $this->getProxy();

        $proxy->SetFont('arial');

        $proxy->SetTextColor(255, 0, 255);

        $template = $proxy->beginTemplate();
        $proxy->SetTextColor(0, 255, 255);
        $proxy->SetFont(null, 'b', 10);
        $proxy->Text(10, 10, 'Simple test');
        $proxy->endTemplate();

        $template0 = $proxy->beginTemplate();
        $proxy->SetFont(null, '', 200);
        $proxy->useTemplate($template);
        $proxy->SetFont(null, 'i', 20);
        $proxy->Text(20, 20, 'test 2');
        $proxy->endTemplate();

        $proxy->AddPage();
        $proxy->useTemplate($template0);
        $proxy->useTemplate($template0, 10, 10, 200, 200);
        $proxy->useTemplate($template0, 0, 0, 400, 400);

        $this->assertProxySame($proxy, 1);
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testAdjustPageSize()
    {
        $proxy = $this->getProxy();

        $templateId = $proxy->beginTemplate(200, 200);
        $proxy->endTemplate();

        $proxy->beginTemplate(250, 250);
        $proxy->useTemplate($templateId, 10, 10, null, null, true);
    }

    public function testColorMode()
    {
        $proxy = $this->getProxy();

        $proxy->SetDrawColor(0, 255, 0);
        $proxy->SetFillColor(255, 0, 255);

        $proxy->AddPage();
        $proxy->Rect(0, 0, 100, 100, 'F');

        $proxy->beginTemplate(0, 0);
        $proxy->SetDrawColor(255, 0, 255);
        $proxy->SetFillColor(0, 255, 0);
        $proxy->endTemplate();

        $proxy->Rect(0, 100, 100, 100, 'F');

        $this->assertProxySame($proxy);
    }
}