<?php

namespace setasign\tests\functional\SetaFpdf\FooterTest;

/**
 * Trait MethodTrait
 *
 * @mixin \FPDF
 */
trait MethodTrait
{
    public $footerInformation = [];

    public function footer()
    {
        $this->footerInformation[$this->PageNo()] = [
            'x' => $this->GetX(),
            'y' => $this->GetY(),
        ];
    }
}