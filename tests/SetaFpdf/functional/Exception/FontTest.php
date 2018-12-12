<?php

namespace setasign\tests\SetaFpdf\functional\Exception;

use setasign\tests\TestCase;

class FontTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Font "hawda" with style "" not found.
     */
    public function testUndefinedFont()
    {
        $proxy = $this->getProxy();
        $proxy->SetFont('hawda');
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage No font has been set
     */
    public function testFontNotSetTextEmpty()
    {
        $proxy = $this->getProxy();
        $proxy->Text(0, 0, '');
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage No font has been set
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
     * @expectedExceptionMessage No font has been set
     */
    public function testFontNotSetCell()
    {
        $proxy =  $this->getProxy();
        $proxy->Cell(200, 200, 'hallo');
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage No font has been set
     */
    public function testFontNotSetMultiCellEmpty()
    {
        $proxy = $this->getProxy();
        $proxy->MultiCell(200, 200, '');
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage No font has been set
     */
    public function testFontNotSetMultiCell()
    {
        $proxy = $this->getProxy();
        $proxy->MultiCell(200, 200, 'hallo');
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage No font has been set
     */
    public function testFontNotSetWriteEmpty()
    {
        $proxy = $this->getProxy();
        $proxy->Write(200, '');
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage No font has been set
     */
    public function testFontNotSetWrite()
    {
        $proxy = $this->getProxy();
        $proxy->Write(200, 'hallo');
    }
}