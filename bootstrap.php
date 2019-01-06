<?php

require_once __DIR__ . '/vendor/autoload.php';

$envFlags = new \Crunz\EnvFlags\EnvFlags();
$envFlags->disableDeprecationHandler();

if (\strpos(\getcwd(), 'tests') !== false) {
    return;
}

if (!\chdir('tests')) {
    throw new RuntimeException("Unable to change current directory to 'tests'.");
}
