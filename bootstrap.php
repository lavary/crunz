<?php

require_once __DIR__ . '/vendor/autoload.php';

$testCrunzRoot = \implode(
    DIRECTORY_SEPARATOR,
    [
        __DIR__,
        'tests'
    ]
);

\define('CRUNZ_ROOT', $testCrunzRoot);
\putenv('CRUNZ_BASE_DIR=' . $testCrunzRoot);
