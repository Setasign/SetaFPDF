<?php
/**
 * This file is part of SetaFPDF
 *
 * @package   setasign\SetaFpdf
 * @copyright Copyright (c) 2018 Setasign - Jan Slabon (https://www.setasign.com)
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
     * @var mixed[]
     */
    private $modules = [];

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
     * @return float|int
     */
    public function getWidth()
    {
        if ($this->canvas === null) {
            // fallback if no page was added before
            return $this->getModule(Document::class)->getDefaultWidth();
        }

        return $this->canvas->getWidth();
    }

    /**
     * Returns the height of the canvas.
     *
     * If there isn't a canvas (because no page was added yet) the default page height of the document will be returned.
     *
     * @return float|int
     */
    public function getHeight()
    {
        if ($this->canvas === null) {
            // fallback if no page was added before
            return $this->getModule(Document::class)->getDefaultHeight();
        }

        return $this->canvas->getHeight();
    }

    /**
     * Check whether space of a specific height is available on the current page.
     *
     * @param $height
     * @return bool
     */
    public function hasSpaceOnPage($height)
    {
        $margin = $this->getModule(Margin::class);

        $canvasHeight = $this->getConverter()->fromPt($this->getHeight());

        $leftSpace = $canvasHeight - $this->getCursor()->getY() - $margin->getBottom();

        return $leftSpace - $height >= 0;
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
     * Get a module.
     *
     * @param string $class
     * @param mixed[] $args
     * @return mixed|Cell|Color|Document|Draw|Font|Margin|Text|Link
     */
    public function getModule($class, $args = [])
    {
        if (!isset($this->modules[$class])) {
            $newModule = new $class($this, ...$args);

            if ($newModule instanceof StateBufferInterface) {
                $this->stateBufferInterfaces[] = $newModule;
            }

            $this->modules[$class] = $newModule;
        }

        return $this->modules[$class];
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

        // maintain the document module, to enable saving after the cleanup.
        $documentModule = $this->getModule(Document::class);
        foreach ($this->modules as $module) {
            if (!($module instanceof CleanupInterface)) {
                continue;
            }
            $module->cleanUp();
        }
        // append only the document module, which might got cleaned.
        $this->modules = [$documentModule];

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
