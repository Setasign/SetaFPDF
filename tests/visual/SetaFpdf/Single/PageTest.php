<?php

namespace setasign\tests\visual\SetaFpdf\Single;

use setasign\tests\VisualTestCase;

/**
 * Class PageTest
 *
 * With different AddPage
 */
class PageTest extends VisualTestCase
{
    public function testAutoPageBreakSimple()
    {
        $proxy = $this->getProxy();
        $f = function () use ($proxy) {
            $proxy->Image(
                $this->getAssetsDir() . '/images/logo.png',
                null,
                null,
                0,
                1200
            );
        };

        $proxy->AddPage();
        $f();

        $proxy->SetAutoPageBreak(false);
        $proxy->AddPage();
        $f();

        $proxy->SetAutoPageBreak(true);
        $proxy->AddPage();
        $f();

        $this->assertProxySame($proxy);
    }

    public function testAddPagesWithDefault()
    {
        $proxy = $this->getProxy('L', 'cm', 'A3');
        $proxy->AddPage();
        $proxy->AddPage();
        $this->assertProxySame($proxy);
    }

    public function testAddPagesWithOwnValueAndFallback()
    {
        $proxy = $this->getProxy('L', 'cm', 'A3');
        $proxy->AddPage();
        $proxy->AddPage('P', 'A5', 180);
        $proxy->AddPage(null, null, 0);
        $this->assertProxySame($proxy);
    }

    public function testAddPagesWithOwnSize()
    {
        $proxy = $this->getProxy('L', 'cm', [20, 30]);
        $proxy->AddPage();
        $proxy->AddPage('P', [50, 75], 180);
        $proxy->AddPage(null, null, 0);
        $proxy->AddPage('P', [10, 15], 90);
        $proxy->AddPage(null, [100, 25], 0);
        $this->assertProxySame($proxy);
    }
}
