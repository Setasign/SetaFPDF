<?php

namespace setasign\tests\functional\SetaFpdf\Exception;

use setasign\tests\TestCase;

class MissingPageTest extends TestCase
{
    public function getProxy($orientation = 'P', $unit = 'mm', $size = 'A4')
    {
        $proxy = parent::getProxy($orientation, $unit, $size);

        $proxy->SetFont('courier', '', 12);

        return $proxy;
    }

    public function testWithTextEmpty()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('No page has been added yet.');
        $proxy = $this->getProxy();
        $proxy->Text(0, 0, '');
    }

    public function testWithText()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('No page has been added yet.');
        $proxy = $this->getProxy();
        $proxy->Text(0, 0, 'testen');
    }

    /**
     * Surprisingly This should not throw an exception.
     */
    public function testWithCellEmpty()
    {
        $proxy = $this->getProxy();
        $proxy->Cell(200, 200, '');
    }

    public function testWithSetCell()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('No page has been added yet.');
        $proxy =  $this->getProxy();
        $proxy->Cell(200, 200, 'hallo');
    }

    /**
     * Note this test behaves differently on php5.6
     *
     * @requires PHP 7.0
     */
    public function testWithMultiCellEmpty()
    {
        $proxy = $this->getProxy();
        $proxy->MultiCell(200, 200, '');
    }

    public function testWithMultiCell()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('No page has been added yet.');
        $proxy = $this->getProxy();
        $proxy->MultiCell(200, 200, 'hallo');
    }

    public function testWithWriteEmpty()
    {
        $proxy = $this->getProxy();
        $proxy->Write(200, '');
    }

    public function testWithWrite()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('No page has been added yet.');
        $proxy = $this->getProxy();
        $proxy->Write(200, 'hallo');
    }
}
