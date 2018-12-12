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

class Link
{
    /**
     * The manager.
     *
     * @var Manager
     */
    protected $manager;

    /**
     * An array containing all the link locations.
     *
     * @var array
     */
    protected $links = [];

    /**
     * An array containing all the link targets.
     *
     * @var array
     */
    protected $linkTargets = [];

    /**
     * Link constructor.
     *
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Implementation of the FPDF::AddLink() method.
     *
     * @return int
     */
    public function addLink()
    {
        $link = \count($this->linkTargets) + 1;

        $this->linkTargets[$link] = null;

        return $link;
    }

    /**
     * Implementation of the FPDF::SetLink() method.
     *
     * @param int|string $link
     * @param int|float $y
     * @param int|float $page
     */
    public function setLink($link, $y, $page)
    {
        $this->linkTargets[$link] = [$y, $page];
    }

    /**
     * Implementation of the FPDF::Link() method.
     *
     * @param int|float $x
     * @param int|float $y
     * @param int|float $w
     * @param int|float $h
     * @param int|string $link
     * @param bool $scaled
     * @throws \BadMethodCallException
     */
    public function link($x, $y, $w, $h, $link, $scaled = false)
    {
        /** @noinspection NotOptimalIfConditionsInspection */
        if (!isset($this->linkTargets[$link]) && \is_string($link)) {
            $this->linkTargets[$link] = $link;
        }

        if (!$scaled) {
            $x = $this->manager->getConverter()->convertX($x);
            $y = $this->manager->getConverter()->convertY($y);
            $w = $this->manager->getConverter()->convert($w);
            $h = $this->manager->getConverter()->convert($h);
        }

        $this->links[] = [
            'page' => $this->manager->getModule(Document::class)->getActivePageNo(),
            'rectangle' => [$x, $y, $x + $w, $y - $h],
            'target' => $link
        ];
    }

    /**
     * Writes all the links into the document.
     *
     * @param \SetaPDF_Core_Document $document
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function writeLinks(\SetaPDF_Core_Document $document)
    {
        $catalog = $document->getCatalog();
        $pages = $catalog->getPages();

        $targetCache = [];

        foreach ($this->links as $link) {
            if (!isset($this->linkTargets[$link['target']])) {
                throw new \RuntimeException('Link target does not exists.');
            }

            $page = $pages->getPage($link['page']);

            if (!isset($targetCache[$link['target']])) {
                $targetCache[$link['target']] = $this->createTarget(
                    $document,
                    $page->getHeight(),
                    $this->linkTargets[$link['target']]
                );
            }

            $page->getAnnotations()->add(
                new \SetaPDF_Core_Document_Page_Annotation_Link(
                    \SetaPDF_Core_Document_Page_Annotation_Link::createAnnotationDictionary(
                        \SetaPDF_Core_DataStructure_Rectangle::create($link['rectangle']),
                        $targetCache[$link['target']]
                    )
                )
            );
        }
    }

    /**
     * Creates a link target.
     *
     * @param \SetaPDF_Core_Document $document
     * @param float $height
     * @param array|string $target
     * @return \SetaPDF_Core_Document_Destination|\SetaPDF_Core_Document_Action
     * @throws \InvalidArgumentException
     */
    private function createTarget(\SetaPDF_Core_Document $document, $height, $target)
    {
        if (\is_array($target)) {
            $page = $document->getCatalog()->getPages()->getPage($target[1]);

            return \SetaPDF_Core_Document_Destination::createByPage(
                $page,
                \SetaPDF_Core_Document_Destination::FIT_MODE_XYZ,
                0,
                $height - $this->manager->getConverter()->convert($target[0]),
                null
            );
        }

        if (\is_string($target)) {
            return new \SetaPDF_Core_Document_Action_Uri($target);
        }

        throw new \InvalidArgumentException('Unknown link target.');
    }
}
