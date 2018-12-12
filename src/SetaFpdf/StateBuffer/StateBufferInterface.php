<?php
/**
 * This file is part of SetaFPDF
 *
 * @package   setasign\SetaFpdf
 * @copyright Copyright (c) 2018 Setasign - Jan Slabon (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 */

namespace setasign\SetaFpdf\StateBuffer;

use setasign\SetaFpdf\CleanupInterface;

interface StateBufferInterface extends CleanupInterface
{
    /**
     * Resets the state.
     */
    public function reset();

    /**
     * Stores the current state.
     */
    public function store();

    /**
     * Restores the last stored state.
     */
    public function restore();
}