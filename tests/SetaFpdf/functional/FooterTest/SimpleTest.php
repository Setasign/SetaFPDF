<?php

namespace setasign\tests\SetaFpdf\functional;

use setasign\tests\SetaFpdf\functional\FooterTest\FpdfCustom;
use setasign\tests\SetaFpdf\functional\FooterTest\MethodTrait;
use setasign\tests\SetaFpdf\functional\FooterTest\SetaFpdfCustom;
use setasign\tests\TestCase;
use setasign\tests\TestProxy;

class SimpleTest extends TestCase
{
    public function getProxy($orientation = 'P', $unit = 'mm', $size = 'A4')
    {
        return new TestProxy([
            new FpdfCustom($orientation, $unit, $size),
            new SetaFpdfCustom($orientation, $unit, $size)
        ]);
    }

    public function testFooterYValue()
    {
        $proxy = $this->getProxy();

        $proxy->AddPage();
        $proxy->SetFont('arial', '', 12);

        for ($i = 0; $i <= 19; $i++) {
            $proxy->Write(20, 'test');$proxy->Ln();
        }

        $this->assertEquals(150.00125, $proxy->GetY());

        foreach ($proxy->getInstances() as $instance) {
            /** @var MethodTrait $instance */
            $this->assertEquals(
                270.00125,
                $instance->footerInformation[1]['y'],
                get_class($instance)
            );
        }
    }
}