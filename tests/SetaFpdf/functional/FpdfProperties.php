<?php

namespace setasign\tests\SetaFpdf\functional;

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
            case 'wPt':
                return $this->wPt;
            case 'hPt':
                return $this->hPt;
            case 'w':
                return $this->GetPageWidth();
            case 'h':
                return $this->GetPageHeight();
            case 'rMargin':
                return $this->rMargin;
            case 'cMargin':
                return $this->cMargin;
            case 'lMargin':
                return $this->lMargin;
            case 'tMargin':
                return $this->tMargin;
            case 'bMargin':
                return $this->bMargin;
            case 'PageBreakTrigger':
                return $this->PageBreakTrigger;

            default:
                throw new \InvalidArgumentException(sprintf('Property "%s" cannot be accessed.', $name));
        }
    }
}