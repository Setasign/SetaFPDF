<?php

namespace setasign\tests\SetaFpdi\visual\SetaFpdfTpl\One;

/**
 * Trait MethodTrait
 * @package setasign\tests\SetaFpdi\visual\SetaFpdfTpl\One
 *
 * @mixin \FPDF
 */
trait MethodTrait
{

    function Header()
    {
        $title = 'Demo document!';

        $this->SetFont('Arial','B',15);
        $w = $this->GetStringWidth($title)+6;
        $this->SetX((210-$w)/2);
        $this->SetDrawColor(0,80,180);
        $this->SetFillColor(230,230,0);
        $this->SetTextColor(220,50,50);
        $this->SetLineWidth(1);
        $this->Cell($w,9,$title,1,1,'C',true);
        $this->Ln(10);
    }

    function Footer()
    {
        // Page footer
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->SetTextColor(128);
        $this->Cell(0,10,'Page '.$this->PageNo(),0,0,'C');
    }
}