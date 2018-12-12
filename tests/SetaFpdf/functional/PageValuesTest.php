<?php

namespace setasign\tests\SetaFpdf\functional;


use setasign\tests\TestCase;

class PageValuesTest extends TestCase
{
    public function testGetPageWidth()
    {
        $proxy = $this->getProxy();
        $proxy->GetPageWidth();
    }

    public function testGetPageHeight()
    {
        $proxy = $this->getProxy();
        $proxy->GetPageWidth();
    }
}