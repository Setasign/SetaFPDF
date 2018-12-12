<?php
/**
 * This file is part of SetaFPDF
 *
 * @package   setasign\SetaFpdf
 * @copyright Copyright (c) 2018 Setasign - Jan Slabon (https://www.setasign.com)
 * @author    Timo Scholz <timo.scholz@setasign.com>
 * @author    Jan Slabon <jan.slabon@setasign.com>
 * @license   http://opensource.org/licenses/mit-license The MIT License
 */

namespace setasign\SetaFpdf\StateBuffer;

use setasign\SetaFpdf\Manager;

/**
 * Class Color
 * @package setasign\SetaFpdf\StateBuffer
 *
 * @property array|null $strokingColor
 * @method void ensureStrokingColor()
 *
 * @property array|null $nonStrokingColor
 * @method void ensureNonStrokingColor()
 */
class Color extends StateBuffer
{
    /**
     * @var Manager
     */
    private $manager;

    /**
     * The current text color.
     *
     * @var array
     */
    public $textColor;

    /**
     * The stored text color.
     *
     * @var array
     */
    private $storedTextColor;

    /**
     * The current draw color.
     *
     * @var array
     */
    public $drawColor;

    /**
     * The stored draw color.
     *
     * @var array
     */
    private $storedDrawColor;
    /**
     * The current fill color.
     *
     * @var array
     */
    public $fillColor;

    /**
     * The stored fill color.
     *
     * @var array
     */
    private$storedFillColor;

    /**
     * Color constructor.
     *
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;

        parent::__construct([
            'strokingColor' => [$this, 'passStrokingColor'],
            'nonStrokingColor' => [$this, 'passNonStrokingColor']
        ]);
    }

    /**
     * Ensures that the draw color is the active stroking color.
     */
    public function ensureDrawColor()
    {
        $this->strokingColor = $this->drawColor;
        $this->ensureStrokingColor();
    }

    /**
     * Ensures that the fill color is the active non stroking color.
     */
    public function ensureFillColor()
    {
        $this->nonStrokingColor = $this->fillColor;
        $this->ensureNonStrokingColor();
    }

    /**
     * Ensures that the text color is the active non stroking color.
     */
    public function ensureTextColor()
    {
        $this->nonStrokingColor = $this->textColor;
        $this->ensureNonStrokingColor();
    }

    /**
     * Callback method for changes in $strokingColor between ensureStrokingColor() calls.
     *
     * @param $value
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     */
    protected function passStrokingColor($value)
    {
        $this->manager->getCanvas()->draw()->setStrokingColor(
            \SetaPDF_Core_DataStructure_Color::createByComponents($value)
        );
    }

    /**
     * Callback method for changes in $nonStrokingColor between ensureNonStrokingColor() calls.
     *
     * @param $value
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     */
    protected function passNonStrokingColor($value)
    {
        $this->manager->getCanvas()->draw()->setNonStrokingColor(
            \SetaPDF_Core_DataStructure_Color::createByComponents($value)
        );
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
        $this->storedDrawColor = $this->drawColor;
        $this->storedFillColor = $this->fillColor;
        $this->storedTextColor = $this->textColor;
    }

    /**
     * @inheritdoc
     */
    public function restore()
    {
        $this->drawColor = $this->storedDrawColor;
        $this->fillColor = $this->storedFillColor;
        $this->textColor = $this->storedTextColor;
    }
}