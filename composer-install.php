#!/usr/bin/env php
<?php

$version = $_SERVER['argv'][1] ?? '';
if ('' === $version) {
    throw new RuntimeException('Version cannot be empty.');
}

$dependenciesEnv = $_SERVER['argv'][2] ?? '';
$defaultComposerFlags = $_SERVER['argv'][3] ?? '';
$composerFilePath = __DIR__ . DIRECTORY_SEPARATOR . 'composer.json';
$ignoredPackages = ['symfony/error-handler'];
$changeVersion = static function (
    array $packages
) use (
    $version,
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

        if (false === \mb_strpos($packageName, 'symfony/')) {
            continue;
        }

        if ('symfony/filesystem' === $packageName && '~v4.4.30' === $version) {
            $packageVersion = '~v4.4.27';

            continue;
        }

        if ('symfony/mailer' === $packageName && '~v4.4.30' === $version) {
            $packageVersion = '~v4.4.27';

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

$preferLowest = '';
if ('high' !== $dependenciesEnv) {
    $preferLowest = '--prefer-lowest';
}

$command = \trim("composer update -o {$defaultComposerFlags} {$preferLowest}");
echo $command, PHP_EOL;
echo \shell_exec($command);
