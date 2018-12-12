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

namespace setasign\SetaFpdf\Modules;

use setasign\SetaFpdf\Manager;

class Text
{
    /**
     * @var Manager
     */
    private $manager;

    /**
     * Text constructor.
     *
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param int|float $x
     * @param int|float $y
     * @param string $text
     * @throws \BadMethodCallException
     * @throws \SetaPDF_Core_Font_Exception
     * @throws \SetaPDF_Core_Type_IndirectReference_Exception
     * @throws \SetaPDF_Exception_NotImplemented
     */
    public function text($x, $y, $text)
    {
        $font = $this->manager->getFontState();

        if ($font->getNewFont() === null) {
            throw new \BadMethodCallException('No font has been set yet.');
        }

        $converter = $this->manager->getConverter();

        $x = $converter->convertX($x);
        $y = $converter->convertY($y);

        $font->ensureFont();
        $this->manager->getColorState()->ensureTextColor();

        $this->manager->getModule(Font::class)->doUnderline(
            $x,
            $y,
            $converter->convert($this->manager->getModule(Cell::class)->getStringWidth($text, 'UTF-8'))
        );

        $this->manager->getCanvas()->text()
            ->begin()
                ->moveToNextLine($x, $y)
                ->showText($font->font->getCharCodes($text, 'UTF-8'))
            ->end();
    }
}