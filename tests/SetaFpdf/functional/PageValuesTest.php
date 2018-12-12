<?php

namespace setasign\tests\SetaFpdf\functional;


use setasign\SetaFpdf\SetaFpdf;
use setasign\tests\TestCase;
use setasign\tests\TestProxy;

class PageValuesTest extends TestCase
{
    /**
     * @return TestProxy
     * @throws \InvalidArgumentException
     */
    public function getProxy($orientation = 'P', $unit = 'mm', $size = 'A4')
    {
        return new TestProxy([
            new SetaFpdf($orientation, $unit, $size),
            new FpdfProperties($orientation, $unit, $size)
        ]);
    }

    public function getPageSizeProvider()
    {
        return [
            [],
            ['L', 'pt', 'A4'],
            ['P', 'cm', 'A4'],
            ['P', 'cm', [100, 200]],
            ['L', 'cm', [100, 200]],
            ['P', 'pt', [100, 200]],
            ['L', 'pt', [200, 100]],
        ];
    }

    /**
     * @param string $orientation
     * @param string $unit
     * @param string $size
     * @dataProvider getPageSizeProvider
     */
    public function testGetPageWidth($orientation = 'P', $unit = 'mm', $size = 'A4')
    {
        $proxy = $this->getProxy($orientation, $unit, $size);
        $proxy->GetPageWidth();

        // asserts are done in the proxy
        $proxy->w;
        $proxy->wPt;
    }

    /**
     * @param string $orientation
     * @param string $unit
     * @param string $size
     * @dataProvider getPageSizeProvider
     */
    public function testGetPageHeight($orientation = 'P', $unit = 'mm', $size = 'A4')
    {
        $proxy = $this->getProxy($orientation, $unit, $size);
        $proxy->GetPageHeight();

        // asserts are done in the proxy
        $proxy->h;
        $proxy->hPt;
    }
}