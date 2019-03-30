<?php

require_once __DIR__ . '/vendor/autoload.php';

$envFlags = new \Crunz\EnvFlags\EnvFlags();
$envFlags->disableDeprecationHandler();

$filesystem = new \Crunz\Filesystem\Filesystem();

if (\strpos($filesystem->getCwd(), 'tests') !== false) {
    return;
}

if (!\chdir('tests')) {
    throw new RuntimeException("Unable to change current directory to 'tests'.");
}
