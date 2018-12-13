<?php
/**
 * This file is part of SetaFPDF
 *
 * @package   setasign\SetaFpdf
 * @copyright Copyright (c) 2018 Setasign - Jan Slabon (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 */

namespace setasign\SetaFpdf\StateBuffer;

use setasign\SetaFpdf\Manager;

/**
 * Class Canvas
 *
 * @property int|float $lineWidth
 * @property int|float $lineCap
 * @method void ensureLineWidth()
 * @method void ensureLineCap()
 */
class Canvas extends StateBuffer
{
    /**
     * The manager.
     *
     * @var Manager
     */
    protected $manager;

    public function __construct(Manager $manager)
    {
        $this->manager = $manager;

        parent::__construct([
            'lineWidth' => [$this, 'passLineWidth'],
            'lineCap' => [$this, 'passLineCap']
        ]);

        $this->lineWidth = .567;
    }

    /**
     * Callback method for changes in $lineWidth between ensureLineWidth() calls.
     *
     * @param $value
     * @throws \BadMethodCallException
     */
    protected function passLineWidth($value)
    {
        $this->manager->getCanvas()->path()->setLineWidth($value);
    }

    /**
     * Callback method for changes in $lineCap between ensureLineCap() calls.
     *
     * @param $value
     * @throws \BadMethodCallException
     */
    protected function passLineCap($value)
    {
        $this->manager->getCanvas()->path()->setLineCap($value);
    }

    /**
     * @inheritdoc
     */
    public function cleanUp()
    {
        $this->manager = null;
    }
}
