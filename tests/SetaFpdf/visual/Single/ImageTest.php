<?php

namespace setasign\tests\SetaFpdf\visual\Single;

use setasign\tests\VisualTestCase;

/**
 * Class Image
 * @package setasign\FPDF\visual\Draw
 *
 * @covers \setasign\SetaFpdf\Modules\Draw::Image()
 */
class ImageTest extends VisualTestCase
{
    public function getRandomImage()
    {
        $images = glob(__DIR__ . '/../../../../assets/images/*');
        return $images[array_rand($images)];
    }

    public function testDraw()
    {
        $proxy = $this->getProxy();

        $proxy->AddPage();
        $proxy->Image($this->getRandomImage());

        $this->assertProxySame($proxy);
    }

    public function testDrawWithGivenHeight()
    {
        $proxy = $this->getProxy();

        $proxy->AddPage();
        $proxy->Image($this->getRandomImage(), null, null, 0, 1000);

        $this->assertProxySame($proxy);
    }

    public function testDrawWithGivenWidth()
    {
        $proxy = $this->getProxy();

        $proxy->AddPage();
        $proxy->Image($this->getRandomImage(), null, null, 1000);

        $this->assertProxySame($proxy);
    }
}