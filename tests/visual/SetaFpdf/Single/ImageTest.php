<?php

namespace setasign\tests\visual\SetaFpdf\Single;

use setasign\tests\VisualTestCase;

/**
 * Class Image
 *
 * @covers \setasign\SetaFpdf\Modules\Draw::Image()
 */
class ImageTest extends VisualTestCase
{
    public function testDraw()
    {
        $proxy = $this->getProxy();

        $proxy->AddPage();
        $proxy->Image($this->getAssetsDir() . '/images/logo.png');

        $this->assertProxySame($proxy);
    }

    public function testDrawWithGivenHeight()
    {
        $proxy = $this->getProxy();

        $proxy->AddPage();
        $proxy->Image($this->getAssetsDir() . '/images/logo.png', null, null, 0, 1000);

        $this->assertProxySame($proxy);
    }

    public function testDrawWithGivenWidth()
    {
        $proxy = $this->getProxy();

        $proxy->AddPage();
        $proxy->Image($this->getAssetsDir() . '/images/logo.png', null, null, 1000);

        $this->assertProxySame($proxy);
    }
}
