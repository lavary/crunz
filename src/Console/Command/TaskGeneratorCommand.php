<?php

declare(strict_types=1);

namespace Crunz\Console\Command;

use Crunz\Application\Service\ConfigurationInterface;
use Crunz\Filesystem\FilesystemInterface;
use Crunz\Path\Path;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class TaskGeneratorCommand extends Command
{
    /**
     * Default option values.
     *
     * @var array<string,string>
     */
    const DEFAULTS = [
        'frequency' => 'everyThirtyMinutes',
        'constraint' => 'weekdays',
        'in' => 'path/to/your/command',
        'run' => 'command/to/execute',
        'description' => 'Task description',
        'type' => 'basic',
    ];
    /**
     * Stub content.
     *
     * @var string
     */
    protected $stub;
    /** @var ConfigurationInterface */
    private $config;
    /** @var FilesystemInterface */
    private $filesystem;

    public function __construct(ConfigurationInterface $configuration, FilesystemInterface $filesystem)
    {
        $this->config = $configuration;
        $this->filesystem = $filesystem;

        parent::__construct();
    }

    /**
     * Configures the current command.
     */
    protected function configure(): void
    {
        $this
            ->setName('make:task')
            ->setDescription('Generates a task file with one task.')
            ->setDefinition(
                [
                    new InputArgument(
                        'taskfile',
                        InputArgument::REQUIRED,
                        'The task file name'
                    ),
                    new InputOption(
                        'frequency',
                        'f',
                        InputOption::VALUE_OPTIONAL,
                        "The task's frequency",
                        self::DEFAULTS['frequency']
                    ),
                    new InputOption(
                        'constraint',
                        'c',
                        InputOption::VALUE_OPTIONAL,
                        "The task's constraint",
                        self::DEFAULTS['constraint']
                    ),
                    new InputOption(
                        'in',
                        'i',
                        InputOption::VALUE_OPTIONAL,
                        "The command's path",
                        self::DEFAULTS['in']
                    ),
                    new InputOption(
                        'run',
                        'r',
                        InputOption::VALUE_OPTIONAL,
                        "The task's command",
                        self::DEFAULTS['run']
                    ),
                    new InputOption(
                        'description',
                        'd',
                        InputOption::VALUE_OPTIONAL,
                        "The task's description",
                        self::DEFAULTS['description']
                    ),
                    new InputOption(
                        'type',
                        't',
                        InputOption::VALUE_OPTIONAL,
                        'The task type',
                        self::DEFAULTS['type']
                    ),
                ]
            )
            ->setHelp('This command makes a task file skeleton.');
    }

    /**
     * Executes the current command.
     *
     * @return int|null null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $this->arguments = $input->getArguments();
        $this->options = $input->getOptions();
        $this->stub = $this->getStub();

        if ($this->stub) {
            $this
                ->replaceFrequency()
                ->replaceConstraint()
                ->replaceCommand()
                ->replacePath()
                ->replaceDescription()
            ;
        }

        if ($this->save()) {
            $output->writeln('<info>The task file generated successfully</info>');
        } else {
            $output->writeln('<comment>There was a problem when generating the file. Please check your command.</comment>');
        }

        return 0;
    }

    /**
     * Save the generate task skeleton into a file.
     *
     * @return bool
     */
    protected function save()
    {
        $filename = Path::create([$this->outputPath(), $this->outputFile()]);

        return (bool) \file_put_contents($filename->toString(), $this->stub);
    }

    /**
     * Ask a question.
     *
     * @param string $question
     *
     * @return ?string
     */
    protected function ask($question)
    {
        $helper = $this->getHelper('question');
        $question = new Question("<question>{$question}</question>");

        return $helper->ask($this->input, $this->output, $question);
    }

    /**
     * Return the output path.
     *
     * @return string
     */
    protected function outputPath()
    {
        $source = $this->config
            ->getSourcePath()
        ;
        $destination = $this->ask('Where do you want to save the file? (Press enter for the current directory)');
        $outputPath = null !== $destination ? $destination : $source;

        if (!\file_exists($outputPath)) {
            \mkdir($outputPath, 0744, true);
        }

        return $outputPath;
    }

    /**
     * Populate the output filename.
     *
     * @return string
     */
    protected function outputFile()
    {
        $suffix = $this->config
            ->get('suffix')
        ;

        return \preg_replace('/Tasks|\.php$/', '', $this->arguments['taskfile']) . $suffix;
    }

    /**
     * Get the task stub.
     *
     * @return string
     */
    protected function getStub()
    {
        $projectRootDirectory = $this->filesystem
            ->projectRootDirectory();
        $path = Path::fromStrings(
            $projectRootDirectory,
            'src',
            'Stubs',
            \ucfirst($this->type() . 'Task.php')
        );

        return $this->filesystem
            ->readContent($path->toString());
    }

    /**
     * Get the task type.
     *
     * @return string
     */
    protected function type()
    {
        return $this->options['type'];
    }

    /**
     * Replace frequency.
     */
    protected function replaceFrequency(): self
    {
        $this->stub = \str_replace('DummyFrequency', \rtrim($this->options['frequency'], '()'), $this->stub);

        return $this;
    }

    /**
     * Replace constraint.
     */
    protected function replaceConstraint(): self
    {
        $this->stub = \str_replace('DummyConstraint', \rtrim($this->options['constraint'], '()'), $this->stub);

        return $this;
    }

    /**
     * Replace command.
     */
    protected function replaceCommand(): self
    {
        $this->stub = \str_replace('DummyCommand', $this->options['run'], $this->stub);

        return $this;
    }

    /**
     * Replace path.
     */
    protected function replacePath(): self
    {
        $this->stub = \str_replace('DummyPath', $this->options['in'], $this->stub);

        return $this;
    }

    /**
     * Replace description.
     */
    protected function replaceDescription(): self
    {
        $this->stub = \str_replace('DummyDescription', $this->options['description'], $this->stub);

        return $this;
    }
}
