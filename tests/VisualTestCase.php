<?php

namespace setasign\tests;

abstract class VisualTestCase extends TestCase
{
    const TOLERANCE = 0.001;
    const DPI = 150;

    /**
     * @param $tmpDir
     * @param $inputFile
     * @param $dpi
     * @throws \RuntimeException
     */
    private function createImages($tmpDir, $inputFile, $dpi)
    {
        if (!file_exists($tmpDir)) {
            $old = umask(0);
            if (!@mkdir($tmpDir, 0775, true) && !is_dir($tmpDir)) {
                throw new \RuntimeException(sprintf(
                    'Couldn\'t create tmpDir "%s"',
                    $tmpDir
                ));
            }
            umask($old);
        }

        exec(
            'mutool draw -o "' . $tmpDir . DIRECTORY_SEPARATOR . '%d.png" -r ' . $dpi . ' -A 8 "' . $inputFile . '" 2>&1',
            $output,
            $status
        );

        $old = umask(0);
        foreach (glob($tmpDir . '/*.png') as $filename) {
            chmod($filename, 0775);
        }
        umask($old);

        if ($status != 0) {
            $this->fail(implode("\n", $output));
        }
    }

    /**
     * @param string[] $pdfs
     * @param float $tolerance
     * @param bool $delete
     * @throws \RuntimeException
     */
    public function assertPdfsEqual($pdfs, $dpi, $tolerance, $delete)
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

        $comparePdf = array_shift($pdfs);

        /** @noinspection SuspiciousAssignmentsInspection */
        $old = umask(0);
        chmod($comparePdf, 0775);
        umask($old);

        $compareName = basename($comparePdf, '.pdf');
        $this->createImages(
            $tempDir . DIRECTORY_SEPARATOR . $compareName,
            $comparePdf,
            $dpi
        );

        $esc = function ($path) {
            return preg_replace('/(\*|\?|\[)/', '[$1]', $path);
        };

        $originalImages = array();
        foreach (glob($esc($tempDir) . '/' . $compareName . '/*.png') as $filename) {
            $originalImages[] = $filename;
        }

        foreach ($pdfs as $pdf) {
            /** @noinspection SuspiciousAssignmentsInspection */
            $old = umask(0);
            chmod($pdf, 0775);
            umask($old);

            $name = basename($pdf, '.pdf');
            $this->createImages($tempDir . DIRECTORY_SEPARATOR . $name, $pdf, $dpi);

            $testImages = array();
            foreach (glob($esc($tempDir) . '/' . $name . '/*.png') as $filename) {
                $testImages[] = $filename;
            }

            $diffName = $tempDir . '/' . $compareName . '_compare_to_' . $name . '.';
            if (file_exists($diffName)) {
                unlink($diffName);
            }

            $this->assertCount(\count($testImages), $originalImages, 'Count of pages for file ' . $tempDir);
            foreach ($testImages as $k => $filename) {
                $diffFullName = $diffName . ($k + 1) . '.png';

                $out = exec(sprintf(
                    'compare -alpha on -metric mae "%s" "%s" "%s" 2>&1',
                    $testImages[$k],
                    $originalImages[$k],
                    $diffFullName
                ));

                if (file_exists($diffFullName)) {
                    $old = umask(0);
                    chmod($diffFullName, 0775);
                    umask($old);
                }

                $this->assertNotEquals(
                    0,
                    preg_match('~^[0-9.]*(\s\([0-9e.\-]*\))?$~', $out),
                    $out . ' for file ' . $tempDir
                );

                $this->assertLessThanOrEqual($tolerance, $out, 'Page ' . $diffFullName . ' for file ' . $tempDir);

                if (!$delete) {
                    continue;
                }

                unlink($diffFullName);
            }


            if (!$delete) {
                continue;
            }

            // clean up
            foreach ($testImages as $filename) {
                unlink($filename);
            }
            rmdir($tempDir . DIRECTORY_SEPARATOR . $name);
        }

        if (!$delete) {
            return;
        }

        //clean up
        foreach ($originalImages as $filename) {
            unlink($filename);
        }
        rmdir($tempDir . DIRECTORY_SEPARATOR . $compareName);
    }

    /**
     * @param TestProxy $proxy
     * @param int|float $tolerance
     * @param int $dpi
     * @param boolean $delete
     * @throws \SetaPDF_Core_Exception
     * @throws \SetaPDF_Exception_NotImplemented
     */
    public function assertProxySame(TestProxy $proxy, $tolerance = self::TOLERANCE, $dpi = self::DPI, $delete = true)
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

        $pdfs = [];
        $instances = $proxy->getInstances();
        while (($instance = array_pop($instances)) !== null) {
            /** @var \FPDF $instance */
            $pdfs[] = $compareFileName =
                $tempDir
                . DIRECTORY_SEPARATOR
                . str_replace('\\', '_', \get_class($instance))
                . '.pdf';

            $instance->Output('F', $compareFileName);
            $instance->Close();
        }

        $this->assertPdfsEqual($pdfs, $dpi, $tolerance, $delete);

        if (!$delete) {
            return;
        }

        foreach ($pdfs as $pdf) {
            unlink($pdf);
        }

        try {

            $targetDir = \realpath(self::tempDir);
            $currentDir = \realpath($tempDir);

            do {
                /** @noinspection RealpathInSteamContextInspection */
                $newCurrentDir = \realpath($currentDir . DIRECTORY_SEPARATOR . '..');
                rmdir($currentDir);
            } while (($currentDir = $newCurrentDir) !== $targetDir);

            rmdir($targetDir);
        } catch (\Throwable $e) {}
    }
}