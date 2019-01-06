<?php

$root = \dirname(\dirname(__DIR__));
$autoloaderPath = \implode(
    DIRECTORY_SEPARATOR,
    [
        $root,
        'vendor',
        'autoload.php',
    ]
);

require_once $autoloaderPath;
