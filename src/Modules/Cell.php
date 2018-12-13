<?php
/**
 * This file is part of SetaFPDF
 *
 * @package   setasign\SetaFpdf
 * @copyright Copyright (c) 2018 Setasign - Jan Slabon (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 */

namespace setasign\SetaFpdf\Modules;

use setasign\SetaFpdf\Modules\Cell\StaticHelper;
use setasign\SetaFpdf\Manager;

class Cell
{
    /**
     * @var Manager
     */
    private $manager;

    /**
     * Cell constructor.
     *
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Implementation of the FPDF::Cell() method.
     *
     * @param int|float $width
     * @param int|float $height
     * @param string $text
     * @param int|string $border
     * @param int $lineBreak
     * @param string $align
     * @param bool $fill
     * @param string $link
     * @param string $encoding
     * @param int $wordSpacing
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws \SetaPDF_Core_Font_Exception
     * @throws \SetaPDF_Core_Type_IndirectReference_Exception
     * @throws \SetaPDF_Exception_NotImplemented
     */
    public function cell(
        $width,
        $height,
        $text,
        $border,
        $lineBreak,
        $align,
        $fill,
        $link,
        $encoding = 'UTF-8',
        $wordSpacing = 0
    ) {
        if ($encoding !== 'UTF-16BE') {
            $text = \SetaPDF_Core_Encoding::convert($text, $encoding, 'UTF-16BE');
        }

        $cursor = $this->manager->getCursor();
        $converter = $this->manager->getConverter();
        $colorState = $this->manager->getColorState();
        $canvasState = $this->manager->getCanvasState();
        $fontState = $this->manager->getFontState();
        $margin = $this->manager->getModule(Margin::class);
        $document = $this->manager->getModule(Document::class);

        if ($document->pageBreakAllowed() && !$this->manager->hasSpaceOnPage($height)) {
            $this->manager->getModule(Document::class)->handleAutoPageBreak();
        }

        /** @noinspection TypeUnsafeComparisonInspection */
        if ($width == 0) {
            $canvasWidth = $converter->fromPt($this->manager->getWidth());

            $width = $canvasWidth - $cursor->getX() - $margin->getRight();
        }

        $x = $converter->toPt($cursor->getX());
        $y = $this->manager->getHeight() - $converter->toPt($cursor->getY());

        $_h = $converter->toPt($height);
        $_w = $converter->toPt($width);

        /** @noinspection TypeUnsafeComparisonInspection also allow true */
        if ($fill || $border == 1) {
            $canvasState->ensureLineWidth();
            $canvasState->ensureLineCap();

            $colorState->ensureFillColor();
            $colorState->ensureDrawColor();

            $path = $this->manager->getCanvas()->path();

            $path->rect($x, $y, $_w, -$_h);

            if ($fill) {
                /** @noinspection TypeUnsafeComparisonInspection also allow true */
                /** @noinspection NotOptimalIfConditionsInspection */
                if ($border == 1) {
                    $path->fillAndStroke();
                } else {
                    $path->fill();
                }
            } else {
                $path->stroke();
            }
        }

        if (\is_string($border)) {
            $path = $this->manager->getCanvas()->path();

            $canvasState->ensureLineWidth();
            $canvasState->ensureLineCap();

            $colorState->ensureFillColor();
            $colorState->ensureDrawColor();

            if (strpos($border, 'L') !== false) {
                $path->moveTo($x, $y)->lineTo($x, $y - $_h)->stroke();
            }
            if (strpos($border, 'T') !== false) {
                $path->moveTo($x, $y)->lineTo($x + $_w, $y)->stroke();
            }
            if (strpos($border, 'R') !== false) {
                $path->moveTo($x + $_w, $y)->lineTo($x + $_w, $y - $_h)->stroke();
            }
            if (strpos($border, 'B') !== false) {
                $path->moveTo($x, $y - $_h)->lineTo($x + $_w, $y - $_h)->stroke();
            }
        }

        $text = str_replace("\x00\x0A", "\x00\x20", $text);


        if ($text !== '') {
            if ($fontState->getNewFont() === null) {
                throw new \BadMethodCallException('No font has been set yet.');
            }

            $colorState->ensureTextColor();
            $canvas = $this->manager->getCanvas();
            $textCanvas = $canvas->text();

            if ($align === 'R') {
                $dx = $width - $margin->getCell() - $this->getStringWidth($text, 'UTF-16BE');
            } elseif ($align === 'C') {
                $dx = ($width - $this->getStringWidth($text, 'UTF-16BE')) / 2;
            } else {
                $dx = $margin->getCell();
            }

            $fontState->ensureFont();

            $fontSize = $converter->fromPt($fontState->fontSize);

            $x += $converter->toPt($dx);
            $y = $canvas->getHeight() - $converter->toPt($cursor->getY() + .5 * $height + .3 * $fontSize);

            $stringWidth = $converter->toPt($this->getStringWidth($text, 'UTF-16BE'));

            $textCanvas->begin()->moveToNextLine($x, $y);
            $charCodes = $fontState->font->getCharCodes($text);
            if ($wordSpacing !== 0) {
                $spaceCharCode = $fontState->font->getCharCodes("\x00\x20")[0];
                $buffer = [];
                $stringBuffer = '';

                foreach ($charCodes as $charCode) {
                    if ($charCode === $spaceCharCode) {
                        if ($stringBuffer !== '') {
                            $buffer[] = $stringBuffer;
                            $stringBuffer = '';
                        }

                        $stringWidth += $wordSpacing;
                        $buffer[] = round(-$wordSpacing * 1000 / $fontState->fontSize, 3);
                    }
                    $stringBuffer .= $charCode;
                }

                if ($stringBuffer !== '') {
                    $buffer[] = $stringBuffer;
                }

                $textCanvas->showTextStrings($buffer);
            } else {
                $textCanvas->showText($charCodes);
            }
            $textCanvas->end();

            if ($link !== '') {
                $linkHeight = $converter->toPt($fontSize);
                $this->manager->getModule(Link::class)->link(
                    $x,
                    $y + .5 * $linkHeight + .3 * $linkHeight - .5 * $height,
                    $stringWidth,
                    $linkHeight,
                    $link,
                    true
                );
            }

            $this->manager->getModule(Font::class)->doUnderline($x, $y, $stringWidth);
        }

        $this->manager->setLastHeight($height);

        if ($lineBreak > 0) {
            $cursor->addY($height);
            /** @noinspection TypeUnsafeComparisonInspection This is unsafe to allow true and 1 */
            if ($lineBreak == 1) {
                $cursor->setX($margin->getLeft());
            }
        } else {
            $cursor->addX($width);
        }
    }

    /**
     * Implementation of the FPDF::MultiCell() method.
     *
     * @param int|float $width
     * @param int|float $height
     * @param string $text
     * @param string|int $border
     * @param string $align
     * @param bool $fill
     * @param string $encoding
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws \SetaPDF_Core_Font_Exception
     * @throws \SetaPDF_Core_Type_IndirectReference_Exception
     * @throws \SetaPDF_Exception_NotImplemented
     */
    public function multiCell($width, $height, $text, $border, $align, $fill, $encoding = 'UTF-8')
    {
        if ($encoding !== 'UTF-16BE') {
            $text = \SetaPDF_Core_Encoding::convert($text, $encoding, 'UTF-16BE');
        }

        $font = $this->manager->getFontState();

        if ($font->getNewFont() === null) {
            throw new \BadMethodCallException('No font has been set yet.');
        }

        $cursor = $this->manager->getCursor();
        $converter = $this->manager->getConverter();
        $margin = $this->manager->getModule(Margin::class);

        /** @noinspection TypeUnsafeComparisonInspection */
        if ($width == 0) {
            $width = (
                $converter->fromPt($this->manager->getCanvas()->getWidth())
                - $margin->getRight()
                - $cursor->getX()
            );
        }
        $wMax = ($width - 2 * $margin->getCell());

        $b = 0;
        $b2 = '';
        /** @noinspection TypeUnsafeComparisonInspection */
        if ($border == 1) {
            $border = 'LTRB';
            $b = 'LRT';
            $b2 = 'LR';
        } elseif ($border) {
            if (\strpos($border, 'L') !== false) {
                $b2 .= 'L';
            }
            if (\strpos($border, 'R') !== false) {
                $b2 .= 'R';
            }
            $b = (\strpos($border, 'T') !== false) ? $b2 . 'T' : $b2;
        }

        $lines = StaticHelper::getLines(
            \SetaPDF_Core_Text::normalizeLineBreaks($text),
            $converter->toPt($wMax),
            $font->getNewFont(),
            $font->getNewFontSize()
        );

        $lastLine = \array_pop($lines)[1];
        foreach ($lines as list($forcedLineBreak, $line)) {
            $spaces = \substr_count($line, "\x00\x20");

            $lineLen = \SetaPDF_Core_Encoding::strlen($line, 'UTF-16BE');
            while (\SetaPDF_Core_Encoding::substr($line, $lineLen - 1, 1, 'UTF-16BE') === "\x00\x20" && $spaces > 0) {
                $lineLen--;
                $spaces--;
                $line = \SetaPDF_Core_Encoding::substr($line, 0, $lineLen, 'UTF-16BE');
            }

            $stringWidth = $this->getStringWidth($line, 'UTF-16BE');

            if ($spaces > 0) {
                $fontSize = $converter->fromPt($font->getNewFontSize());

                $_wMax = $wMax * 1000 / $fontSize;
                $_stringWidth = $stringWidth * 1000 / $fontSize;

                $wordSpacing = $converter->toPt(($_wMax - $_stringWidth) / 1000 * $fontSize / $spaces);
            } else {
                $wordSpacing = 0;
            }

            if ($line === "\x00\x0A") {
                $line = '';
            }

            if ($align !== 'J' || $forcedLineBreak === true) {
                $wordSpacing = 0;
            }

            $this->cell($width, $height, $line, $b, 2, $align, $fill, '', 'UTF-16BE', $wordSpacing);
            $b = $b2;
        }

        if ($border && \strpos($border, 'B') !== false) {
            $b .= 'B';
        }

        $this->cell($width, $height, $lastLine, $b, 2, $align, $fill, '', 'UTF-16BE');
        $cursor->setX($margin->getLeft());
    }

    /**
     * Implementation of the FPDF::Write() method.
     *
     * @param int|float $height
     * @param string $text
     * @param string $link
     * @param string $encoding
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws \SetaPDF_Core_Font_Exception
     * @throws \SetaPDF_Core_Type_IndirectReference_Exception
     * @throws \SetaPDF_Exception_NotImplemented
     */
    public function write($height, $text, $link, $encoding = 'UTF-16BE')
    {
        if ($encoding !== 'UTF-16BE') {
            $text = \SetaPDF_Core_Encoding::convert($text, $encoding, 'UTF-16BE');
        }

        $font = $this->manager->getFontState();

        if ($font->getNewFont() === null) {
            throw new \BadMethodCallException('No font has been set yet.');
        }

        if ($text === '') {
            return;
        }

        $cursor = $this->manager->getCursor();
        $margin = $this->manager->getModule(Margin::class);
        $converter = $this->manager->getConverter();

        $width = (
            $this->manager->getCanvas()->getWidth()
            - $converter->toPt($margin->getRight() + $cursor->getX())
        );
        $maxWidth = ($width - 2 * $converter->toPt($margin->getCell()));

        try {
            $firstLines = StaticHelper::getLines(
                $text,
                $maxWidth,
                $font->getNewFont(),
                $font->getNewFontSize(),
                0,
                0,
                1,
                false
            );

            if (isset($firstLines[1])) {
                $text = $firstLines[1][1];
            } else {
                $text = '';
            }

            $lines = [[$firstLines[0][0], $firstLines[0][1], $width]];
        } catch (\InvalidArgumentException $e) {
            $lines = [];
            if ($cursor->getX() - $margin->getLeft() >= \SetaPDF_Core::FLOAT_COMPARISON_PRECISION) {
                $cursor->addY($height);
                $cursor->setX($margin->getLeft());
            }
        }

        $width = (
            $this->manager->getCanvas()->getWidth()
            - $converter->toPt($margin->getRight() + $margin->getLeft())
        );
        $maxWidth = ($width - 2 * $converter->toPt($margin->getCell()));
        if ($text !== '') {
            foreach (StaticHelper::getLines($text, $maxWidth, $font->getNewFont(), $font->getNewFontSize()) as $line) {
                $lines[] = [true, $line[1], $width];
            }
        }

        $lastLine = array_pop($lines);
        foreach ($lines as $line) {
            $this->cell($line[2], $height, $line[1], 0, $line[0] ? 2 : 1, '', false, $link, 'UTF-16BE');
        }

        $this->cell(
            $this->getStringWidth($lastLine[1], 'UTF-16BE'),
            $height,
            $lastLine[1],
            0,
            0,
            '',
            false,
            $link,
            'UTF-16BE'
        );
    }

    /**
     * Implementation of the FPDF::GetStringWidth() method.
     *
     * @param string $text
     * @param string $encoding
     * @return float|int
     */
    public function getStringWidth($text, $encoding)
    {
        $fontState = $this->manager->getFontState();
        $glyphWidth = $fontState->getNewFont()->getGlyphsWidth($text, $encoding) / 1000;

        return $glyphWidth * $this->manager->getConverter()->fromPt($fontState->getNewFontSize());
    }
}
