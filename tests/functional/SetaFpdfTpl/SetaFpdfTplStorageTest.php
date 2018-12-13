<?php

namespace setasign\tests\functional\SetaFpdfTpl;

use PHPUnit\Framework\TestCase;
use setasign\SetaFpdf\Modules\Document;
use setasign\SetaFpdf\Modules\Margin;
use setasign\SetaFpdf\SetaFpdfTpl;

class SetaFpdfTplStorageTest extends TestCase
{
    public function testTemplatePositionBehaviour()
    {
        $pdf = new SetaFpdfTpl();

        $pdf->SetXY(10, 20);
        $pdf->beginTemplate();
        $pdf->SetXY(30, 30);
        $pdf->endTemplate();

        $this->assertEquals(10, $pdf->GetX());
        $this->assertEquals(20, $pdf->GetY());
    }

    public function testTemplateColorBehaviour()
    {
        $pdf = new SetaFpdfTpl();
        $color = $pdf->getManager()->getColorState();

        $pdf->SetTextColor(255,0, 255);
        $this->assertEquals([1, 0, 1], $color->textColor);

        $pdf->beginTemplate();
        $pdf->SetTextColor(0, 255,0);
        $this->assertEquals([0, 1, 0], $color->textColor);
        $pdf->endTemplate();

        $this->assertEquals([1, 0, 1], $color->textColor);
    }

    public function testFontBehaviour()
    {
        $pdf = new SetaFpdfTpl();
        $font = $pdf->getManager()->getFontState();

        $pdf->SetFont('arial', 'b', 10);
        $this->assertEquals($font->getNewFont()->getFontName(), 'Helvetica-Bold');
        $this->assertEquals($font->getNewFontSize(), 10);

        $pdf->beginTemplate();
        $this->assertEquals($font->getNewFont()->getFontName(), 'Helvetica-Bold');
        $this->assertEquals($font->getNewFontSize(), 10);
        $pdf->SetFont('times', '', 20);
        $this->assertEquals($font->getNewFont()->getFontName(), 'Times-Roman');
        $this->assertEquals($font->getNewFontSize(), 20);
        $pdf->endTemplate();

        $this->assertEquals($font->getNewFont()->getFontName(), 'Helvetica-Bold');
        $this->assertEquals($font->getNewFontSize(), 10);
    }

    public function testAutoPageBreakBehaviour()
    {
        $pdf = new SetaFpdfTpl();

        $pdf->SetAutoPageBreak(false);
        $pdf->beginTemplate();
        $pdf->endTemplate();

        $this->assertEquals(false, $pdf->getManager()->getModule(Document::class)->pageBreakAllowed());

        $pdf->SetAutoPageBreak(true);
        $pdf->beginTemplate();
        $pdf->endTemplate();

        $this->assertEquals(true, $pdf->getManager()->getModule(Document::class)->pageBreakAllowed());
    }

    public function testMarginBehaviour()
    {
        $pdf = new SetaFpdfTpl();
        $margin = $pdf->getManager()->getModule(Margin::class);

        $pdf->SetMargins(10, 20 ,30);
        $pdf->SetAutoPageBreak(true, 200);


        $pdf->beginTemplate();

        $this->assertEquals($margin->getLeft(), 10);
        $this->assertEquals($margin->getTop(), 20);
        $this->assertEquals($margin->getRight(), 30);

        $pdf->SetMargins(40, 50, 60);

        $this->assertEquals($margin->getLeft(), 40);
        $this->assertEquals($margin->getTop(), 50);
        $this->assertEquals($margin->getRight(), 60);

        $pdf->endTemplate();


        $this->assertEquals($margin->getLeft(), 10);
        $this->assertEquals($margin->getTop(), 20);
        $this->assertEquals($margin->getRight(), 30);


        $pdf->beginTemplate();

        $this->assertEquals($margin->getLeft(), 10);
        $this->assertEquals($margin->getTop(), 20);
        $this->assertEquals($margin->getRight(), 30);

        $pdf->endTemplate();

        $pdf->SetMargins(11, 22, 33);

        $this->assertEquals($margin->getLeft(), 11);
        $this->assertEquals($margin->getTop(), 22);
        $this->assertEquals($margin->getRight(), 33);


        $pdf->beginTemplate();

        $this->assertEquals($margin->getLeft(), 11);
        $this->assertEquals($margin->getTop(), 22);
        $this->assertEquals($margin->getRight(), 33);

        $pdf->endTemplate();

    }
}