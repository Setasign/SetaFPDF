<?php

namespace setasign\tests\functional\SetaFpdf;


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
    public function testPageWidth($orientation = 'P', $unit = 'mm', $size = 'A4')
    {
        /** @var SetaFpdf $proxy */
        $proxy = $this->getProxy($orientation, $unit, $size);
        $width = $proxy->GetPageWidth();

        // asserts are done in the proxy
        $this->assertSame($width, $proxy->w);
    }

    /**
     * @param string $orientation
     * @param string $unit
     * @param string $size
     * @dataProvider getPageSizeProvider
     */
    public function testPageHeight($orientation = 'P', $unit = 'mm', $size = 'A4')
    {
        /** @var SetaFpdf $proxy */
        $proxy = $this->getProxy($orientation, $unit, $size);
        $height = $proxy->GetPageHeight();

        // asserts are done in the proxy
        $this->assertSame($height, $proxy->h);
    }
}