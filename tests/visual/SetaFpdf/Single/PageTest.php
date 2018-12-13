<?php

namespace setasign\tests\visual\SetaFpdf\Single;

use setasign\tests\VisualTestCase;

class PageTest extends VisualTestCase
{
    public function testAutoPageBreakSimple()
    {
        $proxy = $this->getProxy();
        $f = function () use($proxy) {
            $proxy->Image(__DIR__ . '/../../../../assets/images/logo.png',
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
}