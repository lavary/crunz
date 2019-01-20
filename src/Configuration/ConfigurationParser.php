<?php

namespace Crunz\Configuration;

use Crunz\Filesystem\FilesystemInterface;
use Crunz\Logger\ConsoleLoggerInterface;
use Crunz\Path\Path;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationParser implements ConfigurationParserInterface
{
    /** @var ConfigurationInterface */
    private $configurationDefinition;
    /** @var Processor */
    private $definitionProcessor;
    /** @var ConsoleLoggerInterface */
    private $consoleLogger;
    /** @var FileParser */
    private $fileParser;
    /** @var FilesystemInterface */
    private $filesystem;

    public function __construct(
        ConfigurationInterface $configurationDefinition,
        Processor $definitionProcessor,
        FileParser $fileParser,
        ConsoleLoggerInterface $consoleLogger,
        FilesystemInterface $filesystem
    ) {
        $this->consoleLogger = $consoleLogger;
        $this->configurationDefinition = $configurationDefinition;
        $this->definitionProcessor = $definitionProcessor;
        $this->fileParser = $fileParser;
        $this->filesystem = $filesystem;
    }

    /** @return array */
    public function parseConfig()
    {
        $parsedConfig = [];
        $configFileParsed = false;

        try {
            $configFile = $this->configFilePath();
            $parsedConfig = $this->fileParser
                ->parse($configFile);

            $configFileParsed = true;
        } catch (ConfigFileNotExistsException $exception) {
            $this->consoleLogger
                ->debug("Config file not found, exception message: '<error>{$exception->getMessage()}</error>'.");
        } catch (ConfigFileNotReadableException $exception) {
            $this->consoleLogger
                ->debug("Config file is not readable, exception message: '<error>{$exception->getMessage()}</error>'.");
        }

        if (false === $configFileParsed) {
            $this->consoleLogger
                ->verbose('Unable to find/parse config file, fallback to default values.');
        } else {
            $this->consoleLogger
                ->verbose("Using config file <info>{$configFile}</info>.");
        }

        return $this->definitionProcessor
            ->processConfiguration(
                $this->configurationDefinition,
                $parsedConfig
            );
    }

    /**
     * @return string
     *
     * @throws ConfigFileNotExistsException
     */
    private function configFilePath()
    {
        $cwd = $this->filesystem
            ->getCwd();
        $configPath = \implode(
            DIRECTORY_SEPARATOR,
            [$cwd, 'crunz.yml']
        );
        $configExists = $this->filesystem
            ->fileExists($configPath);

        if ($configExists) {
            return $configPath;
        }

        $this->consoleLogger
            ->debug('Trying to resolve legacy config file:');

        $legacyConfigPaths = $this->legacyConfigPaths();

        foreach ($legacyConfigPaths as $legacyConfigPath) {
            $this->consoleLogger
                ->debug("- testing path <info>'{$legacyConfigPath}'</info>.");

            $legacyConfigExists = $this->filesystem
                ->fileExists($legacyConfigPath);

            if ($legacyConfigExists) {
                $this->consoleLogger
                    ->debug("Legacy config found at <info>'{$legacyConfigPath}'</info>.");

                @\trigger_error(
                    "Probably you are relying on legacy config file recognition which is deprecated. Currently default config file is relative to current working directory, make sure you changed it accordingly in your Cron task. Run your command with '-vvv' to check resolved paths. Legacy behavior will be removed in v2.",
                    E_USER_DEPRECATED
                );

                return $legacyConfigPath;
            }
        }

        $this->consoleLogger
            ->debug('Legacy config not found.');

        throw new ConfigFileNotExistsException(
            \sprintf(
                'Unable to find config file "%s".',
                $configPath
            )
        );
    }

    /**
     * Method is used only for provide legacy config paths.
     *
     * @deprecated Since v1.11
     *
     * @todo Remove in v2
     *
     * @return string[]
     */
    private function legacyConfigPaths()
    {
        $vendorBin = Path::create(
            [
                '..',
                '..',
                'crunz.yml',
            ]
        );
        $vendorCrunzBin = Path::create(
            [
                '..',
                '..',
                '..',
                'crunz.yml',
            ]
        );
        $paths = [
            $vendorBin->toString(),
            $vendorCrunzBin->toString(),
        ];

        return \array_map(
            function ($relativePart) {
                return Path::create([CRUNZ_BIN_DIR, $relativePart])->toString();
            },
            $paths
        );
    }
}
