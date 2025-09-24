<?php

namespace setasign\tests\functional\SetaFpdf\Exception;

use setasign\SetaFpdf\SetaFpdf;
use setasign\tests\TestCase;

class ConstructTest extends TestCase
{
    private function construct($orientation, $unit, $size)
    {
        try {
            new \FPDF($orientation, $unit, $size);
            $this->fail();
        // required for php5.6
        } catch (\Exception $e) {
            $error = $e;
        } catch (\Throwable $e) {
            $error = $e;
        }

        try {
            new SetaFpdf($orientation, $unit, $size);
            $this->fail();
        // required for php5.6
        } catch (\Exception $e) {
            $error2 = $e;
        } catch (\Throwable $e) {
            $error2 = $e;
        }

        return [
            'error' => $error,
            'error2' => $error2
        ];
    }

    private function validate($exceptions)
    {
        $this->assertInstanceOf(\Exception::class, $exceptions['error']);
        $this->assertInstanceOf(\Exception::class, $exceptions['error2']);
        throw $exceptions['error2'];
    }

    public function testWithInvalidOrientation()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown orientation: hallo');
        $this->validate($this->construct('hallo', 'mm', 'A4'));
    }

    public function testWithInvalidUnit()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Incorrect unit: hallo');
        $this->validate($this->construct('P', 'hallo', 'A4'));
    }

    public function testWidthInvalidSize()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid size array.');
        $this->validate($this->construct('P', 'mm', [2]));
    }
}