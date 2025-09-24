<?php

namespace setasign\tests\functional\SetaFpdf\Exception;

use setasign\tests\TestCase;

class FontTest extends TestCase
{
    public function testUndefinedFont()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Font "hawda" with style "" not found.');
        $proxy = $this->getProxy();
        $proxy->SetFont('hawda');
    }

    public function testFontNotSetTextEmpty()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('No font has been set');
        $proxy = $this->getProxy();
        $proxy->Text(0, 0, '');
    }

    public function testFontNotSetText()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('No font has been set');
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

    public function testFontNotSetCell()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('No font has been set');
        $proxy =  $this->getProxy();
        $proxy->Cell(200, 200, 'hallo');
    }

    public function testFontNotSetMultiCellEmpty()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('No font has been set');
        $proxy = $this->getProxy();
        $proxy->MultiCell(200, 200, '');
    }

    public function testFontNotSetMultiCell()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('No font has been set');
        $proxy = $this->getProxy();
        $proxy->MultiCell(200, 200, 'hallo');
    }

    public function testFontNotSetWriteEmpty()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('No font has been set');
        $proxy = $this->getProxy();
        $proxy->Write(200, '');
    }

    public function testFontNotSetWrite()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('No font has been set');
        $proxy = $this->getProxy();
        $proxy->Write(200, 'hallo');
    }
}