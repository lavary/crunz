#!/usr/bin/env php
<?php

$version = $_SERVER['argv'][1] ?? '';
if ('' === $version) {
    throw new RuntimeException('Version cannot be empty.');
}

$dependenciesEnv = $_SERVER['argv'][2] ?? '';
$defaultComposerFlags = $_SERVER['argv'][3] ?? '';
$phpunitBridgeVersion = $_SERVER['argv'][4] ?? $version;

$ignoredPackages = [
    'symfony/error-handler',
    'symfony/phpunit-bridge',
];
$composerJson = \json_decode(
    \file_get_contents(
        __DIR__ . DIRECTORY_SEPARATOR . 'composer.json'
    ),
    true
);
$packages = \array_merge(
    $composerJson['require'] ?? [],
    $composerJson['require-dev'] ?? []
);
$symfonyPackages = [];

foreach ($packages as $packageName => $packageVersion) {
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

    $symfonyPackages[] = $packageName;
}

$withVersion = \array_map(
    static function (string $packageName) use ($version): string {
        return "{$packageName}:{$version}";
    },
    $symfonyPackages
);
$withVersion[] = "symfony/phpunit-bridge:{$phpunitBridgeVersion}";

$symfonyPackagesVersions = '"' . \implode('" "', $withVersion) . '"';
$dependencies = 'high' === $dependenciesEnv
    ? ''
    : '--prefer-lowest'
;

$command = "composer req -o {$symfonyPackagesVersions} {$dependencies} {$defaultComposerFlags}";

echo $command . PHP_EOL;
echo \shell_exec($command);
