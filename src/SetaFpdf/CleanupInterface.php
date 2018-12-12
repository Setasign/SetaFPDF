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

namespace setasign\SetaFpdf;

interface CleanupInterface
{
    /**
     * Release memory and remove cycled references.
     */
    public function cleanUp();
}