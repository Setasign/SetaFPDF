<?php

namespace setasign\tests\SetaFpdf\functional\Exception;

use setasign\tests\TestCase;

class PageTest extends TestCase
{
    public function getProxy($orientation = 'P', $unit = 'mm', $size = 'A4')
    {
        $proxy = parent::getProxy($orientation, $unit, $size);

        $proxy->SetFont('courier', '', 12);

        return $proxy;
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage No page has been added yet.
     */
    public function testFontNotSetTextEmpty()
    {
        $proxy = $this->getProxy();
        $proxy->Text(0, 0, '');
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage No page has been added yet.
     */
    public function testFontNotSetText()
    {
        $proxy = $this->getProxy();
        $proxy->Text(0, 0, 'testen');
    }

    /**
     * Surprisingly This should not throw an exception.
     */
    public function testFontNotSetCellEmpty()
    {
        $proxy = $this->getProxy();
        $proxy->Cell(200, 200, '');
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage No page has been added yet.
     */
    public function testFontNotSetCell()
    {
        $proxy =  $this->getProxy();
        $proxy->Cell(200, 200, 'hallo');
    }


    public function testFontNotSetMultiCellEmpty()
    {
        $proxy = $this->getProxy();
        $proxy->MultiCell(200, 200, '');
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage No page has been added yet.
     */
    public function testFontNotSetMultiCell()
    {
        $proxy = $this->getProxy();
        $proxy->MultiCell(200, 200, 'hallo');
    }

    public function testFontNotSetWriteEmpty()
    {
        $proxy = $this->getProxy();
        $proxy->Write(200, '');
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage No page has been added yet.
     */
    public function testFontNotSetWrite()
    {
        $proxy = $this->getProxy();
        $proxy->Write(200, 'hallo');
    }
}