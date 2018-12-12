<?php
/**
 * This file is part of SetaFPDI
 *
 * @package   setasign\SetaFpdi
 * @copyright Copyright (c) 2018 Setasign - Jan Slabon (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 */

namespace setasign\SetaFpdi;

use setasign\SetaFpdf\Modules\Document;
use setasign\SetaFpdf\SetaFpdf;

class SetaFpdfTpl extends SetaFpdf
{
    /**
     * The template id counter.
     *
     * @var int
     */
    protected $templateId = 0;

    /**
     * @var \SetaPDF_Core_XObject_Form[]
     */
    protected $templates = [];

    /**
     * The currently active template id.
     *
     * When this value is not null, we are writing to a template.
     *
     * @var int|null
     */
    protected $currentTemplateId = null;

    /**
     * Get the next template id.
     *
     * @return int
     */
    protected function getNextTemplateId()
    {
        return $this->templateId++;
    }

    /**
     * Set the page format of the current page.
     *
     * @param array $size An array with two values defining the size.
     * @param string $orientation "L" for landscape, "P" for portrait.
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function setPageFormat($size, $orientation)
    {
        if ($this->currentTemplateId !== null) {
            throw new \BadMethodCallException('The page format cannot be changed when writing to a template.');
        }

        $orientation = Document::parseOrientation($orientation);
        $size = Document::parseSize($size, $orientation);

        $document = $this->manager->getModule(Document::class);
        $page = $document->get()->getCatalog()->getPages()->getPage($document->getActivePageNo());

        $box = \SetaPDF_Core_DataStructure_Rectangle::byArray([
            0,
            0,
            $this->manager->getConverter()->convert($size[0]),
            $this->manager->getConverter()->convert($size[1])
        ]);

        $page->setMediaBox($box);
        $page->setCropBox($box);
    }

    /**
     * Draws a template onto the page or another template.
     *
     * Omit one of the size parameters (width, height) to calculate the other one automatically in view to the aspect
     * ratio.
     *
     * @param mixed $tpl The template id
     * @param array|float|int $x The abscissa of upper-left corner. Alternatively you could use an assoc array
     *                           with the keys "x", "y", "width", "height", "adjustPageSize".
     * @param float|int $y The ordinate of upper-left corner.
     * @param float|int|null $width The width.
     * @param float|int|null $height The height.
     * @param bool $adjustPageSize
     * @return array The size
     * @see FpdfTpl::getTemplateSize()
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     */
    public function useTemplate($tpl, $x = 0, $y = 0, $width = null, $height = null, $adjustPageSize = false)
    {
        if (!isset($this->templates[$tpl])) {
            throw new \InvalidArgumentException('Template does not exist!');
        }

        if (\is_array($x)) {
            unset($x['tpl']);
            \extract($x, EXTR_IF_EXISTS);
            /** @noinspection NotOptimalIfConditionsInspection */
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            if (\is_array($x)) {
                $x = 0;
            }
        }

        $newSize = $this->getTemplateSize($tpl, $width, $height);
        if ($adjustPageSize) {
            $this->setPageFormat($newSize, $newSize['orientation']);
        }

        $canvas = $this->manager->getCanvas();

        $newWidth = $this->manager->getConverter()->convert($newSize['width']);
        $newHeight = $this->manager->getConverter()->convert($newSize['height']);

        $this->templates[$tpl]->draw(
            $canvas,
            $this->manager->getConverter()->convert($x),
            $canvas->getHeight() - $this->manager->getConverter()->convert($y) - $newHeight,
            $newWidth,
            $newHeight
        );

        return $newSize;
    }

    /**
     * Get the size of a template.
     *
     * Omit one of the size parameters (width, height) to calculate the other one automatically in view to the aspect
     * ratio.
     *
     * @param mixed $tpl The template id
     * @param float|int|null $width The width.
     * @param float|int|null $height The height.
     * @return array|bool An array with following keys: width, height, 0 (=width), 1 (=height), orientation (L or P)
     * @throws \InvalidArgumentException
     */
    public function getTemplateSize($tpl, $width = null, $height = null)
    {
        if (!isset($this->templates[$tpl])) {
            return false;
        }

        $converter = $this->manager->getConverter();

        if ($width === null && $height === null) {
            $width = $converter->revert($this->templates[$tpl]->getWidth());
            $height = $converter->revert($this->templates[$tpl]->getHeight());
        } elseif ($width === null) {
            $width = $this->templates[$tpl]->getWidth($height);
        } elseif ($height === null) {
            $height = $this->templates[$tpl]->getHeight($width);
        }

        if ($height <= 0. || $width <= 0.) {
            throw new \InvalidArgumentException('Width and height parameter needs to be larger than zero.');
        }

        return [
            'width' => $width,
            'height' => $height,
            0 => $width,
            1 => $height,
            'orientation' => $width > $height ? 'L' : 'P'
        ];
    }

    /**
     * Begins a new template.
     *
     * @param float|int|null $width The width of the template. If null, the current page width is used.
     * @param float|int|null $height The height of the template. If null, the current page height is used.
     * @return int A template identifier.
     */
    public function beginTemplate($width = null, $height = null)
    {
        $document = $this->manager->getModule(Document::class);
        $converter = $this->manager->getConverter();

        if ($width === null) {
            $width = $converter->revert($document->getDefaultWidth());
        }

        if ($height === null) {
            $height = $converter->revert($document->getDefaultHeight());
        }

        $this->currentTemplateId = $this->getNextTemplateId();

        $this->templates[$this->currentTemplateId] = \SetaPDF_Core_XObject_Form::create(
            $document->get(), [0, 0, $converter->convert($width), $converter->convert($height)]
        );

        $this->manager->save();
        $document->setAutoPageBreak(false);
        $this->manager->setCanvas($this->templates[$this->currentTemplateId]->getCanvas());

        return $this->currentTemplateId;
    }

    /**
     * Ends a template.
     *
     * @return bool|int|null A template identifier.
     */
    public function endTemplate()
    {
        if ($this->currentTemplateId === null) {
            return false;
        }


        $document = $this->manager->getModule(Document::class);
        try {
            $document->setActivePage($document->getActivePageNo());
        } catch (\BadMethodCallException $e) {
        }

        $this->manager->restore();

        $templateId = $this->currentTemplateId;
        $this->currentTemplateId = null;

        return $templateId;
    }

    /* overwritten FPDF methods: */

    /**
     * @inheritdoc
     * @throws \BadMethodCallException
     */
    public function AddPage($orientation = '', $size = '', $rotation = 0)
    {
        if ($this->currentTemplateId !== null) {
            throw new \BadMethodCallException('Pages cannot be added when writing to a template.');
        }

        parent::AddPage($orientation, $size, $rotation);
    }

    /**
     * @inheritdoc
     * @throws \BadMethodCallException
     */
    public function SetPage($pageNo = null)
    {
        if ($this->currentTemplateId !== null) {
            throw new \BadMethodCallException('Cannot change the active page while writing to a template.');
        }

        parent::SetPage($pageNo);
    }

    /**
     * @inheritdoc
     * @throws \BadMethodCallException
     */
    public function Link($x, $y, $w, $h, $link)
    {
        if ($this->currentTemplateId !== null) {
            throw new \BadMethodCallException('Links cannot be set when writing to a template.');
        }

        parent::Link($x, $y, $w, $h, $link);
    }
}
