<?php
/**
 * This file is part of SetaFPDF
 *
 * @package   setasign\SetaFpdf
 * @copyright Copyright (c) 2018 Setasign - Jan Slabon (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 */

namespace setasign\SetaFpdf\Position;

use setasign\SetaFpdf\Manager;
use setasign\SetaFpdf\Modules\Document;

class Converter
{
    /**
     * @var int|float
     */
    protected $scaleFactor;

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * Converter constructor.
     *
     * @param int|float $scaleFactor
     * @param Manager $manager
     */
    public function __construct($scaleFactor, Manager $manager)
    {
        $this->manager = $manager;
        $this->scaleFactor = $scaleFactor;
    }

    /**
     * Scales up a value.
     *
     * @param float|int $value
     * @return float|int
     */
    public function convert($value)
    {
        return ($value * $this->scaleFactor);
    }

    /**
     * Scales up and prepares a x value.
     *
     * @param float|int $value
     * @return float|int
     */
    public function convertX($value)
    {
        return $this->convert($value);
    }

    /**
     * Scales up and prepares a y value.
     *
     * @param int|float $value
     * @return int|float
     * @throws \BadMethodCallException
     */
    public function convertY($value)
    {
        try {
            $canvasHeight = $this->manager->getCanvas()->getHeight();
        } catch (\BadMethodCallException $e) {
            $canvasHeight = $this->manager->getModule(Document::class)->getDefaultHeight();
        }

        return ($canvasHeight - $this->convert($value));
    }

    /**
     * Scales down a value.
     *
     * @param int|float $value
     * @return int|float
     */
    public function revert($value)
    {
        return ($value / $this->scaleFactor);
    }
}
