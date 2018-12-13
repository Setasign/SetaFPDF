<?php
/**
 * This file is part of SetaFPDI
 *
 * @package   setasign\SetaFpdi
 * @copyright Copyright (c) 2018 Setasign - Jan Slabon (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 */

namespace setasign\SetaFpdf;

use setasign\SetaFpdf\Modules\Document;

class SetaFpdi extends SetaFpdfTpl
{
    /**
     * The imported pages stored as XObjects.
     *
     * @var \SetaPDF_Core_XObject_Form[]
     */
    protected $importedPages = [];

    /**
     * The document instances for all the opened documents.
     *
     * @var \SetaPDF_Core_Document[]
     */
    protected $documents = [];

    /**
     * The currently active reader id.
     *
     * @var null|string
     */
    protected $currentReaderId;

    /**
     * The currently active document.
     *
     * @var null|\SetaPDF_Core_Document
     */
    protected $currentDocument;

    /**
     * Get an unique reader id by the $file parameter.
     *
     * @param string|resource|\SetaPDF_Core_Reader_File $file
     * @return string
     * @throws \Exception
     * @throws \InvalidArgumentException
     * @throws \SetaPDF_Core_Parser_CrossReferenceTable_Exception
     */
    protected function getPdfReaderId($file)
    {
        if (\is_resource($file)) {
            $id = (string) $file;
        } elseif (\is_string($file)) {
            $id = realpath($file);
            if ($id === false) {
                $id = $file;
            }
        } elseif (is_object($file)) {
            $id = spl_object_hash($file);
        } else {
            throw new \InvalidArgumentException(
                \sprintf('Invalid type in $file parameter (%s)', \gettype($file))
            );
        }

        /** @noinspection OffsetOperationsInspection */
        if (isset($this->documents[$id])) {
            return $id;
        }

        if (\is_resource($file)) {
            $reader = new \SetaPDF_Core_Reader_Stream($file);
        } elseif (\is_string($file)) {
            $reader = new \SetaPDF_Core_Reader_File($file);
        } else {
            $reader = $file;
        }

        /** @noinspection OffsetOperationsInspection */
        $this->documents[$id] = \SetaPDF_Core_Document::load($reader);

        return $id;
    }

    /**
     * Set the source PDF file.
     *
     * @param string|resource|\SetaPDF_Core_Reader_Stream $file Path to the file or a stream resource or
     *                                                          a StreamReader instance.
     * @return int The page count of the PDF document.
     * @throws \InvalidArgumentException
     * @throws \Exception
     * @throws \SetaPDF_Core_Parser_CrossReferenceTable_Exception
     */
    public function setSourceFile($file)
    {
        $this->currentReaderId = $this->getPdfReaderId($file);
        $this->currentDocument = $this->documents[$this->currentReaderId];

        return $this->currentDocument->getCatalog()->getPages()->count();
    }

    /**
     * Imports a page.
     *
     * @param int $pageNumber The page number.
     * @param string $box The page boundary to import. Default set to PageBoundaries::CROP_BOX.
     * @param bool $groupXObject Define the form XObject as a group XObject to support transparency (if used).
     * @return string A unique string identifying the imported page.
     * @see PageBoundaries
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function importPage($pageNumber, $box = \SetaPDF_Core_PageBoundaries::CROP_BOX, $groupXObject = true)
    {
        if ($this->currentDocument === null) {
            throw new \BadMethodCallException('No reader initiated. Call setSourceFile() first.');
        }

        if (!\SetaPDF_Core_PageBoundaries::isValidName($box)) {
            throw new \InvalidArgumentException(
                \sprintf('Box name is invalid: "%s"', $box)
            );
        }

        $pageId = $this->currentReaderId . '|' . $pageNumber . '|' . ($groupXObject ? '1' : '0') . '|' . $box;
        if (isset($this->importedPages[$pageId])) {
            return $pageId;
        }

        // used to increase the template id.
        $this->getNextTemplateId();

        $this->importedPages[$pageId] = $this->currentDocument
            ->getCatalog()
            ->getPages()
            ->getPage($pageNumber)
            ->toXObject($this->manager->getDocument()->get(), $box);

        if ($groupXObject) {
            $this->importedPages[$pageId]->setGroup(new \SetaPDF_Core_TransparencyGroup());
        }

        return $pageId;
    }

    /**
     * Draws an imported page onto the page.
     *
     * Omit one of the size parameters (width, height) to calculate the other one automatically in view to the aspect
     * ratio.
     *
     * @param mixed $pageId The page id
     * @param float|int|array $x The abscissa of upper-left corner. Alternatively you could use an assoc array
     *                           with the keys "x", "y", "width", "height", "adjustPageSize".
     * @param float|int $y The ordinate of upper-left corner.
     * @param float|int|null $width The width.
     * @param float|int|null $height The height.
     * @param bool $adjustPageSize
     * @return array The size.
     * @see SetaFpdf::getTemplateSize()
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function useImportedPage($pageId, $x = 0, $y = 0, $width = null, $height = null, $adjustPageSize = false)
    {
        if (\is_array($x)) {
            unset($x['pageId']);
            \extract($x, EXTR_IF_EXISTS);
            /** @noinspection NotOptimalIfConditionsInspection */
            if (\is_array($x)) {
                $x = 0;
            }
        }

        if (!isset($this->importedPages[$pageId])) {
            throw new \InvalidArgumentException('Imported page does not exist!');
        }

        $newSize = $this->getTemplateSize($pageId, $width, $height);
        if ($adjustPageSize) {
            $this->setPageFormat($newSize, $newSize['orientation']);
        }

        $canvas = $this->manager->getCanvas();

        $height = $this->manager->getConverter()->toPt($newSize['height']);
        $width = $this->manager->getConverter()->toPt($newSize['width']);

        $this->importedPages[$pageId]->draw(
            $canvas,
            $this->manager->getConverter()->toPt($x),
            $canvas->getHeight() - $this->manager->getConverter()->toPt($y) - $height,
            $width,
            $height
        );

        return $newSize;
    }

    /**
     * Get the size of an imported page.
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
    public function getImportedPageSize($tpl, $width = null, $height = null)
    {
        if (isset($this->importedPages[$tpl])) {
            $importedPage = $this->importedPages[$tpl];

            $converter = $this->manager->getConverter();

            if ($width === null && $height === null) {
                $width = $converter->fromPt($importedPage->getWidth());
                $height = $converter->fromPt($importedPage->getHeight());
            } elseif ($width === null) {
                $width = $importedPage->getWidth($height);
            } elseif ($height  === null) {
                $height = $importedPage->getHeight($width);
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

        return false;
    }

    /**
     * @inheritdoc
     */
    public function useTemplate($tpl, $x = 0, $y = 0, $width = null, $height = null, $adjustPageSize = false)
    {
        if (isset($this->importedPages[$tpl])) {
            return $this->useImportedPage($tpl, $x, $y, $width, $height, $adjustPageSize);
        }

        return parent::useTemplate($tpl, $x, $y, $width, $height, $adjustPageSize);
    }

    /**
     * @inheritdoc
     */
    public function Close()
    {
        parent::Close();

        foreach ($this->documents as $document) {
            $document->finish();
        }
    }

    /**
     * @inheritdoc
     */
    public function getTemplateSize($tpl, $width = null, $height = null)
    {
        if (isset($this->importedPages[$tpl])) {
            return $this->getImportedPageSize($tpl, $width, $height);
        }

        return parent::getTemplateSize($tpl, $width, $height);
    }
}
