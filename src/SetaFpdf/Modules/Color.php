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

class Color
{
    /**
     * @var \setasign\SetaFpdf\StateBuffer\Color
     */
    private $color;

    /**
     * Color constructor.
     *
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        $this->color = $manager->getColorState();
    }

    /**
     * Implementation of the FPDF::SetDrawColor() method.
     *
     * @param int|float $red
     * @param int|float $green
     * @param int|float $blue
     * @param int|float $cyan
     */
    public function setDraw($red, $green, $blue, $cyan)
    {
        $this->color->drawColor = $this->ensureColor($red, $green, $blue, $cyan);
    }

    /**
     * Implementation of the FPDF::SetFillColor method.
     *
     * @param int|float $red
     * @param int|float $green
     * @param int|float $blue
     * @param int|float $cyan
     */
    public function setFill($red, $green, $blue, $cyan)
    {
        $this->color->fillColor = $this->ensureColor($red, $green, $blue, $cyan);
    }

    /**
     * Implementation of the FPDF::SetTextColor method.
     *
     * @param int|float $red
     * @param int|float $green
     * @param int|float $blue
     * @param int|float $cyan
     */
    public function setText($red, $green, $blue, $cyan)
    {
        $this->color->textColor = $this->ensureColor($red, $green, $blue, $cyan);
    }

    /**
     * Converts the given values into a proper color array.
     *
     * @param int|float $red
     * @param int|float $green
     * @param int|float $blue
     * @param int|float $cyan
     * @return array
     */
    protected function ensureColor($red, $green, $blue, $cyan)
    {
        if ($cyan !== null) {
            $colorArray = [
                round($red / 100, 3),
                round($green / 100, 3),
                round($blue / 100, 3),
                round($cyan / 100, 3)
            ];
        } else {
            $colorArray = [round($red / 255, 3)];

            if ($green !== null) {
                $colorArray[] = round($green / 255, 3);
                $colorArray[] = round($blue / 255, 3);
            }
        }

        return $colorArray;
    }
}