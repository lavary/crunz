<?php

require_once __DIR__ . '/vendor/autoload.php';

if (\strpos(\getcwd(), 'tests') !== false) {
    return;
}

if (!\chdir('tests')) {
    throw new RuntimeException("Unable to change currenjt directory to 'tests'.");
}
