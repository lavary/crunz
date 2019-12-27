<?php

declare(strict_types=1);

namespace Crunz\Console\Command;

use Crunz\Filesystem\FilesystemInterface;
use Crunz\Path\Path;
use Crunz\Timezone\ProviderInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

final class ConfigGeneratorCommand extends Command
{
    public const CONFIG_FILE_NAME = 'crunz.yml';

    /** @var ProviderInterface */
    private $timezoneProvider;
    /** @var Filesystem */
    private $symfonyFilesystem;
    /** @var FilesystemInterface */
    private $filesystem;

    public function __construct(
        ProviderInterface $timezoneProvider,
        Filesystem $symfonyFilesystem,
        FilesystemInterface $filesystem
    ) {
        $this->timezoneProvider = $timezoneProvider;
        $this->symfonyFilesystem = $symfonyFilesystem;
        $this->filesystem = $filesystem;

        parent::__construct();
    }

    /**
     * Configures the current command.
     */
    protected function configure(): void
    {
        $this
            ->setName('publish:config')
            ->setDescription("Generates a config file within the project's root directory.")
            ->setHelp("This generates a config file in YML format within the project's root directory.")
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $symfonyStyleIo = new SymfonyStyle($input, $output);
        $cwd = $this->filesystem
            ->getCwd();
        $path = Path::create([$cwd, self::CONFIG_FILE_NAME])->toString();
        $destination = \realpath($path) ?: $path;
        $configExists = $this->filesystem
            ->fileExists($destination)
        ;

        $output->writeln(
            "<info>Destination config file: '{$destination}'.</info>",
            OutputInterface::VERBOSITY_VERBOSE
        );

        if ($configExists) {
            $output->writeln(
                "<comment>The configuration file already exists at '{$destination}'.</comment>"
            );

            return 0;
        }

        $projectRoot = $this->filesystem
            ->projectRootDirectory();
        $srcPath = Path::fromStrings(
            $projectRoot,
            'resources',
            'config',
            self::CONFIG_FILE_NAME
        );
        $src = $srcPath->toString();
        $output->writeln(
            "<info>Source config file: '{$src}'.</info>",
            OutputInterface::VERBOSITY_VERBOSE
        );
        $defaultTimezone = $this->askForTimezone($symfonyStyleIo);
        $output->writeln(
            "<info>Provided timezone: '{$defaultTimezone}'.</info>",
            OutputInterface::VERBOSITY_VERBOSE
        );

        $this->updateTimezone(
            $destination,
            $src,
            $defaultTimezone
        );

        $output->writeln('<info>The configuration file was generated successfully.</info>');

        return 0;
    }

    /**
     * @return string
     */
    protected function askForTimezone(SymfonyStyle $symfonyStyleIo)
    {
        $defaultTimezone = $this->timezoneProvider
            ->defaultTimezone()
            ->getName()
        ;
        $question = new Question(
            '<question>Please provide default timezone for task run date calculations</question>',
            $defaultTimezone
        );
        $question->setAutocompleterValues(\DateTimeZone::listIdentifiers());
        $question->setValidator(
            static function ($answer) {
                try {
                    new \DateTimeZone($answer);
                } catch (\Exception $exception) {
                    throw new \Exception("Timezone '{$answer}' is not valid. Please provide valid timezone.");
                }

                return $answer;
            }
        );

        return $symfonyStyleIo->askQuestion($question);
    }

    private function updateTimezone(
        string $destination,
        string $src,
        string $timezone
    ): void {
        $this->symfonyFilesystem
            ->dumpFile(
                $destination,
                \str_replace(
                    'timezone: ~',
                    "timezone: {$timezone}",
                    $this->filesystem
                        ->readContent($src)
                )
            )
        ;
    }
}
