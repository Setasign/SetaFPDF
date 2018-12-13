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

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage No page has been added yet.
     */
    public function testWithTextEmpty()
    {
        $proxy = $this->getProxy();
        $proxy->Text(0, 0, '');
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage No page has been added yet.
     */
    public function testWithText()
    {
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

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage No page has been added yet.
     */
    public function testWithSetCell()
    {
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

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage No page has been added yet.
     */
    public function testWithMultiCell()
    {
        $proxy = $this->getProxy();
        $proxy->MultiCell(200, 200, 'hallo');
    }

    public function testWithWriteEmpty()
    {
        $proxy = $this->getProxy();
        $proxy->Write(200, '');
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage No page has been added yet.
     */
    public function testWithWrite()
    {
        $proxy = $this->getProxy();
        $proxy->Write(200, 'hallo');
    }
}
