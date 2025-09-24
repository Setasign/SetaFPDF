<?php

namespace setasign\tests\functional\SetaFpdfTpl;

use PHPUnit\Framework\TestCase;
use setasign\SetaFpdf\SetaFpdf;
use setasign\SetaFpdf\SetaFpdfTpl;


class SetaFpdfTplTest extends TestCase
{
    /**
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function testGetTemplateSize1()
    {
        $pdf = new SetaFpdfTpl();

        $template = $pdf->beginTemplate();
        $pdf->endTemplate();

        $this->assertEquals(
            [
                'width' => 210.0015555555555,
                'height' => 297.0000833333333,
                0 => 210.0015555555555,
                1 => 297.0000833333333,
                'orientation' => 'P'
            ],
            $pdf->getTemplateSize($template)
        );
    }

    public function testGetTemplateSize2()
    {
        $pdf = new SetaFpdfTpl();

        $template = $pdf->beginTemplate(100, 200);
        $pdf->endTemplate();

        $this->assertEqualsWithDelta(
            [
                'width' => 100.,
                'height' => 200.,
                0 => 100,
                1 => 200,
                'orientation' => 'P'
            ],
            $pdf->getTemplateSize($template),
            0.00001
        );
    }

}