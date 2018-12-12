<?php

namespace setasign\tests\SetaFpdf\visual\Tutorial\Three;

use setasign\SetaFpdf\SetaFpdf;

class SetaFpdfCustom extends SetaFpdf
{
    use MethodTrait;

    public function getContent($file)
    {
        $str = file_get_contents($file);

        return mb_convert_encoding($str, 'UTF-8', 'cp1252');
    }
}