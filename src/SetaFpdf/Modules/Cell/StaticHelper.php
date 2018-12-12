<?php
/**
 * This file is part of SetaFPDF
 *
 * @package   setasign\SetaFpdf
 * @copyright Copyright (c) 2018 Setasign - Jan Slabon (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 */

namespace setasign\SetaFpdf\Modules\Cell;

/**
 * Class StaticHelper
 *
 * @TODO Maybe merge back into SetaPDF
 */
class StaticHelper
{
    /**
     * The constructor.
     *
     * Made private to make instancing harder, since there is no use for in instance of this class.
     */
    private function __construct()
    {
    }

    /**
     * Splits a UTF-16BE encoded string into lines based on a specific font and width.
     *
     * @see SetaPDF_Core_Text::getLines()
     * @param $text
     * @param null $width
     * @param \SetaPDF_Core_Font_Glyph_Collection_CollectionInterface|null $font
     * @param null $fontSize
     * @param int $charSpacing
     * @param int $wordSpacing
     * @param int $lineCount
     * @param bool $breakWords
     * @return array
     * @throws \InvalidArgumentException
     */
    public static function getLines(
        $text,
        $width = null,
        \SetaPDF_Core_Font_Glyph_Collection_CollectionInterface $font = null,
        $fontSize = null,
        $charSpacing = 0,
        $wordSpacing = 0,
        $lineCount = 0,
        $breakWords = true
    ) {
        if ($width === null) {
            return explode("\x00\x0a", $text);
        }

        $breaks = [];

        $currentLine = 0;
        $lines = [0 => ''];
        $lineWidth = 0;
        $linePosition = 0;
        $lastDelimiterPos = null;
        $lastDelimiterDirection = null;
        $lastChar = null;

        $len = \SetaPDF_Core_Encoding::strlen($text, 'UTF-16BE');
        $nextChar = \SetaPDF_Core_Encoding::substr($text, 0, 1, 'UTF-16BE');
        for ($i = 0; $i < $len; $i++) {
            $char = $nextChar;
            $nextChar = \SetaPDF_Core_Encoding::substr($text, $i + 1, 1, 'UTF-16BE');

            if ($lineCount > 0 && count($lines) > $lineCount) {
                $lines[$currentLine] .= \SetaPDF_Core_Encoding::substr($text, $i, $len - $i, 'UTF-16BE');
                break;
            }

            if ($char === "\x00\x0a") {
                $breaks[] = $currentLine;
                $lines[++$currentLine] = '';
                $lineWidth = 0;
                $linePosition = 0;
                $lastDelimiterPos = null;
                continue;
            }

            if ((
                    "\x00\x20" === $char
                    || (isset(\SetaPDF_Core_Text::$possibleDelimiter[$char])
                        && 1 === \SetaPDF_Core_Text::$possibleDelimiter[$char]
                    )
                    || (isset(\SetaPDF_Core_Text::$possibleDelimiter[$char])
                        && (($lastDelimiterPos === null
                                && ($nextChar === false
                                    || !isset(\SetaPDF_Core_Text::$possibleGlueCharacters[$nextChar])
                                )
                            )
                            || $lastDelimiterPos === ($linePosition - 1)
                        )
                    )
                    || ("\x00\x25" === $char && (
                            $lastChar === null || $lastChar[0] !== "\x00" || !ctype_digit($lastChar[1])
                        )
                    )
                ) && ($lastChar === null || !isset(\SetaPDF_Core_Text::$possibleGlueCharacters[$lastChar]))
            ) {
                $lastDelimiterPos = $linePosition;
                $lastDelimiterDirection = \SetaPDF_Core_Text::$possibleDelimiter[$char];
            }

            $charWidth = $font->getGlyphWidth($char) / 1000 * $fontSize;

            if ($char !== "\x00\x20"
                && (abs($charWidth + $lineWidth) - $width > \SetaPDF_Core::FLOAT_COMPARISON_PRECISION)
            ) {
                if ($breakWords === true && $lines[$currentLine] === '') {
                    $lines[$currentLine] = $char;
                    $currentLine++;
                    $lines[$currentLine] = '';
                    $lineWidth = 0;
                    $linePosition = 0;
                    $lastDelimiterPos = null;
                    $lastChar = $char;
                    continue;
                }

                if (0 === $i) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'A single character (%s) does not fit into the given $width (%F).',
                            \SetaPDF_Core_Encoding::convert($char, 'UTF-16BE', 'UTF-8'),
                            $width
                        )
                    );
                }

                // If no delimiter exists in the current line, simply add a line break
                if ($lastDelimiterPos === null) {
                    if ($breakWords === false) {
                        throw new \InvalidArgumentException(sprintf(
                            'The long word (%s) does not fit into the given $width (%F).',
                            \SetaPDF_Core_Encoding::convert($lines[$currentLine], 'UTF-16BE', 'UTF-8'),
                            $width
                        ));
                    }

                    $currentLine++;
                    $lines[$currentLine] = '';

                    $lineWidth = 0;
                    $linePosition = 0;
                    // Else cut the last "word" and shift it to the next line
                } else {
                    // save last "word"
                    $tmpLine = \SetaPDF_Core_Encoding::substr(
                        $lines[$currentLine],
                        $lastDelimiterPos + ($lastDelimiterDirection == 1 ? 0 : 1),
                        \SetaPDF_Core_Encoding::strlen($lines[$currentLine], 'UTF-16BE'),
                        'UTF-16BE'
                    );

                    // Remove last "word"
                    $lines[$currentLine] = \SetaPDF_Core_Encoding::substr(
                        $lines[$currentLine],
                        0,
                        $lastDelimiterPos + ($lastDelimiterDirection == 1 ? 0 : 1),
                        'UTF-16BE'
                    );

                    // Init next line with the last "word" of the previous line
                    $lines[++$currentLine] = $tmpLine;
                    $lineWidth = $font->getGlyphsWidth($tmpLine) / 1000 * $fontSize;
                    $linePosition = \SetaPDF_Core_Encoding::strlen($tmpLine, 'UTF-16BE');
                    if ($charSpacing != 0) {
                        $lineWidth += $linePosition * $charSpacing;
                    }

                    if (isset(\SetaPDF_Core_Text::$possibleDelimiter[$char])
                        && 0 === \SetaPDF_Core_Text::$possibleDelimiter[$char]
                    ) {
                        $lastDelimiterPos = $linePosition;
                        $lastDelimiterDirection = \SetaPDF_Core_Text::$possibleDelimiter[$char];
                    } else {
                        $lastDelimiterPos = null;
                    }
                }
            }

            if ($linePosition > 0
                && ((isset(\SetaPDF_Core_Text::$possibleDelimiter[$char])
                        && 0 === \SetaPDF_Core_Text::$possibleDelimiter[$char]
                    )
                    || (
                        $char === "\x00\x25" && $lastChar !== null
                        && $lastChar[0] === "\x00" && ctype_digit($lastChar[1])
                    )
                )
                && ($nextChar === false || !isset(\SetaPDF_Core_Text::$possibleGlueCharacters[$nextChar]))
            ) {
                $lastDelimiterPos = $linePosition;
                $lastDelimiterDirection = 0;
            }

            if ($wordSpacing != 0 && $char === "\x00\x20") {
                $lineWidth += $wordSpacing;
            }

            if ($charSpacing != 0) {
                $lineWidth += $charSpacing;
            }

            $lineWidth += $charWidth;
            $lines[$currentLine] .= $char;

            $linePosition++;
            $lastChar = $char;
        }

        $result = [];
        foreach ($lines as $lineNo => $line) {
            $result[] = [\in_array($lineNo, $breaks), $line];
        }

        return $result;
    }
}
