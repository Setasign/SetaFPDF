<?php

namespace setasign\tests\visual\SetaFpdf\Single;

use setasign\tests\VisualTestCase;

/**
 * Class Write
 *
 * @covers \setasign\SetaFpdf\Modules\Cell::write()
 */
class WriteTest extends VisualTestCase
{
    public function testWrite()
    {
        $proxy = $this->getProxy();

        $proxy->SetFont('arial');
        $proxy->AddPage();

        $proxy->Write(2, 'Da kommt ein sehr langer text und ich weiss nicht mehr wie sich dieses ding verhaelt...');
        $proxy->Write(2, 'Da kommt ein sehr langer text und ich weiss nicht mehr wie sich dieses ding verhaelt...');
        $proxy->Write(2, 'Da kommt ein sehr langer text und ich weiss nicht mehr wie sich dieses ding verhaelt...');
        $proxy->Write(2, 'Da kommt ein sehr langer text und ich weiss nicht mehr wie sich dieses ding verhaelt...');
        $proxy->Write(2, 'Da kommt ein sehr langer text und ich weiss nicht mehr wie sich dieses ding verhaelt...');

        $this->assertProxySame($proxy, 4);
    }

    public function testWriteWithLineBreak()
    {
        $proxy = $this->getProxy();

        $proxy->SetFont('arial');
        $proxy->AddPage();

        $proxy->Write(2, 'Da kommt ein sehr langer text und ich weiss nicht mehr wie sich dieses ding verhaelt...');
        $proxy->Write(2, 'Da kommt ein sehr langer text und ich weiss nicht' . "\n" . 'mehr wie sich dieses ding verhaelt...');

        $this->assertProxySame($proxy, 2);
    }

    public function testWriteLong()
    {
        $proxy = $this->getProxy();

        $proxy->SetFont('arial');
        $proxy->AddPage();

        $proxy->Write(6, str_repeat('HelloWorld', 20));

        $proxy->Ln();

        $proxy->Write(6, 'test');
        $proxy->Write(6, str_repeat('HelloWorld', 20));

        $this->assertProxySame($proxy, 14.64);
    }

    public function testWriteEmpty()
    {
        $proxy = $this->getProxy();

        $proxy->SetFont('arial');
        $proxy->AddPage();

        $proxy->Write(6, '');
    }
}