<?php
/**
 * This file is part of SetaFPDF
 *
 * @package   setasign\SetaFpdf
 * @copyright Copyright (c) 2018 Setasign - Jan Slabon (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 */

namespace setasign\SetaFpdf;

use setasign\SetaFpdf\Modules\Cell;
use setasign\SetaFpdf\Modules\Color;
use setasign\SetaFpdf\Modules\Document;
use setasign\SetaFpdf\Modules\Draw;
use setasign\SetaFpdf\Modules\Font;
use setasign\SetaFpdf\Modules\Link;
use setasign\SetaFpdf\Modules\Margin;
use setasign\SetaFpdf\Modules\Text;
use setasign\SetaFpdf\Position\Converter;

class SetaFpdf
{
    const VERSION = 'v1.0.0beta';
    
    /**
     * The manager.
     *
     * @var Manager
     */
    protected $manager;

    /**
     * The display mode.
     *
     * @var array
     */
    protected $displayMode;

    /**
     * Class constructor.
     *
     * It allows to set up the page size, the orientation and the unit of measure used in all methods (except for font
     * sizes).
     *
     * @param string $orientation Default page orientation. Possible values are (case insensitive):
     *                              P or Portrait
     *                              L or Landscape
     *                            Default value is P.
     * @param string $unit User unit. Possible values are:
     *                       pt: point
     *                       mm: millimeter
     *                       cm: centimeter
     *                       in: inch
     *                     A point equals 1/72 of inch, that is to say about 0.35 mm (an inch being 2.54 cm).
     *                     This is a very common unit in typography; font sizes are expressed in that unit.
     * @param string $size The size used for pages. It can be either one of the following values (case insensitive):
     *                       A3
     *                       A4
     *                       A5
     *                       Letter
     *                       Legal
     *                     or an array containing the width and the height (expressed in the unit given by unit).
     *                     Default value is A4.
     * @throws \InvalidArgumentException
     */
    public function __construct($orientation = 'P', $unit = 'mm', $size = 'A4')
    {
        if ($unit === 'pt') {
            $factor = Converter::UNIT_PT;
        } elseif ($unit === 'mm') {
            $factor = Converter::UNIT_MM;
        } elseif ($unit === 'cm') {
            $factor = Converter::UNIT_CM;
        } elseif ($unit === 'in') {
            $factor = Converter::UNIT_IN;
        } else {
            throw new \InvalidArgumentException(sprintf('Incorrect unit: %s', $unit));
        }

        $this->manager = new Manager($factor);
        $this->manager->getCanvasState()->lineCap = \SetaPDF_Core_Canvas_Path::LINE_CAP_PROJECTING_SQUARE;

        $this->manager->getModule(Document::class, [
            $orientation, $size, [$this, 'Footer'], [$this, 'Header'], [$this, 'AcceptPageBreak']
        ]);

        $this->SetDrawColor(0);
        $this->SetTextColor(0);
        $this->SetFillColor(0);
    }

    /**
     * Whenever a page break condition is met, this method is called,
     *
     * ...and the break is issued or not depending on the returned value.
     *
     * The default implementation returns a value according to the mode selected by SetAutoPageBreak().
     * This method is called automatically and should not be called directly by the application.
     *
     * @return bool
     */
    public function AcceptPageBreak()
    {
        return true;
    }

    /**
     * Imports a TrueType, OpenType font and makes it available.
     *
     * The font will always be subset.
     *
     * @param string $family
     * @param string $style
     * @param string|\SetaPDF_Core_Font_FontInterface $pathOrInstance
     * @throws \SetaPDF_Core_Font_Exception
     * @throws \SetaPDF_Exception_NotImplemented
     */
    public function AddFont($family, $style = '', $pathOrInstance = '')
    {
        $this->manager->getModule(Font::class)->add($family, $style, $pathOrInstance);
    }

    /**
     * Creates a new internal link and returns its identifier.
     *
     * An internal link is a clickable area which directs to another place within the document. The identifier can then
     * be passed to Cell(), Write(), Image() or Link().
     *
     * The destination is defined with SetLink().
     *
     * @return int
     */
    public function AddLink()
    {
        return $this->manager->getModule(Link::class)->addLink();
    }

    /**
     * Adds a new page to the document.
     *
     * If a page is already present, the Footer() method is called first to output the footer. Then the page is added,
     * the current position set to the top-left corner according to the left and top margins, and Header() is called to
     * display the header. The font which was set before calling is automatically restored. There is no need to call
     * SetFont() again if you want to continue with the same font. The same is true for colors and line width. The
     * origin of the coordinate system is at the top-left corner and increasing ordinates go downwards.
     *
     * @param string $orientation Page orientation. Possible values are (case insensitive):
     *                              P or Portrait
     *                              L or Landscape
     *                            The default value is the one passed to the constructor.
     * @param string $size Page size. It can be either one of the following values (case insensitive):
     *                       A3
     *                       A4
     *                       A5
     *                       Letter
     *                       Legal
     *                       or an array containing the width and the height (expressed in user unit).
     *                     The default value is the one passed to the constructor.
     * @param int $rotation Angle by which to rotate the page. It must be a multiple of 90; positive values mean
     *                      clockwise rotation. The default value is 0.
     * @throws \InvalidArgumentException
     */
    public function AddPage($orientation = '', $size = '', $rotation = 0)
    {
        $this->manager->getModule(Document::class)->addPage($orientation, $size, $rotation);
    }

    /**
     * Defines an alias for the total number of pages.
     *
     * Currently not supported {@see SetaFpdf::SetPage()} for "manual" processing.
     *
     * @throws \SetaPDF_Exception_NotImplemented
     */
    public function AliasNbPages()
    {
        throw new \SetaPDF_Exception_NotImplemented('This method is not supported in SetaFpdf.');
    }

    /**
     * Prints a cell (rectangular area) with optional borders, background color and character string.
     *
     * The upper-left corner of the cell corresponds to the current position. The text can be aligned or centered.
     * After the call, the current position moves to the right or to the next line. It is possible to put a link on the
     * text.
     * If automatic page breaking is enabled and the cell goes beyond the limit, a page break is done before outputting.
     *
     * @param int|float $w Cell width. If 0, the cell extends up to the right margin.
     * @param int|float $h Cell height.
     * @param string $txt String to print.
     * @param int|string $border
     * @param int $ln
     * @param string $align
     * @param bool $fill Indicates if the cell background must be painted (true) or transparent (false).
     * @param string $link URL or identifier returned by AddLink().
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws \SetaPDF_Core_Font_Exception
     * @throws \SetaPDF_Core_Type_IndirectReference_Exception
     * @throws \SetaPDF_Exception_NotImplemented
     */
    public function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '')
    {
        $txt = \SetaPDF_Core_Text::normalizeLineBreaks($txt);
        $this->manager->getModule(Cell::class)->cell($w, $h, $txt, $border, $ln, $align, $fill, $link);
    }

    /**
     * Terminates the PDF document.
     *
     * It is not necessary to call this method explicitly because Output() does it automatically.
     * If the document contains no page, AddPage() is called to prevent from getting an invalid document.
     */
    public function Close()
    {
        $this->manager->cleanUp();
    }

    /**
     * This method is used to render the page footer.
     *
     * It is automatically called by AddPage() and Close() and should not be called directly by the application.
     * The implementation in FPDF is empty, so you have to subclass it and override the method if you want a specific
     * processing.
     */
    public function Footer()
    {
    }

    /**
     * Returns the current page height.
     *
     * @return float|int
     * @throws \BadMethodCallException
     */
    public function GetPageHeight()
    {
        return $this->manager->getConverter()->fromPt($this->manager->getHeight());
    }

    /**
     * Returns the current page width.
     *
     * @return float|int
     * @throws \BadMethodCallException
     */
    public function GetPageWidth()
    {
        return $this->manager->getConverter()->fromPt($this->manager->getWidth());
    }

    /**
     * Returns the length of a string in user unit.
     *
     * A font must be selected.
     *
     * @param string $s The string whose length is to be computed.
     * @return float|int
     */
    public function GetStringWidth($s)
    {
        return $this->manager->getModule(Cell::class)->getStringWidth($s, 'UTF-8');
    }

    /**
     * Returns the abscissa of the current position.
     *
     * @return int|float
     */
    public function GetX()
    {
        return $this->manager->getCursor()->getX();
    }

    /**
     * Returns the ordinate of the current position.
     *
     * @return int|float
     */
    public function GetY()
    {
        return $this->manager->getCursor()->getY();
    }

    /**
     * This method is used to render the page header.
     *
     * It is automatically called by AddPage() and should not be called directly by the application. The implementation
     * in FPDF is empty, so you have to subclass it and override the method if you want a specific processing.
     */
    public function Header()
    {
    }

    /**
     * Puts an image.
     *
     * The size it will take on the page can be specified in different ways:
     * - explicit width and height (expressed in user unit or dpi)
     * - one explicit dimension, the other being calculated automatically in order to keep the original proportions
     * - no explicit dimension, in which case the image is put at 96 dpi
     *
     * @param string $file Path of the image.
     * @param int|float|null $x Abscissa of the upper-left corner. If not specified or equal to null,
     *                          the current abscissa is used.
     * @param int|float|null $y Ordinate of the upper-left corner. If not specified or equal to null,
     *                          the current ordinate is used; moreover, a page break is triggered first if
     *                          necessary (in case automatic page breaking is enabled) and, after the call, the current
     *                          ordinate is moved to the bottom of the image.
     * @param int $w Width of the image in the page. There are three cases:
     * @param int $h Height of the image in the page. There are three cases:
     * @param string $type Unused property - remains at place to have the same signature as FPDF
     * @param string $link URL or identifier returned by AddLink().
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function Image(
        $file,
        $x = null,
        $y = null,
        $w = 0,
        $h = 0,
        /** @noinspection PhpUnusedParameterInspection */ $type = '',
        $link = ''
    ) {
        $this->manager->getModule(Draw::class)->image($file, $x, $y, $w, $h, $link);
    }

    /**
     * Draws a line between two points.
     *
     * @param int|float $x1 Abscissa of first point.
     * @param int|float $y1 Ordinate of first point.
     * @param int|float $x2 Abscissa of second point.
     * @param int|float $y2 Ordinate of second point.
     * @throws \BadMethodCallException
     */
    public function Line($x1, $y1, $x2, $y2)
    {
        $this->manager->getModule(Draw::class)->line($x1, $y1, $x2, $y2);
    }

    /**
     * Puts a link on a rectangular area of the page.
     *
     * Text or image links are generally put via Cell(), Write() or Image(), but this method can be useful for instance
     * to define a clickable area inside an image.
     *
     * @param int|float $x Abscissa of the upper-left corner of the rectangle.
     * @param int|float $y Ordinate of the upper-left corner of the rectangle.
     * @param int|float $w Width of the rectangle.
     * @param int|float $h Height of the rectangle.
     * @param mixed $link URL or identifier returned by AddLink().
     * @throws \BadMethodCallException
     */
    public function Link($x, $y, $w, $h, $link)
    {
        $this->manager->getModule(Link::class)->link($x, $y, $w, $h, $link);
    }

    /**
     * Performs a line break.
     *
     * The current abscissa goes back to the left margin and the ordinate increases by the amount passed in parameter.
     *
     * @param float|int|null $h The height of the break.
     *                          By default, the value equals the height of the last printed cell.
     */
    public function Ln($h = null)
    {
        $this->manager->getCursor()->setX($this->manager->getModule(Margin::class)->getLeft());

        if ($h === null) {
            $h = $this->manager->getLastHeight();
        }

        $this->manager->getCursor()->addY($h);
    }

    /**
     * This method allows printing text with line breaks.
     *
     * They can be automatic (as soon as the text reaches the right
     * border of the cell) or explicit (via the \n character). As many cells as necessary are output, one below the
     * other. Text can be aligned, centered or justified. The cell block can be framed and the background painted.
     *
     * @param int|float $w Width of cells. If 0, they extend up to the right margin of the page.
     * @param int|float $h Height of cells.
     * @param string $txt String to print.
     * @param mixed $border Indicates if borders must be drawn around the cell block. The value can be either a number:
     *                        0: no border
     *                        1: frame
     *                      Or a string containing some or all of the following characters (in any order):
     *                        L: left
     *                        T: top
     *                        R: right
     *                        B: bottom
     * @param string $align Sets the text alignment. Possible values are:
     *                        L: left alignment
     *                        C: center
     *                        R: right alignment
     *                        J: justification (default value)
     * @param bool $fill Indicates if the cell background must be painted (true) or transparent (false).
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws \SetaPDF_Core_Font_Exception
     * @throws \SetaPDF_Core_Type_IndirectReference_Exception
     * @throws \SetaPDF_Exception_NotImplemented
     */
    public function MultiCell($w, $h, $txt, $border = 0, $align = 'J', $fill = false)
    {
        $this->manager->getModule(Cell::class)->multiCell($w, $h, $txt, $border, $align, $fill);
    }

    /**
     * Send the document to a given destination: browser, file or string.
     *
     * In the case of a browser, the PDF viewer may be used or a download may be forced. The method first calls Close()
     * if necessary to terminate the document.
     *
     * @param string $dest Destination where to send the document. It can be one of the following:
     *                       I: send the file inline to the browser. The PDF viewer is used if available.
     *                       D: send to the browser and force a file download with the name given by name.
     *                       F: save to a local file with the name given by name (may include a path).
     *                       S: return the document as a string.
     * @param string $name The name of the file. It is ignored in case of destination S.
     * @return null|string
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws \SetaPDF_Core_Exception
     */
    public function Output($dest = '', $name = '')
    {
        $this->manager->getModule(Link::class)->writeLinks(
            $this->manager->getModule(Document::class)->get()
        );

        if (\strlen($name) === 1 && strlen($dest) !== 1) {
            // Fix parameter order.
            list($dest, $name) = [$name, $dest];
        }

        if ($dest === '') {
            $dest = 'I';
        }

        if ($name === '') {
            $name = 'doc.pdf';
        }

        return $this->manager->getModule(Document::class)->output($dest, $name, $this->displayMode);
    }

    /**
     * Returns the current page number.
     *
     * @return int
     */
    public function PageNo()
    {
        return $this->manager->getModule(Document::class)->getActivePageNo();
    }

    /**
     * Returns the page count.
     *
     * @return int
     */
    public function getPageCount()
    {
        return $this->manager->getModule(Document::class)->getPageCount();
    }

    /**
     * Outputs a rectangle.
     *
     * It can be drawn (border only), filled (with no border) or both.
     *
     * @param int|float $x Abscissa of upper-left corner.
     * @param int|float $y Ordinate of upper-left corner.
     * @param int|float $w Width.
     * @param int|float $h Height.
     * @param string $style Style of rendering. Possible values are:
     *                        D or empty string: draw. This is the default value.
     *                        F: fill
     *                        DF or FD: draw and fill
     * @throws \BadMethodCallException
     */
    public function Rect($x, $y, $w, $h, $style = '')
    {
        $this->manager->getModule(Draw::class)->rect($x, $y, $w, $h, $style);
    }

    /**
     * Defines the author of the document.
     *
     * @param string $author The name of the author.
     */
    public function SetAuthor($author)
    {
        $this->manager->getModule(Document::class)
            ->get()
            ->getInfo()
            ->setSubject($author);
    }

    /**
     * Enables or disables the automatic page breaking mode.
     *
     * When enabling, the second parameter is the distance from the bottom of the page that defines the triggering
     * limit. By default, the mode is on and the margin is 2 cm.
     *
     * @param bool $auto Boolean indicating if mode should be on or off.
     * @param int|float $margin Distance from the bottom of the page.
     */
    public function SetAutoPageBreak($auto, $margin = 0)
    {
        $this->manager->getModule(Document::class)->setAutoPageBreak($auto);
        $this->manager->getModule(Margin::class)->setBottom($margin);
    }

    /**
     * Activates or deactivates page compression.
     *
     * When activated, the internal representation of each page is compressed, which leads to a compression ratio of
     * about 2 for the resulting document. Compression is on by default.
     *
     * Note: Compression can't be turned off while using SetaPDF.
     *
     * @param bool $value Boolean indicating if compression must be enabled.
     * @throws \SetaPDF_Exception_NotImplemented
     */
    public function SetCompression($value = true)
    {
        if ($value === false) {
            throw new \SetaPDF_Exception_NotImplemented('SetaPDF only supports compressed streams.');
        }
    }

    /**
     * Defines the creator of the document. This is typically the name of the application that generates the PDF.
     *
     * @param string $creator The name of the creator.
     */
    public function SetCreator($creator)
    {
        $this->manager->getModule(Document::class)
            ->get()
            ->getInfo()
            ->setSubject($creator);
    }

    /**
     * Defines the way the document is to be displayed by the viewer. The zoom level can be set: pages can be displayed
     * entirely on screen, occupy the full width of the window, use real size, be scaled by a specific zooming factor
     * or use viewer default (configured in the Preferences menu of Adobe Reader). The page layout can be specified too:
     * single at once, continuous display, two columns or viewer default.
     *
     * @param string|int|float $zoom The zoom to use. It can be one of the following string values:
     *                                 fullpage: displays the entire page on screen
     *                                 fullwidth: uses maximum width of window
     *                                 real: uses real size (equivalent to 100% zoom)
     *                                 default: uses viewer default mode
     *                                 or a number indicating the zooming factor to use.
     * @param string $layout The page layout. Possible values are:
     *                         single: displays one page at once
     *                         continuous: displays pages continuously
     *                         two: displays two pages on two columns
     *                         default: uses viewer default mode
     */
    public function SetDisplayMode($zoom, $layout = 'default')
    {
        $this->displayMode = [$zoom, $layout];
    }

    /**
     * Defines the color used for all drawing operations (lines, rectangles and cell borders). It can be expressed in
     * RGB components, CMYK components or gray scale. The method can be called before the first page is created and the
     * value is retained from page to page.
     *
     * @param int|float $r If g and b are given, red component; if $k is given, cyan component;
     *                     if not, indicates the gray level. Value between 0 and 255.
     * @param int|float|null $g If $k is given, magenta component; if not Green component. Value between 0 and 255.
     * @param int|float|null $b If $k is given, yellow component; if nto Blue component. Value between 0 and 255.
     * @param int|float|null $k If $k black component. Value between 0 and 255.
     */
    public function SetDrawColor($r, $g = null, $b = null, $k = null)
    {
        $this->manager->getModule(Color::class)->setDraw($r, $g, $b, $k);
    }

    /**
     * Defines the color used for all filling operations (filled rectangles and cell backgrounds). It can be expressed
     * in RGB components, CMYK components or gray scale. The method can be called before the first page is created and
     * the value is retained from page to page.
     *
     * @param int|float $r If g and b are given, red component; if $k is given, cyan component;
     *                     if not, indicates the gray level. Value between 0 and 255.
     * @param int|float|null $g If $k is given, magenta component; if not Green component. Value between 0 and 255.
     * @param int|float|null $b If $k is given, yellow component; if nto Blue component. Value between 0 and 255.
     * @param int|float|null $k If $k black component. Value between 0 and 255.
     */
    public function SetFillColor($r, $g = null, $b = null, $k = null)
    {
        $this->manager->getModule(Color::class)->setFill($r, $g, $b, $k);
    }

    /**
     * Sets the font used to print character strings. It is mandatory to call this method at least once before printing
     * text or the resulting document would not be valid. The font can be either a standard one or a font added via the
     * AddFont() method. Standard fonts use the Windows encoding cp1252 (Western Europe). The method can be called
     * before the first page is created and the font is kept from page to page. If you just wish to change the current
     * font size, it is simpler to call SetFontSize().
     *
     * @param string $family Family font. It can be either a name defined by AddFont() or one of the
     *                       standard families (case insensitive):
     *                         Courier: (fixed with)
     *                         Helvetica or Arial: (synonymous; sans serif)
     *                         Times: (serif)
     *                         Symbol: (symbolic)
     *                         ZapfDingbats: (symbolic)
     *                       It is also possible to pass an empty string. In that case, the current family is kept.
     * @param string $style Font style. Possible values are (case insensitive):
     *                        empty string: regular
     *                        B: bold
     *                        I: italic
     *                        U: underline
     *                      or any combination. The default value is regular. Bold and italic styles do not apply to
     *                      Symbol and ZapfDingbats.
     * @param string $size Font size in points. The default value is the current size. If no size has been specified
     *                     since the beginning of the document, the value taken is 12.
     */
    public function SetFont($family, $style = '', $size = '')
    {
        $this->manager->getModule(Font::class)->set($family, $style, $size);
    }

    /**
     * Defines the size of the current font.
     *
     * @param int|float $size The size (in points).
     */
    public function SetFontSize($size)
    {
        $this->manager->getFontState()->fontSize = $size;
    }

    /**
     * Associates keywords with the document, generally in the form 'keyword1 keyword2 ...'.
     *
     * @param string $keywords The list of keywords.
     */
    public function SetKeywords($keywords)
    {
        $this->manager->getModule(Document::class)
            ->get()
            ->getInfo()
            ->setKeywords($keywords);
    }

    /**
     * Defines the left margin. The method can be called before creating the first page.
     * If the current abscissa gets out of page, it is brought back to the margin.
     *
     * @param int|float $margin The margin.
     */
    public function SetLeftMargin($margin)
    {
        $this->manager->getModule(Margin::class)->setLeft($margin);
    }

    /**
     * Defines the line width. By default, the value equals 0.2 mm. The method can be called before the first page is
     * created and the value is retained from page to page.
     *
     * @param int|float $width The width.
     */
    public function SetLineWidth($width)
    {
        $this->manager->getCanvasState()->lineWidth = $this->manager->getConverter()->toPt($width);
    }

    /**
     * Defines the page and position a link points to.
     *
     * @param int $link The link identifier returned by AddLink().
     * @param int|float $y Ordinate of target position; -1 indicates the current position.
     *                     The default value is 0 (top of page).
     * @param int $page Number of target page; -1 indicates the current page. This is the default value.
     */
    public function SetLink($link, $y = 0, $page = -1)
    {
        if ($page === -1) {
            $page = $this->PageNo();
        }

        $this->manager->getModule(Link::class)->setLink($link, $y, $page);
    }

    /**
     * Defines the left, top and right margins. By default, they equal 1 cm. Call this method to change them.
     *
     * @param int|float $left Left margin.
     * @param int|float $top Top margin.
     * @param null|int|float $right Right margin. Default value is the left one.
     */
    public function SetMargins($left, $top, $right = null)
    {
        $this->manager->getModule(Margin::class)->set($left, $top, $right);
    }

    /**
     * Defines the right margin. The method can be called before creating the first page.
     *
     * @param int|float $margin
     */
    public function SetRightMargin($margin)
    {
        $this->manager->getModule(Margin::class)->setRight($margin);
    }

    /**
     * Defines the subject of the document.
     *
     * @param string $subject The subject.
     */
    public function SetSubject($subject)
    {
        $this->manager->getModule(Document::class)
            ->get()
            ->getInfo()
            ->setSubject($subject);
    }

    /**
     * Defines the color used for text.
     *
     * It can be expressed in RGB components, CMYK components or gray scale. The method can be called
     * before the first page is created and the value is retained from page to page.
     *
     * @param int|float $r If g and b are given, red component; if $k is given, cyan component;
     *                     if not, indicates the gray level. Value between 0 and 255.
     * @param int|float|null $g If $k is given, magenta component; if not Green component. Value between 0 and 255.
     * @param int|float|null $b If $k is given, yellow component; if nto Blue component. Value between 0 and 255.
     * @param int|float|null $k If $k black component. Value between 0 and 255.
     */
    public function SetTextColor($r, $g = null, $b = null, $k = null)
    {
        $this->manager->getModule(Color::class)->setText($r, $g, $b, $k);
    }

    /**
     * Defines the title of the document.
     *
     * @param string $title The title.
     */
    public function SetTitle($title)
    {
        $this->manager->getModule(Document::class)
            ->get()
            ->getInfo()
            ->setTitle($title);
    }

    /**
     * Defines the top margin. The method can be called before creating the first page.
     *
     * @param int|float $margin The margin.
     */
    public function SetTopMargin($margin)
    {
        $this->manager->getModule(Margin::class)->setTop($margin);
    }

    /**
     * Defines the abscissa of the current position.
     *
     * If the passed value is negative, it is relative to the right of the page.
     *
     * @param int|float $x The value of the abscissa.
     */
    public function SetX($x)
    {
        $this->manager->getCursor()->setX($x);
    }

    /**
     * Defines the abscissa and ordinate of the current position.
     *
     * If the passed values are negative, they are relative respectively to the right and bottom of the page.
     *
     * @param int|float $x The value of the abscissa.
     * @param int|float $y The value of the ordinate.
     * @throws \BadMethodCallException
     */
    public function SetXY($x, $y)
    {
        $this->SetX($x);
        $this->SetY($y, false);
    }

    /**
     * Sets the ordinate and optionally moves the current abscissa back to the left margin.
     *
     * If the value is negative, it is relative to the bottom of the page.
     *
     * @param int|float $y The value of the ordinate.
     * @param bool $resetX Whether to reset the abscissa.
     * @throws \BadMethodCallException
     */
    public function SetY($y, $resetX = true)
    {
        if ($y < 0) {
            $y = $this->GetPageHeight() + $y;
        }

        $cursor = $this->manager->getCursor();
        $cursor->setY($y);
        if ($resetX) {
            $cursor->setX($this->manager->getModule(Margin::class)->getLeft());
        }
    }

    /**
     * Prints a character string.
     *
     * The origin is on the left of the first character, on the baseline. This method allows to place a string
     * precisely on the page, but it is usually easier to use Cell(), MultiCell() or Write() which are the standard
     * methods to print text.
     *
     * @param float|int $x Abscissa of the origin.
     * @param float|int $y Ordinate of the origin.
     * @param string $txt String to print.
     * @throws \BadMethodCallException
     * @throws \SetaPDF_Core_Font_Exception
     * @throws \SetaPDF_Core_Type_IndirectReference_Exception
     * @throws \SetaPDF_Exception_NotImplemented
     */
    public function Text($x, $y, $txt)
    {
        $this->manager->getModule(Text::class)->text($x, $y, $txt);
    }

    /**
     * This method prints text from the current position.
     *
     * When the right margin is reached (or the \n character is met) a line break occurs and text continues from the
     * left margin. Upon method exit, the current position is left just at the end of the text. It is possible to put a
     * link on the text.
     *
     * @param float|int $h Line height.
     * @param string $txt String to print.
     * @param string $link URL or identifier returned by AddLink().
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws \SetaPDF_Core_Font_Exception
     * @throws \SetaPDF_Core_Type_IndirectReference_Exception
     * @throws \SetaPDF_Exception_NotImplemented
     */
    public function Write($h, $txt, $link = '')
    {
        $txt = \SetaPDF_Core_Text::normalizeLineBreaks($txt);

        $this->manager->getModule(Cell::class)->write($h, $txt, $link, 'UTF-8');
    }

    /**
     * Sets the active page number.
     *
     * @param null|int $pageNo Default value: last page.
     */
    public function SetPage($pageNo = null)
    {
        if ($pageNo === null) {
            $pageNo = $this->PageNo();
        }

        $this->manager->getModule(Document::class)->setActivePage($pageNo);
        $this->manager->getCursor()->reset();
    }

    /**
     * Gets the manager.
     *
     * @return Manager
     */
    public function getManager()
    {
        return $this->manager;
    }

    public function __isset($name)
    {
        return in_array(
            $name,
            [
                'page', 'x', 'y', 'w', 'h', 'FontSize', 'rMargin', 'cMargin', 'lMargin', 'tMargin', 'bMargin',
                'PageBreakTrigger', 'wpt', 'hpt'
            ]
        );
    }

    /**
     * Set internal FPDF values.
     *
     * @param $name
     * @param $value
     * @throws \InvalidArgumentException
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'page':
                $this->SetPage($value);
                break;
            case 'x':
                $this->manager->getCursor()->setX($value);
                break;
            case 'y':
                $this->manager->getCursor()->setY($value);
                break;
            case 'FontSize':
                $this->manager->getFontState()->fontSize = $value;
                break;
            case 'rMargin':
                $this->manager->getModule(Margin::class)->setRight($value);
                break;
            case 'lMargin':
                $this->manager->getModule(Margin::class)->setLeft($value);
                break;
            case 'tMargin':
                $this->manager->getModule(Margin::class)->setTop($value);
                break;
            case 'bMargin':
                $this->manager->getModule(Margin::class)->setBottom($value);
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Property "%s" cannot be set.', $name));
        }
    }

    /**
     * Get internal FPDF values.
     *
     * @param $name
     * @return float|int
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function __get($name)
    {
        switch ($name) {
            case 'page':
                return $this->PageNo();
            case 'pageCount':
                return $this->getPageCount();
            case 'x':
                return $this->manager->getCursor()->getX();
            case 'y':
                return $this->manager->getCursor()->getY();
            case 'wPt':
                return $this->manager->getWidth();
            case 'hPt':
                return $this->manager->getHeight();
            case 'w':
                return $this->GetPageWidth();
            case 'h':
                return $this->GetPageHeight();
            case 'FontSize':
                return $this->manager->getFontState()->getNewFontSize();
            case 'rMargin':
                return $this->manager->getModule(Margin::class)->getRight();
            case 'cMargin':
                return $this->manager->getModule(Margin::class)->getCell();
            case 'lMargin':
                return $this->manager->getModule(Margin::class)->getLeft();
            case 'tMargin':
                return $this->manager->getModule(Margin::class)->getTop();
            case 'bMargin':
                return $this->manager->getModule(Margin::class)->getBottom();
            case 'PageBreakTrigger':
                // todo solve via property
                $margin = $this->manager->getModule(Margin::class);

                return $this->manager->getConverter()->fromPt($this->manager->getCanvas()->getHeight())
                    - $margin->getBottom();

            default:
                throw new \InvalidArgumentException(sprintf('Property "%s" cannot be accessed.', $name));
        }
    }
}
