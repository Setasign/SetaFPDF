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

class Font
{
    /**
     * A mapping used to map the default fonts to their given instances.
     *
     * @var array
     */
    protected static $fontMapping = [
        'courier' => 'SetaPDF_Core_Font_Standard_Courier',
        'courierb' => 'SetaPDF_Core_Font_Standard_CourierBold',
        'courieri' => 'SetaPDF_Core_Font_Standard_CourierOblique',
        'courierbi' => 'SetaPDF_Core_Font_Standard_CourierBoldOblique',

        'helvetica' => 'SetaPDF_Core_Font_Standard_Helvetica',
        'helveticab' => 'SetaPDF_Core_Font_Standard_HelveticaBold',
        'helveticai' => 'SetaPDF_Core_Font_Standard_HelveticaOblique',
        'helveticabi' => 'SetaPDF_Core_Font_Standard_HelveticaBoldOblique',

        'times' => 'SetaPDF_Core_Font_Standard_TimesRoman',
        'timesb' => 'SetaPDF_Core_Font_Standard_TimesBold',
        'timesi' => 'SetaPDF_Core_Font_Standard_TimesItalic',
        'timesbi' => 'SetaPDF_Core_Font_Standard_TimesBoldItalic',

        'symbol' => 'SetaPDF_Core_Font_Standard_Symbol',
        'zapfdingbats' => 'SetaPDF_Core_Font_Standard_ZapfDingbats',
    ];

    /**
     * A map with all font instances that belong to this document.
     *
     * @var \SetaPDF_Core_Font[]
     */
    protected $fonts;


    /**
     * The document module, to get the document instance.
     * We need the document instance to create new font instances.
     *
     * @var Document
     */
    protected $document;

    /**
     * The font state instance.
     *
     * @var \setasign\SetaFpdf\StateBuffer\Font
     */
    protected $fontState;

    /**
     * The current font family.
     *
     * @var string|null
     */
    protected $currentFamily;

    /**
     * The manager.
     *
     * @var Manager
     */
    protected $manager;

    /**
     * A flag defining whether to draw a underline.
     *
     * @var bool
     */
    protected $underline;

    /**
     * Font constructor.
     *
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        $this->underline = false;
        $this->manager = $manager;
        $this->fontState = $manager->getFontState();
        $this->document = $manager->getModule(Document::class);
    }

    /**
     * Implementation of the FPDF::SetFont() method.
     *
     * @param string|null $family
     * @param string $style
     * @param string $size
     * @throws \InvalidArgumentException
     */
    public function set($family, $style, $size)
    {
        $family = \strtolower($family);
        $style = \strtolower($style);

        if ($family === 'arial') {
            $family = 'helvetica';
        }

        if ($family == null) {
            $family = $this->currentFamily;
        }
        $this->currentFamily = $family;

        if (\strpos($style, 'u') !== false) {
            $this->underline = true;
            $style = str_replace('u', '', $style);
        } else {
            $this->underline = false;
        }

        $fontKey = $family . $style;

        if (!isset($this->fonts[$fontKey])) {
            if (isset(self::$fontMapping[$fontKey])) {
                $name = '\\' . self::$fontMapping[$fontKey];

                /** @noinspection PhpUndefinedMethodInspection */
                $this->fonts[$fontKey] = $name::create($this->document->get());
            } else {
                throw new \InvalidArgumentException(sprintf('Font "%s" with style "%s" not found.', $family, $style));
            }
        }

        $this->fontState->font = $this->fonts[$fontKey];
        if ($size !== '') {
            $this->fontState->fontSize = $size;
        }
    }

    /**
     * Implementation of the FPDF::AddFont() method.
     *
     * @param string $family
     * @param string $style
     * @param string|\SetaPDF_Core_Font_FontInterface $pathOrInstance
     * @throws \InvalidArgumentException
     * @throws \SetaPDF_Core_Font_Exception
     * @throws \SetaPDF_Exception_NotImplemented
     */
    public function add($family, $style, $pathOrInstance)
    {
        $family = \strtolower($family);
        $style = \strtolower($style);
        $fontKey = $family . $style;

        if (isset($this->fonts[$fontKey])) {
            throw new \InvalidArgumentException(
                \sprintf('Font "%s" with style "%s" was aready added.', $family, $style)
            );
        }

        if (!($pathOrInstance instanceof \SetaPDF_Core_Font_FontInterface)) {
            if (!file_exists($pathOrInstance)) {
                throw new \InvalidArgumentException('File not found (' . $pathOrInstance . ').');
            }

            $extension = \pathinfo($pathOrInstance, PATHINFO_EXTENSION);

            switch ($extension) {
                case 'otf':
                case 'ttf':
                    $instance = new \SetaPDF_Core_Font_Type0_Subset($this->document->get(), $pathOrInstance);
                    break;
                default:
                    throw new \SetaPDF_Exception_NotImplemented(
                        sprintf('File extension "%s" currently not supported (%s).', $extension, $pathOrInstance)
                    );
            }
        } else {
            $instance = $pathOrInstance;
        }

        $this->fonts[$fontKey] = $instance;
    }

    /**
     * Tries to draw a underline.
     *
     * This function draws a underline when its enabled, otherwise it will do nothing.
     *
     * @param int|float $x
     * @param int|float $y
     * @param int|float $width
     * @throws \BadMethodCallException
     */
    public function doUnderline($x, $y, $width)
    {
        if (!$this->underline) {
            return;
        }

        $converter = $this->manager->getConverter();

        $font = $this->fontState->getNewFont();

        $fontSizePt = $this->fontState->getNewFontSize();
        $fontSize = $converter->revert($fontSizePt);

        $this->manager->getCanvas()->draw()->setStrokingColor($this->manager->getColorState()->textColor);
        $this->manager->getCanvas()->draw()->rect(
            $x,
            $y + $converter->convert($font->getUnderlinePosition() / 1000 * $fontSize),
            $width,
            -($font->getUnderlineThickness() / 1000 * $fontSizePt),
            \SetaPDF_Core_Canvas_Draw::STYLE_FILL
        );
        $strokingColor = $this->manager->getColorState()->strokingColor;
        if ($strokingColor) {
            $this->manager->getCanvas()->draw()->setStrokingColor($strokingColor);
        }
    }
}