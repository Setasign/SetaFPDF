<?php
/**
 * This file is part of SetaFPDF
 *
 * @package   setasign\SetaFpdf
 * @copyright Copyright (c) 2018 Setasign - Jan Slabon (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 */

namespace setasign\SetaFpdf\Modules;

use setasign\SetaFpdf\Manager;

class Draw
{
    /**
     * Tries to create a reader instance.
     *
     * @param string|resource|\SetaPDF_Core_Reader_ReaderInterface $file
     * @return \SetaPDF_Core_Reader_ReaderInterface
     * @throws \InvalidArgumentException
     */
    protected static function createReader($file)
    {
        if (\is_object($file) && $file instanceof \SetaPDF_Core_Reader_ReaderInterface) {
            return $file;
        }

        if (\is_resource($file)) {
            return new \SetaPDF_Core_Reader_Stream($file);
        }

        if (\is_string($file)) {
            return new \SetaPDF_Core_Reader_File($file);
        }

        throw new \InvalidArgumentException('Could not create reader.');
    }

    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var \SetaPDF_Core_XObject_Image[]
     */
    private $images = [];

    /**
     * Draw constructor.
     *
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Implementation of the FPDF::Rect() method.
     *
     * @param int|float $x
     * @param int|float $y
     * @param int|float $width
     * @param int|float $height
     * @param string|int $style
     * @throws \BadMethodCallException
     */
    public function rect($x, $y, $width, $height, $style)
    {
        $converter = $this->manager->getConverter();

        $x = $converter->toPt($x);
        $y = $this->manager->getHeight() - $converter->toPt($y);
        $width = $converter->toPt($width);
        $height = $converter->toPt($height);

        switch (\strtolower($style)) {
            case 'f':
                $style = \SetaPDF_Core_Canvas_Draw::STYLE_FILL;
                break;
            case 'df':
            case 'fd':
                $style = \SetaPDF_Core_Canvas_Draw::STYLE_DRAW_AND_FILL;
                break;
            default:
                $style = \SetaPDF_Core_Canvas_Draw::STYLE_DRAW;
        }

        $this->ensureDraw();
        $this->manager->getCanvas()->draw()->rect(
            round($x, 2),
            round($y - $height, 2),
            round($width, 2),
            round($height, 2),
            $style
        );
    }

    /**
     * Implementation of the FPDF::Line() method.
     *
     * @param int|float $x1
     * @param int|float $y1
     * @param int|float $x2
     * @param int|float $y2
     * @throws \BadMethodCallException
     */
    public function line($x1, $y1, $x2, $y2)
    {
        $converter = $this->manager->getConverter();

        $x1 = $converter->toPt($x1);
        $y1 = $this->manager->getHeight() - $converter->toPt($y1);
        $x2 = $converter->toPt($x2);
        $y2 = $this->manager->getHeight() - $converter->toPt($y2);

        $this->ensureDraw();

        $canvas = $this->manager->getCanvas();
        $canvas->draw()->line($x1, $y1, $x2, $y2);
    }

    /**
     * Implementation of the FPDF::Image() method.
     *
     * @param string|resource|\SetaPDF_Core_Reader_ReaderInterface $file
     * @param int|float|null $x
     * @param int|float|null $y
     * @param int|float $width
     * @param int|float $height
     * @param string $link
     * @throws \InvalidArgumentException
     */
    public function image($file, $x, $y, $width, $height, $link)
    {
        if ($file == '') {
            throw new \BadMethodCallException('Image file name is empty.');
        }

        $uuid = null;
        if (\is_string($file)) {
            $uuid = \realpath($file);
            if ($uuid === false) {
                $uuid = $file;
            }
            /**
             * @var string $uuid
             */
        } elseif (\is_object($file)) {
            $uuid = spl_object_hash($file);
        } elseif (\is_resource($file)) {
            $uuid = (string) $file;
        }

        if ($uuid === null || !isset($this->images[$uuid])) {
            $this->images[$uuid] = \SetaPDF_Core_XObject_Image::create(
                $this->manager->getModule(Document::class)->get(),
                self::createReader($file)
            );
        }

        $xObject = $this->images[$uuid];
        $converter = $this->manager->getConverter();
        $document = $this->manager->getModule(Document::class);

        /** @noinspection TypeUnsafeComparisonInspection */
        if ($width == 0 && $height == 0) {
            $width = -96;
            $height = -96;
        }

        if ($width < 0) {
            $width = $converter->fromPt(-$xObject->getWidth() * 72 / $width);
        }
        if ($height < 0) {
            $height = $converter->fromPt(-$xObject->getHeight() * 72 / $height);
        }
        /** @noinspection TypeUnsafeComparisonInspection */
        if ($width == 0) {
            $width = $height * $xObject->getWidth() / $xObject->getHeight();
        }
        /** @noinspection TypeUnsafeComparisonInspection */
        if ($height == 0) {
            $height = $width * $xObject->getHeight() / $xObject->getWidth();
        }

        if ($y === null) {
            if ($document->pageBreakAllowed() && !$this->manager->hasSpaceOnPage($height)) {
                $x2 = $this->manager->getCursor()->getX();
                $this->manager->getModule(Document::class)->handleAutoPageBreak();
                $this->manager->getCursor()->setX($x2);
            }
            $y = $this->manager->getCursor()->getY();
            $this->manager->getCursor()->addY($height);
        }

        if ($x === null) {
            $x = $this->manager->getCursor()->getX();
        }

        $width = $converter->toPt($width);
        $height = $converter->toPt($height);
        $x = $converter->toPt($x);
        $canvas = $this->manager->getCanvas();
        $y = $canvas->getHeight() - ($converter->toPt($y) + $height);

        $xObject->draw(
            $canvas,
            $x,
            $y,
            $width,
            $height
        );

        if ($link != '') {
            $this->manager->getModule(Link::class)->link(
                $x,
                $y + $height,
                $width,
                $height,
                $link,
                true
            );
        }
    }

    /**
     *  Ensures that the draw dependent values are set in the canvas.
     */
    private function ensureDraw()
    {
        $colorState = $this->manager->getColorState();
        $canvasState = $this->manager->getCanvasState();

        $colorState->ensureFillColor();
        $colorState->ensureDrawColor();

        $canvasState->ensureLineCap();
        $canvasState->ensureLineWidth();
    }
}
