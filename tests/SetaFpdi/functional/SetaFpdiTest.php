<?php

namespace setasign\tests\SetaFpdi\functional;

use PHPUnit\Framework\TestCase;
use setasign\SetaFpdi\SetaFpdi;


class SetaFpdiTest extends TestCase
{
    static protected $pageId;

    /**
     * @param array $pagesConfig
     * @return \SetaPDF_Core_Reader_String
     * @throws \SetaPDF_Core_Exception
     */
    private function createPdf(array $pagesConfig)
    {
        $writer = new \SetaPDF_Core_Writer_String();
        $document = new \SetaPDF_Core_Document($writer);
        $pages = $document->getCatalog()->getPages();
        foreach ($pagesConfig as list($format, $orientation)) {
            $pages->create($format, $orientation);
        }

        $document->save()->finish();

        return new \SetaPDF_Core_Reader_String($writer);
    }

    /**
     * @return SetaFpdi
     * @throws \SetaPDF_Core_Exception
     * @throws \SetaPDF_Core_Parser_CrossReferenceTable_Exception
     */
    public function testGetImportedPageSize()
    {
        $reader = $this->createPdf([[[100, 200], \SetaPDF_Core_PageFormats::ORIENTATION_PORTRAIT]]);
        $pdf = new SetaFpdi('P', 'pt');
        $pdf->setSourceFile($reader);
        self::$pageId = $pdf->importPage(1);

        $size = $pdf->getImportedPageSize(self::$pageId);
        $this->assertEquals([
            'width' => 100,
            'height' => 200,
            0 => 100,
            1 => 200,
            'orientation' => 'P'
        ], $size);

        return $pdf;
    }

    /**
     * @param SetaFpdi $pdf
     * @param $pageId
     * @return SetaFpdi
     * @depends testGetImportedPageSize
     */
    public function testGetTemplateSize(SetaFpdi $pdf)
    {
        $size = $pdf->getTemplateSize(self::$pageId);
        $this->assertEquals([
            'width' => 100,
            'height' => 200,
            0 => 100,
            1 => 200,
            'orientation' => 'P'
        ], $size);

        return $pdf;
    }

    /**
     * @param SetaFpdi $pdf
     * @return SetaFpdi
     * @depends testGetTemplateSize
     */
    public function testGetImportedPageSizeWithDifferentWidth(SetaFpdi $pdf)
    {
        $size = $pdf->getImportedPageSize(self::$pageId, 50);
        $this->assertEquals([
            'width' => 50,
            'height' => 100,
            0 => 50,
            1 => 100,
            'orientation' => 'P'
        ], $size);

        return $pdf;
    }

    /**
     * @param SetaFpdi $pdf
     * @depends testGetImportedPageSizeWithDifferentWidth
     */
    public function testGetImportedPageSizeWithDifferentHeight(SetaFpdi $pdf)
    {
        $size = $pdf->getImportedPageSize(self::$pageId, null, 100);
        $this->assertEquals([
            'width' => 50,
            'height' => 100,
            0 => 50,
            1 => 100,
            'orientation' => 'P'
        ], $size);

        self::$pageId = null;
    }

    public function testSeveralImportPageCalls()
    {
        $reader = $this->createPdf([
            [[100, 200], \SetaPDF_Core_PageFormats::ORIENTATION_PORTRAIT],
            [[100, 200], \SetaPDF_Core_PageFormats::ORIENTATION_LANDSCAPE],
        ]);
        $pdf = new SetaFpdi('P', 'pt');
        $pdf->setSourceFile($reader);

        $pageId = $pdf->importPage(1);
        $pageId2 = $pdf->importPage(1);
        $this->assertSame($pageId, $pageId2);

        $pageId = $pdf->importPage(2, \SetaPDF_Core_PageBoundaries::MEDIA_BOX);
        $pageId2 = $pdf->importPage(2, \SetaPDF_Core_PageBoundaries::ART_BOX);
        $this->assertNotSame($pageId, $pageId2);
    }

    /**
     * @throws \SetaPDF_Core_Exception
     * @throws \SetaPDF_Core_Parser_CrossReferenceTable_Exception
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidBoxName()
    {
        $pdf = new SetaFpdi();

        $reader = $this->createPdf([['a4', \SetaPDF_Core_PageFormats::ORIENTATION_PORTRAIT]]);
        $pdf->setSourceFile($reader);
        $pdf->importPage(1, 'anything');
    }
}