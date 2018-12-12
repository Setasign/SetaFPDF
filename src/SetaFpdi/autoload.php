<?php

/**
 * This is part of SetaFpdi
 *
 * @package   setasign\SetaFpdi
 * @copyright Copyright (c) 2018 Setasign - Jan Slabon (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 */
spl_autoload_register(function ($class) {
    if (strpos($class, 'setasign\SetaFpdi\\') === 0) {
        $filename = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 14)) . '.php';
        $fullpath = __DIR__ . DIRECTORY_SEPARATOR . $filename;
        if (file_exists($fullpath)) {
            /** @noinspection PhpIncludeInspection */
            require_once $fullpath;
        }
    }
});