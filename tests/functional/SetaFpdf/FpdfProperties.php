<?php

namespace setasign\tests\functional\SetaFpdf;

class FpdfProperties extends \FPDF
{
    public function __get($name)
    {
        switch ($name) {
            case 'page':
                return $this->PageNo();
            case 'pageCount':
                return count($this->pages);
            case 'x':
                return $this->GetX();
            case 'y':
                return $this->GetY();
            case 'w':
                return $this->GetPageWidth();
            case 'h':
                return $this->GetPageHeight();
            case 'lMargin':
                return $this->lMargin;
            case 'tMargin':
                return $this->tMargin;
            case 'rMargin':
                return $this->rMargin;
            case 'bMargin':
                return $this->bMargin;
            case 'pageBreakTrigger':
            case 'PageBreakTrigger':
                return $this->PageBreakTrigger;

            default:
                throw new \InvalidArgumentException(sprintf('Property "%s" cannot be accessed.', $name));
        }
    }
}