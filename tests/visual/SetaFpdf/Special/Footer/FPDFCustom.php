<?php

namespace setasign\tests\visual\SetaFpdf\Special\Footer;

class FPDFCustom extends \FPDF
{
    public function Footer()
    {
        $this->SetFont('arial');

        $this->SetFontSize(9);
        $this->SetY(-15);

        $this->Cell(0, 5, 'Invoice no A05142 - Page ' . $this->PageNo() . ' of {nb}', 0, 0, 'L');
    }

    public function Output($dest = '', $name = '', $isUTF8 = false)
    {
        $this->AliasNbPages();
        return parent::Output($dest, $name, $isUTF8);
    }
}