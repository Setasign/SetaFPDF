<?php
/**
 * This file is part of SetaFPDF
 *
 * @package   setasign\SetaFpdf
 * @copyright Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 */

namespace setasign\SetaFpdf;

use setasign\SetaFpdf\Modules\Cell;
use setasign\SetaFpdf\Modules\Color;
use setasign\SetaFpdf\Modules\Document;
use setasign\SetaFpdf\Modules\Draw;
use setasign\SetaFpdf\Modules\Font;
use setasign\SetaFpdf\Modules\Link;
use setasign\SetaFpdf\Modules\Margin;
use setasign\SetaFpdf\Modules\Text;
use setasign\SetaFpdf\Position\Converter;
use setasign\SetaFpdf\Position\Cursor;
use setasign\SetaFpdf\StateBuffer\Color as ColorState;
use setasign\SetaFpdf\StateBuffer\Canvas as CanvasState;
use setasign\SetaFpdf\StateBuffer\Font as FontState;
use setasign\SetaFpdf\StateBuffer\StateBufferInterface;

class Manager implements CleanupInterface
{
    /**
     * @var StateBufferInterface[]
     */
    private $stateBufferInterfaces = [];

    /**
     * @var null|Cell
     */
    private $cell;

    /**
     * @var null|Color
     */
    private $color;

    /**
     * @var null|Document
     */
    private $document;

    /**
     * @var null|Draw
     */
    private $draw;

    /**
     * @var null|Font
     */
    private $font;

    /**
     * @var null|Link
     */
    private $link;

    /**
     * @var null|Margin
     */
    private $margin;

    /**
     * @var null|Text
     */
    private $text;

    /**
     * @var null|\SetaPDF_Core_Canvas
     */
    private $canvas;

    /**
     * @var Cursor
     */
    private $cursor;

    /**
     * @var Converter
     */
    private $converter;

    /**
     * @var int
     */
    private $lastHeight;

    /**
     * Manager constructor.
     *
     * @param int|float $scaleFactor
     */
    public function __construct($scaleFactor)
    {
        $this->cursor = new Cursor($this);
        $this->stateBufferInterfaces[] = $this->cursor;
        $this->converter = new Converter($scaleFactor);

        $this->lastHeight = 0;
    }

    /**
     * Returns the width of the canvas.
     *
     * If there isn't a canvas (because no page was added yet) the default page width of the document will be returned.
     *
     * @return float|int Returns the width in pt.
     */
    public function getWidth()
    {
        if ($this->canvas === null) {
            // fallback if no page was added before
            return $this->converter->toPt($this->getDocument()->getDefaultWidth());
        }

        return $this->canvas->getWidth();
    }

    /**
     * Returns the height of the canvas.
     *
     * If there isn't a canvas (because no page was added yet) the default page height of the document will be returned.
     *
     * @return float|int Returns the height in pt.
     */
    public function getHeight()
    {
        if ($this->canvas === null) {
            // fallback if no page was added before
            return $this->converter->toPt($this->getDocument()->getDefaultHeight());
        }

        return $this->canvas->getHeight();
    }

    /**
     * Check whether space of a specific height is available on the current page.
     *
     * @param int|float $heightInUnit
     * @return bool
     */
    public function hasSpaceOnPage($heightInUnit)
    {
        $margin = $this->getMargin();

        $canvasHeight = $this->getConverter()->fromPt($this->getHeight());

        $leftSpace = $canvasHeight - $this->getCursor()->getY() - $margin->getBottom();

        return $leftSpace - $heightInUnit >= 0;
    }

    /**
     * Gets the current canvas.
     *
     * @return \SetaPDF_Core_Canvas
     * @throws \BadMethodCallException
     */
    public function getCanvas()
    {
        if ($this->canvas === null) {
            throw new \BadMethodCallException('No page has been added yet.');
        }

        return $this->canvas;
    }

    /**
     * Sets the current canvas and resets all the states.
     *
     * @param \SetaPDF_Core_Canvas $canvas
     */
    public function setCanvas(\SetaPDF_Core_Canvas $canvas)
    {
        $this->canvas = $canvas;
        foreach ($this->stateBufferInterfaces as $stateBuffer) {
            $stateBuffer->reset();
        }
    }

    /**
     * Gets the canvas state.
     *
     * @return CanvasState
     */
    public function getCanvasState()
    {
        return $this->getState(CanvasState::class);
    }

    /**
     * Gets the color state.
     *
     * @return ColorState
     */
    public function getColorState()
    {
        return $this->getState(ColorState::class);
    }

    /**
     * Gets the font state.
     *
     * @return FontState
     */
    public function getFontState()
    {
        return $this->getState(FontState::class);
    }

    /**
     * @return Cell
     */
    public function getCell()
    {
        if ($this->cell === null) {
            $this->cell = new Cell($this);
        }

        return $this->cell;
    }

    /**
     * @return Color
     */
    public function getColor()
    {
        if ($this->color === null) {
            $this->color = new Color($this->getColorState());
        }

        return $this->color;
    }

    public function setDocument(Document $document)
    {
        $this->stateBufferInterfaces[] = $document;
        $this->document = $document;
    }

    /**
     * @return Document
     */
    public function getDocument()
    {
        if ($this->document === null) {
            throw new \BadMethodCallException('No document is set!');
        }

        return $this->document;
    }

    /**
     * @return Draw
     */
    public function getDraw()
    {
        if ($this->draw === null) {
            $this->draw = new Draw($this);
        }

        return $this->draw;
    }

    /**
     * @return Font
     */
    public function getFont()
    {
        if ($this->font === null) {
            $this->font = new Font($this);
        }

        return $this->font;
    }

    /**
     * @return Link
     */
    public function getLink()
    {
        if ($this->link === null) {
            $this->link = new Link($this);
        }

        return $this->link;
    }

    /**
     * @return Margin
     */
    public function getMargin()
    {
        if ($this->margin === null) {
            $this->margin = new Margin($this->converter, $this->cursor);
            $this->stateBufferInterfaces[] = $this->margin;
        }

        return $this->margin;
    }

    /**
     * @return Text
     */
    public function getText()
    {
        if ($this->text === null) {
            $this->text = new Text($this);
        }

        return $this->text;
    }

    /**
     * @inheritdoc
     */
    public function cleanUp()
    {
        // clean up the buffers, which makes them unusable
        foreach ($this->stateBufferInterfaces as $stateBuffer) {
            $stateBuffer->cleanUp();
        }
        $this->stateBufferInterfaces = [];

        $this->cell = null;
        $this->color = null;
        // note: the document module is not cleaned, to enable saving after the cleanup.
        //$this->document = null;
        $this->draw = null;
        $this->font = null;
        $this->link = null;
        $this->margin = null;
        $this->text = null;

        $this->canvas = null;
        $this->converter = null;
        $this->cursor = null;
    }

    /**
     * Get the last height.
     *
     * @return int
     */
    public function getLastHeight()
    {
        return $this->lastHeight;
    }

    /**
     * Set the last height.
     *
     * @param $height
     */
    public function setLastHeight($height)
    {
        $this->lastHeight = $height;
    }

    /**
     * Get the cursor.
     *
     * @return Cursor
     */
    public function getCursor()
    {
        return $this->cursor;
    }

    /**
     * Get the converter.
     *
     * @return Converter
     */
    public function getConverter()
    {
        return $this->converter;
    }

    /**
     * Get a state instance.
     *
     * @param $class
     * @return mixed
     */
    private function getState($class)
    {
        if (!isset($this->stateBufferInterfaces[$class])) {
            $this->stateBufferInterfaces[$class] = new $class($this);
        }

        return $this->stateBufferInterfaces[$class];
    }

    /**
     * Save the states.
     */
    public function save()
    {
        foreach ($this->stateBufferInterfaces as $interface) {
            $interface->store();
        }
    }

    /**
     * Restore states.
     */
    public function restore()
    {
        foreach ($this->stateBufferInterfaces as $interface) {
            $interface->restore();
        }
    }
}
