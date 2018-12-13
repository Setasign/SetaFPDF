<?php

namespace setasign\tests\visual\SetaFpdf\Special\Footer;

use setasign\SetaFpdf\SetaFpdf;

class SetaFpdfCustom extends SetaFpdf
{
    public function Output($dest = '', $name = '', $utf8 = false)
    {
        $this->SetFont('arial');
        $this->SetAutoPageBreak(false);

        $pageCount = $this->PageNo();
        for ($i = 1; $i <= $pageCount; $i++) {
            $this->SetPage($i);

            $this->SetFontSize(9);
            $this->SetY(-15);
            $this->Cell(0, 5, 'Invoice no A05142 - Page ' . $i . ' of ' . $pageCount, 0, 0, 'L');
        }

        return parent::Output($dest, $name, $utf8);
    }
}