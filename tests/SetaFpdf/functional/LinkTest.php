<?php

namespace setasign\tests\SetaFpdf\functional;

use setasign\tests\TestCase;
use setasign\tests\TestProxy;

class LinkTest extends TestCase
{
    /**
     *
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function testCreateExternalLink()
    {
        $proxy = $this->getProxy();

        $proxy->AddPage();
        $proxy->Link(10, 10, 20, 20, 'https://example.de');

        $proxy->SetFont('arial');
        $proxy->Cell(200, 0, 'testen', 0, 0, '', false, 'https://example.com');


        $proxy->Image(__DIR__ . '/../../../assets/images/logo.png', 0, 0, 0, 0, '', 'https://setasign.com');

        $this->assertProxySame($proxy, 0.1);

    }

    /**
     *
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function testCreateInternalLink()
    {
        $proxy = $this->getProxy();

        $link = $proxy->AddLink();
        $proxy->AddPage();


        $proxy->SetLink($link, 40);

        $proxy->Link(10, 10, 20, 20, $link);

        $proxy->SetFont('arial');
        $proxy->Cell(200, 0, 'testen', 0, 0, '', false, $link);

        $proxy->Image(__DIR__ . '/../../../assets/images/logo.png', 0, 0, 0, 0, '', $link);

        $this->assertProxySame($proxy, .01);
    }

    /**
     *
     * @throws \BadMethodCallException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function testCreateMultipleInternal()
    {
        $proxy = $this->getProxy();

        $proxy->AddPage();

        $xCount = 10;
        $yCount = 10;
        for ($x = 0; $x < $xCount; $x++) {
            for ($y = 0; $y < $yCount; $y++) {

                $link = $proxy->AddLink();
                $proxy->Link(
                    ($proxy->GetPageWidth() / $xCount) * $x,
                    ($proxy->GetPageHeight() / $yCount) * $y,
                    10,
                    10,
                    $link
                );
                $proxy->SetLink($link, ($proxy->GetPageHeight() / $yCount) * $y);
            }
        }

        $this->assertProxySame($proxy, .01);
    }

    /**
     * @param TestProxy $proxy
     * @param float $tolerance
     * @param bool $delete
     */
    public function assertProxySame(TestProxy $proxy, $tolerance = 0.0001, $delete = true)
    {
        $tempDir = $this->getTempDir();

        if (!file_exists($tempDir)) {
            $old = umask(0);
            if (!@mkdir($tempDir, 0775, true) && !is_dir($tempDir)) {
                throw new \RuntimeException(sprintf(
                    'Couldn\'t create tmpDir "%s"',
                    $tempDir
                ));
            }
            umask($old);
        }

        $instances = $proxy->getInstances();

        /** @var \FPDF $originalPdf */
        $originalPdf = array_pop($instances);

        if ($originalPdf === null) {
            return;
        }

        $originalFileName =
            $tempDir
            . DIRECTORY_SEPARATOR
            . str_replace('\\', '_', \get_class($originalPdf))
            . '.pdf';

        $originalPdf->Output('f', $originalFileName);

        while (($instance = array_pop($instances)) !== null) {
            /** @var \FPDF $instance */
            $compareFileName =
                $tempDir
                . DIRECTORY_SEPARATOR
                .  str_replace('\\', '_', \get_class($instance))
                . '.pdf';
            $instance->Output('f', $compareFileName);

            $this->compare($originalFileName, $compareFileName, $tolerance);

            if (!$delete) {
                continue;
            }

            $instance->Close();
            unlink($compareFileName);
        }

        if (!$delete) {
            return;
        }

        $originalPdf->Close();
        unlink($originalFileName);

        try {

            $targetDir = realpath(self::tempDir);
            $currentDir = realpath($tempDir);

            do {
                /** @noinspection RealpathInSteamContextInspection */
                $newCurrentDir = realpath($currentDir . DIRECTORY_SEPARATOR . '..');
                rmdir($currentDir);
            } while (($currentDir = $newCurrentDir) !== $targetDir);

            rmdir($targetDir);
        } catch (\Throwable $e) {}
    }

    /**
     * @param $file0
     * @param $file1
     * @param $tolerance
     * @throws \BadMethodCallException
     */
    private function compare($file0, $file1, $tolerance)
    {
        $instance0 = \SetaPDF_Core_Document::loadByFilename($file0);
        $instance1 = \SetaPDF_Core_Document::loadByFilename($file1);

        $pages0 = $instance0->getCatalog()->getPages();
        $pages1 = $instance1->getCatalog()->getPages();

        $this->assertCount(\count($pages0), $pages1);

        for ($pageNo = 1; $pageNo <= $pages0->count(); $pageNo++) {
            $page0 = $pages0->getPage($pageNo);
            $page1 = $pages1->getPage($pageNo);

            $annotations0 = $page0->getAnnotations()->getAll(\SetaPDF_Core_Document_Page_Annotation::TYPE_LINK);
            $annotations1 = $page1->getAnnotations()->getAll(\SetaPDF_Core_Document_Page_Annotation::TYPE_LINK);

            $this->assertCount(\count($annotations0), $annotations1);

            foreach ($annotations0 as $key => $annotation0) {
                $annotation1 = $annotations1[$key];

                /** @var \SetaPDF_Core_Document_Page_Annotation_Link $annotation0 */
                /** @var \SetaPDF_Core_Document_Page_Annotation_Link $annotation1 */

                $destination0 = $annotation0->getDestination($instance0);
                $destination1 = $annotation1->getDestination($instance1);

                if ($destination0 === false && $destination1 === false) {
                    $action0 = $annotation0->getAction();
                    $action1 = $annotation1->getAction();

                    $this->assertNotFalse($action0);
                    $this->assertNotFalse($action1);

                    $this->assertEquals($action0->getPdfValue()->toPhp(), $action1->getPdfValue()->toPhp());
                } else {
                    $this->assertNotFalse($destination0);
                    $this->assertNotFalse($destination1);

                    $pdfValue0 = $destination0->getPdfValue()->toPhp();
                    $pdfValue1 = $destination1->getPdfValue()->toPhp();

                    unset($pdfValue0[0], $pdfValue1[0]);

                    // round to check if they are equal.
                    $pdfValue0[3] = round($pdfValue0[3], 1);
                    $pdfValue1[3] = round($pdfValue1[3], 1);

                    $this->assertEquals($pdfValue0, $pdfValue1);
                }

                $this->assertLessThanOrEqual(
                    $tolerance,
                    $annotation0->getRect()->getUrx() - $annotation1->getRect()->getUrx()
                );
                $this->assertLessThanOrEqual(
                    $tolerance,
                    $annotation0->getRect()->getUry() - $annotation1->getRect()->getUry()
                );
                $this->assertLessThanOrEqual(
                    $tolerance,
                    $annotation0->getRect()->getLlx() - $annotation1->getRect()->getLlx()
                );
                $this->assertLessThanOrEqual(
                    $tolerance,
                    $annotation0->getRect()->getLly() - $annotation1->getRect()->getLly()
                );
            }
        }

        $instance0->finish();
        $instance1->finish();
    }
}