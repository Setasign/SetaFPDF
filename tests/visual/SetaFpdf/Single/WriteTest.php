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

        $proxy->Write(8, 'Da kommt ein sehr langer Text und ich weiss nicht mehr wie sich dieses Ding verhaelt...');
        $proxy->Write(8, 'Da kommt ein sehr langer Text und ich weiss nicht mehr wie sich dieses Ding verhaelt...');
        $proxy->Write(8, 'Da kommt ein sehr langer Text und ich weiss nicht mehr wie sich dieses Ding verhaelt...');
        $proxy->Write(8, 'Da kommt ein sehr langer Text und ich weiss nicht mehr wie sich dieses Ding verhaelt...');
        $proxy->Write(8, 'Da kommt ein sehr langer Text und ich weiss nicht mehr wie sich dieses Ding verhaelt...');

        $this->assertProxySame($proxy, 6.1);
    }

    public function testSpecialWrite()
    {
        $proxy = $this->getProxy();

        $proxy->SetFont('arial');
        $proxy->AddPage();

        $proxy->Write(8, 'Da kommt ein sehr langer Text und ich weiss nicht mehr wie sich dieses Ding verhaelt...' . "\n");
        $proxy->Write(8, 'Da kommt ein sehr langer Text und ich weiss nicht mehr wie sich dieses Ding verhaelt...' . "\n");
        $proxy->Write(8, 'Da kommt ein sehr langer Text und ich weiss nicht mehr wie sich dieses Ding verhaelt...' . "\n");
        $proxy->Write(8, 'Da kommt ein sehr langer Text und ich weiss nicht mehr wie sich dieses Ding verhaelt...' . "\n");
        $proxy->Write(8, 'Da kommt ein sehr langer Text und ich weiss nicht mehr wie sich dieses Ding verhaelt...' . "\n");

        $this->assertProxySame($proxy, 16.38);
    }

    public function testWriteWithLineBreak()
    {
        $proxy = $this->getProxy();

        $proxy->SetFont('arial');
        $proxy->AddPage();

        $proxy->Write(4, 'Da kommt ein sehr langer Text und ich weiss nicht mehr wie sich dieses Ding verhaelt...');
        $proxy->Write(4, 'Da kommt ein sehr langer Text und ich weiss nicht' . "\n" . 'mehr wie sich dieses Ding verhaelt...');

        $this->assertProxySame($proxy, 4.7);
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

    public function testWriteWithNewLinesSimple()
    {
        $proxy = $this->getProxy('P', 'pt', 'A4');

        $proxy->SetFont('arial');

        $proxy->AddPage();
        $proxy->Write(8, "A\n");
        $proxy->Write(8, "B");


        $proxy->AddPage();

        $proxy->Write(8, 'A simple test');
        $proxy->Write(8, ".\n\nBest regards,\n\nTest Team");

        $proxy->AddPage();

        $proxy->Write(8, 'A test.');
        $proxy->Write(0, "\n");
        $proxy->Write(10, 'Another test.');

        $proxy->AddPage();
        $proxy->Write(10, "\nBreaking with the cooking grandma, they have very impressive quality. More cookies is not even \n considered as an option, because the cookies are great.");

        $proxy->Write(10, 'Testing' . "\n\n");
        $proxy->Write(10, "\n\n" . 'More tests' . "\n\n" . 'hallo' . "\n\n" . 'ouf');

        $this->assertProxySame($proxy, 1.7, 72);
    }
}