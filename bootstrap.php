<?php

require_once __DIR__ . '/vendor/autoload.php';

// Disable deprecation helper
$envFlags = new \Crunz\EnvFlags\EnvFlags();
$envFlags->disableDeprecationHandler();

// Make sure current working directory is "tests"
$filesystem = new \Crunz\Filesystem\Filesystem();
if (\strpos($filesystem->getCwd(), 'tests') !== false) {
    return;
}

if (!\chdir('tests')) {
    throw new RuntimeException("Unable to change current directory to 'tests'.");
}

// Money patch PHPUnit_Framework_MockObject_Generator to avoid
// "Function ReflectionType::__toString() is deprecated" warnings
$mockObjectVersion = PHP_VERSION_ID >= 70100
    ? '71'
    : 'org'
;
$mockObjectFilePath = __DIR__ . "/tests/resources/patches/php-mock-object-{$mockObjectVersion}.php";

if (PHP_VERSION_ID >= 70100) {
    \file_put_contents(
        __DIR__ . '/vendor-bin/phpunit/vendor/phpunit/phpunit-mock-objects/src/Framework/MockObject/Generator.php',
        \file_get_contents($mockObjectFilePath)
    );
}
