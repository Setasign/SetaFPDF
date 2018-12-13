<?php
/**
 * This file is part of SetaFPDF
 *
 * @package   setasign\SetaFpdf
 * @copyright Copyright (c) 2018 Setasign - Jan Slabon (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 */

namespace setasign\SetaFpdf\StateBuffer;

use setasign\SetaFpdf\Manager;

/**
 * Class Font
 *
 * @property \SetaPDF_Core_Font_FontInterface|null $font
 * @property int|float|null $fontSize
 */
class Font implements StateBufferInterface
{
    /**
     * The manager.
     *
     * @var Manager
     */
    private $manager;

    /**
     * The font that is set in the canvas.
     *
     * @var \SetaPDF_Core_Font_FontInterface|null
     */
    private $currentFont;

    /**
     * The stored font.
     *
     * @var \SetaPDF_Core_Font_FontInterface
     */
    private $storedFont;

    /**
     * The font that will be set in the canvas.
     *
     * @var \SetaPDF_Core_Font_FontInterface|null
     */
    private $newFont;

    /**
     * The font size that is set in the canvas.
     *
     * @var int|float|null
     */
    private $currentFontSize;

    /**
     * The stored font size.
     *
     * @var int|float|null
     */
    private $storedFontSize;

    /**
     * The font size that will be set in the canvas.
     *
     * @var int|float
     */
    private $newFontSize;

    /**
     * Font constructor.
     *
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
        $this->newFontSize = 12;
    }

    /**
     * Check if the this instance contains a specific state.
     *
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        switch ($name) {
            case 'font':
                return true;
            case 'fontSize':
                return true;
            default:
                return false;
        }
    }

    /**
     * Set the new value for the state.
     *
     * @param string $name
     * @param int|float|\SetaPDF_Core_Font $value
     * @throws \BadMethodCallException
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'font':
                $this->newFont = $value;
                break;
            case 'fontSize':
                $this->newFontSize = $value;
                break;
            default:
                throw new \BadMethodCallException(sprintf('Unknown property "%s".', $name));
        }
    }

    /**
     * Get the current value for the state.
     *
     * @param string $name
     * @return mixed
     * @throws \BadMethodCallException
     */
    public function __get($name)
    {
        switch ($name) {
            case 'font':
                return $this->currentFont;
            case 'fontSize':
                return $this->currentFontSize;
        }

        throw new \BadMethodCallException(sprintf('Unknown property "%s".', $name));
    }

    /**
     * Gets the newest font.
     *
     * @return \SetaPDF_Core_Font_FontInterface
     */
    public function getNewFont()
    {
        return $this->newFont;
    }

    /**
     * Gets the newest font size.
     *
     * @return int|float
     */
    public function getNewFontSize()
    {
        return $this->newFontSize;
    }

    /**
     * Ensures that the font and the font size are set correctly in the canvas.
     *
     * @throws \BadMethodCallException
     * @throws \SetaPDF_Core_Font_Exception
     * @throws \SetaPDF_Core_Type_IndirectReference_Exception
     * @throws \SetaPDF_Exception_NotImplemented
     */
    public function ensureFont()
    {
        if (($this->currentFont !== $this->newFont) || ($this->currentFontSize !== $this->newFontSize)) {
            $this->currentFont = $this->newFont;
            $this->currentFontSize = $this->newFontSize;

            $this->manager->getCanvas()->text()->setFont($this->currentFont, $this->currentFontSize);
        }
    }

    /**
     * @inheritdoc
     */
    public function reset()
    {
        $this->currentFontSize = $this->currentFont = null;
    }

    /**
     * @inheritdoc
     */
    public function cleanUp()
    {
        $this->manager = null;
    }

    /**
     * @inheritdoc
     */
    public function store()
    {
        $this->storedFont = $this->newFont;
        $this->storedFontSize = $this->newFontSize;
    }

    /**
     * @inheritdoc
     */
    public function restore()
    {
        $this->newFont = $this->storedFont;
        $this->newFontSize = $this->storedFontSize;
    }
}
