<?php

declare(strict_types=1);

namespace Crunz\Console\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;

class Command extends BaseCommand
{
    /**
     * Command arguments.
     *
     * @var array<string,string>
     */
    protected $arguments;

    /**
     * Command options.
     *
     * @var array<string,string>
     */
    protected $options;

    /**
     * Input object.
     *
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    protected $input;

    /**
     * output object.
     *
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;
}
