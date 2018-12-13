<?php

namespace setasign\tests;

use FPDF;
use setasign\SetaFpdf\SetaFpdf;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    const tempDir = __DIR__ . DIRECTORY_SEPARATOR . 'tmp';

    protected function getTempDir()
    {
        $directory = self::tempDir . DIRECTORY_SEPARATOR
            . str_replace('\\', DIRECTORY_SEPARATOR, \get_class($this)) . DIRECTORY_SEPARATOR
            . $this->getName(false);


        if ($this->usesDataProvider()) {
            $directory .= DIRECTORY_SEPARATOR . $this->getDataSetAsString(false);
        }

        return $directory;
    }

    /**
     * @return string
     */
    protected function getAssetsDir()
    {
        return realpath(dirname(__DIR__) . '/assets/');
    }

    /**
     * @return TestProxy
     * @throws \InvalidArgumentException
     */
    public function getProxy($orientation = 'P', $unit = 'mm', $size = 'A4')
    {
        return new TestProxy([
            new SetaFpdf($orientation, $unit, $size),
            new FPDF($orientation, $unit, $size)
        ]);
    }
}
