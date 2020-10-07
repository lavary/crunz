#!/usr/bin/env php
<?php

$version = $_SERVER['argv'][1] ?? '';
if ('' === $version) {
    throw new RuntimeException('Version cannot be empty.');
}

$dependenciesEnv = $_SERVER['argv'][2] ?? '';
$defaultComposerFlags = $_SERVER['argv'][3] ?? '';
$phpunitBridgeVersion = $_SERVER['argv'][4] ?? $version;
$composerFilePath = __DIR__ . DIRECTORY_SEPARATOR . 'composer.json';
$ignoredPackages = ['symfony/error-handler'];
$changeVersion = static function (
    array $packages
) use (
    $version,
    $phpunitBridgeVersion,
    $ignoredPackages
): array {
    foreach ($packages as $packageName => &$packageVersion) {
        $isIgnored = \in_array(
            $packageName,
            $ignoredPackages,
            true
        );

        if ($isIgnored) {
            continue;
        }

        if (PHP_MAJOR_VERSION >= 8 && 'phpunit/phpunit' === $packageName) {
            $packageVersion = '^9.4.0';

            continue;
        }

        if (false === \mb_strpos($packageName, 'symfony/')) {
            continue;
        }

        if ('symfony/phpunit-bridge' === $packageName) {
            $packageVersion = $phpunitBridgeVersion;

            continue;
        }

        $packageVersion = $version;
    }

    return $packages;
};

$composerJson = \json_decode(
    \file_get_contents($composerFilePath),
    true
);
$packages = $composerJson['require'] ?? [];
$packagesDev = $composerJson['require-dev'] ?? [];
$composerJson['require'] = $changeVersion($packages);
$composerJson['require-dev'] = $changeVersion($packagesDev);

\file_put_contents(
    $composerFilePath,
    \json_encode($composerJson, JSON_PRETTY_PRINT)
);

$command = "composer install -o {$defaultComposerFlags}";
echo $command, PHP_EOL;
echo \shell_exec($command);

if ('high' !== $dependenciesEnv) {
    $updateCommand = "composer update -o --prefer-lowest {$defaultComposerFlags}";
    echo $updateCommand, PHP_EOL;
    echo \shell_exec($updateCommand);
}
