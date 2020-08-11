<?php
/**
 * This file is part of SetaFPDF
 *
 * @package   setasign\SetaFpdf
 * @copyright Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 */

namespace setasign\SetaFpdf\Modules;

use setasign\SetaFpdf\StateBuffer\Color as ColorState;

class Color
{
    /**
     * @var ColorState
     */
    private $color;

    /**
     * Color constructor.
     *
     * @param ColorState $colorState
     */
    public function __construct(ColorState $colorState)
    {
        $this->color = $colorState;
    }

    /**
     * Implementation of the FPDF::SetDrawColor() method.
     *
     * The count of $components define the color space (1 - gray, 3 - RGB, 4 - CMYK).
     * If the colorspace is grayscale or RGB the color values must be between 0 and 255.
     * For cmyk the color values must be between 0 and 100.
     *
     * @param array $components
     */
    public function setDraw(...$components)
    {
        $this->color->drawColor = $this->ensureColor(...$components);
    }

    /**
     * Implementation of the FPDF::SetFillColor method.
     *
     * The count of $components define the color space (1 - gray, 3 - RGB, 4 - CMYK).
     * If the colorspace is grayscale or RGB the color values must be between 0 and 255.
     * For cmyk the color values must be between 0 and 100.
     *
     * @param array $components
     */
    public function setFill(...$components)
    {
        $this->color->fillColor = $this->ensureColor(...$components);
    }

    /**
     * Implementation of the FPDF::SetTextColor method.
     *
     * The count of $components define the color space (1 - gray, 3 - RGB, 4 - CMYK).
     * If the colorspace is grayscale or RGB the color values must be between 0 and 255.
     * For cmyk the color values must be between 0 and 100.
     *
     * @param array $components
     */
    public function setText(...$components)
    {
        $this->color->textColor = $this->ensureColor(...$components);
    }

    /**
     * Converts the given values into a proper color array.
     *
     * @param array $components
     * @return array
     */
    protected function ensureColor(...$components)
    {
        switch (count($components)) {
            case 0:
            case 1:
                $grey = isset($components[0]) ? $components[0] : 0;
                return [round($grey / 255, 3)];

            case 4:
                list($cyan, $magenta, $yellow, $key) = $components;
                return [round($cyan / 100, 3), round($magenta / 100, 3), round($yellow / 100, 3), round($key / 100, 3)];

            case 3:
            default:
                $red = isset($components[0]) ? $components[0] : 0;
                $green = isset($components[1]) ? $components[1] : 0;
                $blue = isset($components[2]) ? $components[2] : 0;
                return [round($red / 255, 3), round($green / 255, 3), round($blue / 255, 3)];
        }
    }
}
