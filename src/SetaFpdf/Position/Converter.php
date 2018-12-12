<?php
/**
 * This file is part of SetaFPDF
 *
 * @package   setasign\SetaFpdf
 * @copyright Copyright (c) 2018 Setasign - Jan Slabon (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 */

namespace setasign\SetaFpdf\Position;

/**
 * Class Converter
 *
 * This class converts the configured unit to pt.
 *
 * @see \setasign\SetaFpdf\SetaFpdf::__construct()
 */
class Converter
{
    const UNIT_PT = 1;
    const UNIT_MM = 72 / 25.4;
    const UNIT_CM = 72 / 2.54;
    const UNIT_IN = 72;

    /**
     * @var int|float
     */
    protected $scaleFactor;

    /**
     * Converter constructor.
     *
     * @param int|float $scaleFactor
     */
    public function __construct($scaleFactor)
    {
        $this->scaleFactor = $scaleFactor;
    }

    /**
     * Converts the given value in the configured unit to pt.
     *
     * @param float|int $value Value in the configured unit
     * @return float|int Value in pt
     */
    public function toPt($value)
    {
        return ($value * $this->scaleFactor);
    }

    /**
     * Converts the given value in pt to the configured unit.
     *
     * @param int|float $value Value in pt
     * @return int|float Value in the configured unit
     */
    public function fromPt($value)
    {
        return ($value / $this->scaleFactor);
    }
}
