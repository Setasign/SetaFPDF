<?php
/**
 * This is part of SetaFpdf
 *
 * @package   setasign\SetaFpdf
 * @copyright Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 */

/**
 * This file is only a fallback if you aren't using the composer autoloader
 */
spl_autoload_register(function ($class) {
    if (strpos($class, 'setasign\\SetaFpdf\\') === 0) {
        $filename = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 17)) . '.php';
        $fullpath = __DIR__ . DIRECTORY_SEPARATOR . $filename;

        if (is_file($fullpath)) {
            /** @noinspection PhpIncludeInspection */
            require_once $fullpath;
        }
    }
});
