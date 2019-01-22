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
use setasign\SetaFpdf\Position\Converter;
use setasign\SetaFpdf\SetaFpdf;
use setasign\SetaFpdf\StateBuffer\StateBufferInterface;

class Document implements StateBufferInterface
{
    /**
     * @param \SetaPDF_Core_Document $document
     * @param $displayMode
     * @throws \InvalidArgumentException
     */
    protected static function applyDisplayMode(\SetaPDF_Core_Document $document, $displayMode)
    {
        if ($displayMode === null || $document->getCatalog()->getPages()->count() === 0) {
            return;
        }

        list($zoom, $layout) = $displayMode;

        $catalog = $document->getCatalog();

        switch (strtolower($layout)) {
            case 'default':
                break;
            case 'single':
                $catalog->setPageLayout(
                    \SetaPDF_Core_Document_PageLayout::SINGLE_PAGE
                );
                break;
            case 'continuous':
                $catalog->setPageLayout(
                    \SetaPDF_Core_Document_PageLayout::ONE_COLUMN
                );
                break;
            case 'two':
                $catalog->setPageLayout(
                    \SetaPDF_Core_Document_PageLayout::TWO_COLUMN_LEFT
                );
                break;
            default:
                throw new \InvalidArgumentException('Incorrect layout display mode: ' . $layout);
        }

        $pages = $catalog->getPages();
        if (is_string($zoom)) {
            switch (strtolower($zoom)) {
                case 'fullpage':
                    $_zoomMode = \SetaPDF_Core_Document_Destination::createDestinationArray(
                        $pages->getPage(1)->getPageObject(),
                        \SetaPDF_Core_Document_Destination::FIT_MODE_FIT
                    );
                    break;
                case 'fullwidth':
                    $_zoomMode = \SetaPDF_Core_Document_Destination::createDestinationArray(
                        $pages->getPage(1)->getPageObject(),
                        \SetaPDF_Core_Document_Destination::FIT_MODE_FIT_H
                    );
                    break;
                case 'real':
                    $_zoomMode = \SetaPDF_Core_Document_Destination::createDestinationArray(
                        $pages->getPage(1)->getPageObject(),
                        \SetaPDF_Core_Document_Destination::FIT_MODE_XYZ,
                        null,
                        null,
                        1
                    );
                    break;
                default:
                    throw new \InvalidArgumentException('Incorrect zoom display mode: ' . $zoom);
            }
        } else {
            $_zoomMode = \SetaPDF_Core_Document_Destination::createDestinationArray(
                $pages->getPage(1)->getPageObject(),
                \SetaPDF_Core_Document_Destination::FIT_MODE_XYZ,
                null,
                null,
                $zoom / 100
            );
        }

        $catalog->setOpenAction(new \SetaPDF_Core_Document_Destination($_zoomMode));
    }

    /**
     * Parses the orientation to a SetaPDF orientation.
     *
     * @param string $orientation
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function parseOrientation($orientation)
    {
        $orientation = strtolower($orientation);
        if ($orientation === 'p' || $orientation === 'portrait') {
            return \SetaPDF_Core_PageFormats::ORIENTATION_PORTRAIT;
        }

        if ($orientation === 'l' || $orientation === 'landscape') {
            return \SetaPDF_Core_PageFormats::ORIENTATION_LANDSCAPE;
        }

        throw new \InvalidArgumentException(sprintf('Unknown orientation: %s', $orientation));
    }

    /**
     * Parses size values, and formats it into an array.
     *
     * @param Converter $converter
     * @param array|string $size Expects an string (@see \SetaPDF_Core_PageFormats::getFormat) or an array with 2 values
     *                           in unit.
     * @param string $orientation
     * @return array Size in Unit
     */
    public static function parseSize(Converter $converter, $size, $orientation)
    {
        if (\is_string($size)) {
            $size = strtolower($size);
        } elseif (\is_array($size)) {
            if (!isset($size[0], $size[1])) {
                throw new \InvalidArgumentException('Invalid size array.');
            }
            $size = [
                $converter->toPt($size[0]),
                $converter->toPt($size[1])
            ];
        }

        $result = \SetaPDF_Core_PageFormats::getFormat($size, $orientation);

        $width = $converter->fromPt($result['width']);
        $result['width'] = $width;
        $result[0] = $width;

        $height = $converter->fromPt($result['height']);
        $result['height'] = $height;
        $result[1] = $height;

        return $result;
    }

    /**
     * The manager.
     *
     * @var Manager
     */
    private $manager;

    /**
     * The footer callable.
     *
     * By default this should be SetaFpdf::Footer().
     *
     * @var callable
     */
    private $footerCallable;

    /**
     * The header callable.
     *
     * By default this should be SetaFpdf::Header().
     *
     * @var callable
     */
    private $headerCallable;

    /**
     * The page break check callable.
     *
     * By default this should be SetaFpdf::AcceptPageBreak()
     *
     * @var callable
     */
    private $pageBreakCallable;

    /**
     * The document instance.
     *
     * @var \SetaPDF_Core_Document
     */
    private $document;

    /**
     * The current orientation.
     *
     * @var string \SetaPDF_Core_PageFormats::ORIENTATION_PORTRAIT or \SetaPDF_Core_PageFormats::ORIENTATION_LANDSCAPE
     */
    private $orientation;

    /**
     * The default orientation
     *
     * @var string \SetaPDF_Core_PageFormats::ORIENTATION_PORTRAIT or \SetaPDF_Core_PageFormats::ORIENTATION_LANDSCAPE
     */
    private $defaultOrientation;

    /**
     * The current size.
     *
     * @var array
     */
    private $size;

    /**
     * The default size.
     *
     * @var array
     */
    private $defaultSize;

    /**
     * The current rotation.
     *
     * @var int
     */
    private $rotation;

    /**
     * A flag that disables the page break.
     *
     * This flag does not disable the handlePageBreak function.
     * Also its a second to completely disallow adding pages, while calling the footer or header function.
     *
     * @var bool
     */
    private $pageBreakDisabled;

    /**
     * A flag that disables the page break.
     *
     * This flag does not disable the handlePageBreak function.
     * You can set it using {@see Document::setAutoPageBreak()}
     *
     * @var bool
     */
    private $autoPageBreak;

    /**
     * The stored page break flag.
     *
     * @var bool
     */
    private $oldAutoPageBreak;

    /**
     * The page number of the current active page.
     *
     * @var int|null
     */
    protected $activePage;

    /**
     * Document constructor.
     *
     * @param Manager $manager
     * @param mixed $defaultOrientation
     * @param mixed $defaultSize
     * @param callable $footerCallable
     * @param callable $headerCallable
     * @param callable $pageBreakCallable
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     */
    public function __construct(
        Manager $manager,
        $defaultOrientation,
        $defaultSize,
        $footerCallable,
        $headerCallable,
        $pageBreakCallable
    ) {
        $this->manager = $manager;

        $this->document = new \SetaPDF_Core_Document();
        $this->document->setWriter(new \SetaPDF_Core_Writer_String());

        $this->defaultOrientation = self::parseOrientation($defaultOrientation);
        $this->defaultSize = self::parseSize($this->manager->getConverter(), $defaultSize, $this->defaultOrientation);

        $this->footerCallable = $footerCallable;
        $this->headerCallable = $headerCallable;
        $this->pageBreakCallable = $pageBreakCallable;

        $this->pageBreakDisabled = false;
        $this->autoPageBreak = true;
    }

    /**
     * Gets the default page width.
     *
     * @return int|float Returns the default width in unit
     */
    public function getDefaultWidth()
    {
        return $this->defaultSize['width'];
    }

    /**
     * Gets the default page height.
     *
     * @return int|float Returns the default height in unit
     */
    public function getDefaultHeight()
    {
        return $this->defaultSize['height'];
    }

    /**
     * Implementation of the FPDF::AddPage() method.
     *
     * @param string $orientation You should use one of the following constants:
     *                            \SetaPDF_Core_PageFormats::ORIENTATION_PORTRAIT
     *                            \SetaPDF_Core_PageFormats::ORIENTATION_LANDSCAPE
     * @param string|array $size
     * @param int $rotation
     * @throws \InvalidArgumentException
     */
    public function addPage($orientation, $size, $rotation)
    {
        if ($rotation % 90 !== 0) {
            throw new \InvalidArgumentException(\sprintf('Incorrect rotation value: %s', $rotation));
        }

        /** @noinspection TypeUnsafeComparisonInspection */
        if ($orientation != '') {
            $orientation = self::parseOrientation($orientation);
        } else {
            $orientation = $this->defaultOrientation;
        }

        /** @noinspection TypeUnsafeComparisonInspection */
        if ($size != '') {
            $size = self::parseSize($this->manager->getConverter(), $size, $orientation);
        } else {
            $size = $this->defaultSize;
        }

        $pageCount = $this->getPageCount();

        $margin = $this->manager->getMargin();

        $this->manager->save();

        if ($pageCount !== 0) {
            if ($pageCount !== $this->activePage) {
                $this->setActivePage($pageCount);
            }
            $this->finalizePage($pageCount);
            $this->pageBreakDisabled = true;
            \call_user_func($this->footerCallable);
            $this->pageBreakDisabled = false;
        }

        $margin->store();
        $this->manager->restore();

        $widthPt = $this->manager->getConverter()->toPt($size[0]);
        $heightPt = $this->manager->getConverter()->toPt($size[1]);
        $sizePt = [$widthPt, $heightPt];

        $this->document->getCatalog()->getPages()->create($sizePt, $orientation);
        $this->rotation = $rotation;

        $this->size = $size;
        $this->orientation = $orientation;

        $this->setActivePage($pageCount + 1);
        $this->manager->getCursor()->reset();

        $this->manager->save();

        $this->pageBreakDisabled = true;
        \call_user_func($this->headerCallable);
        $this->pageBreakDisabled = false;

        $margin->store();
        $this->manager->getCursor()->store();

        $this->manager->restore();
    }

    /**
     * Finalizes a page.
     *
     * @param \SetaPDF_Core_Document_Page|int $page
     * @throws \InvalidArgumentException
     */
    public function finalizePage($page)
    {
        if (is_int($page)) {
            $page = $this->document->getCatalog()->getPages()->getPage($page);
        }
        $page->setRotation($this->rotation);
    }

    /**
     * Calls the pageBreak function and adds a page according to the result.
     *
     * @throws \InvalidArgumentException
     */
    public function handleAutoPageBreak()
    {
        if (\call_user_func($this->pageBreakCallable)) {
            $this->addPage($this->orientation, $this->size, $this->rotation);
        }
    }

    /**
     * Implementation of the FPDF::SetAutoPageBreak() method.
     *
     * @param bool $auto
     */
    public function setAutoPageBreak($auto)
    {
        $this->autoPageBreak = $auto;
    }

    /**
     * Checks if a page break is allowed.
     *
     * @return bool
     */
    public function pageBreakAllowed()
    {
        return (!$this->pageBreakDisabled) && $this->autoPageBreak;
    }

    /**
     * Returns the number of created pages.
     *
     * @return int
     */
    public function getPageCount()
    {
        return $this->document->getCatalog()->getPages()->count();
    }

    /**
     * Implantation of FPDF::Output()
     *
     * @param string $destination
     * @param string $name
     * @param array $displayMode
     * @return null|string
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     * @throws \SetaPDF_Core_Exception
     */
    public function output($destination, $name, array $displayMode = null)
    {
        $destination = strtolower($destination);
        switch ($destination) {
            case 'i':
            case 'd':
                $writer = new \SetaPDF_Core_Writer_Http($name, $destination === 'i');
                break;
            case 'f':
                $writer = new \SetaPDF_Core_Writer_File($name);
                break;
            case 's':
                $writer = new \SetaPDF_Core_Writer_String();
                break;
            default:
                throw new \InvalidArgumentException('Invalid destination: ' . $destination);
        }

        if ($this->document->getState() === \SetaPDF_Core_Document::STATE_NONE) {
            $pageCount = $this->getPageCount();

            if ($pageCount === 0) {
                $this->addPage($this->defaultOrientation, $this->defaultSize, 0);
                $pageCount++;
            }

            $this->setActivePage($pageCount);
            $this->finalizePage($pageCount);
            $this->pageBreakDisabled = true;
            \call_user_func($this->footerCallable);
            $this->pageBreakDisabled = false;

            self::applyDisplayMode($this->document, $displayMode);
            $info = $this->document->getInfo();
            $info->setProducer('SetaFPDF ' . SetaFpdf::VERSION);
            if ($info->getSyncMetadata()) {
                $info->syncMetadata();
            }

            $this->document->save()->finish();
        }

        /** @var \SetaPDF_Core_Writer_String $stringWriter */
        $stringWriter = $this->document->getWriter();

        $writer->start();
        $writer->write($stringWriter->getBuffer());
        $writer->finish();

        if ($writer instanceof \SetaPDF_Core_Writer_String) {
            return $writer->getBuffer();
        }
        return null;
    }

    /**
     * Sets the active page.
     *
     * @param int $pageNumber
     */
    public function setActivePage($pageNumber)
    {
        $this->manager->setCanvas(
            $this->document->getCatalog()->getPages()->getPage($pageNumber)->getCanvas()
        );

        $this->activePage = $pageNumber;
        $this->manager->getCursor()->reset();
    }

    /**
     * Returns the number of the active page.
     *
     * @return int|null
     * @throws \BadMethodCallException
     */
    public function getActivePageNo()
    {
        if ($this->activePage === null) {
            throw new \BadMethodCallException('No page added yet.');
        }

        return $this->activePage;
    }

    /**
     * Gets the document instance.
     *
     * @return \SetaPDF_Core_Document
     */
    public function get()
    {
        return $this->document;
    }

    /**
     * @inheritdoc
     */
    public function cleanUp()
    {
        $this->manager = null;
        $this->footerCallable = $this->headerCallable = null;
    }

    /**
     * @inheritdoc
     */
    public function reset()
    {
    }

    /**
     * @inheritdoc
     */
    public function store()
    {
        $this->oldAutoPageBreak = $this->autoPageBreak;
    }

    /**
     * @inheritdoc
     */
    public function restore()
    {
        $this->autoPageBreak = $this->oldAutoPageBreak;
    }
}
