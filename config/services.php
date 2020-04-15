<?php

declare(strict_types=1);

use Crunz\Application\Cron\CronExpressionFactoryInterface;
use Crunz\Application\Query\TaskInformation\TaskInformationHandler;
use Crunz\Application\Service\ClosureSerializerInterface;
use Crunz\Application\Service\ConfigurationInterface;
use Crunz\Clock\Clock;
use Crunz\Clock\ClockInterface;
use Crunz\Configuration\Configuration;
use Crunz\Configuration\ConfigurationParser;
use Crunz\Configuration\ConfigurationParserInterface;
use Crunz\Configuration\Definition;
use Crunz\Configuration\FileParser;
use Crunz\Console\Command\ConfigGeneratorCommand;
use Crunz\Console\Command\ScheduleListCommand;
use Crunz\Console\Command\ScheduleRunCommand;
use Crunz\Console\Command\TaskGeneratorCommand;
use Crunz\EventRunner;
use Crunz\Filesystem\Filesystem as CrunzFilesystem;
use Crunz\Filesystem\FilesystemInterface;
use Crunz\Finder\Finder;
use Crunz\Finder\FinderInterface;
use Crunz\HttpClient\CurlHttpClient;
use Crunz\HttpClient\FallbackHttpClient;
use Crunz\HttpClient\HttpClientInterface;
use Crunz\HttpClient\HttpClientLoggerDecorator;
use Crunz\HttpClient\StreamHttpClient;
use Crunz\Infrastructure\Dragonmantank\CronExpression\DragonmantankCronExpressionFactory;
use Crunz\Infrastructure\Opis\Closure\OpisClosureSerializer;
use Crunz\Invoker;
use Crunz\Logger\ConsoleLogger;
use Crunz\Logger\ConsoleLoggerInterface;
use Crunz\Logger\LoggerFactory;
use Crunz\Mailer;
use Crunz\Output\OutputFactory;
use Crunz\Schedule\ScheduleFactory;
use Crunz\Task\Collection;
use Crunz\Task\Loader;
use Crunz\Task\LoaderInterface;
use Crunz\Task\Timezone;
use Crunz\Timezone\Provider;
use Crunz\Timezone\ProviderInterface;
use Crunz\UserInterface\Cli\ClosureRunCommand;
use Crunz\UserInterface\Cli\DebugTaskCommand;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

$simpleServices = [
    Definition::class,
    Yaml::class,
    Processor::class,
    Invoker::class,
    ProviderInterface::class => Provider::class,
    Filesystem::class,
    ScheduleFactory::class,
    StreamHttpClient::class,
    CurlHttpClient::class,
    FilesystemInterface::class => CrunzFilesystem::class,
    FinderInterface::class => Finder::class,
    LoaderInterface::class => Loader::class,
    CronExpressionFactoryInterface::class => DragonmantankCronExpressionFactory::class,
    ClosureSerializerInterface::class => OpisClosureSerializer::class,
    ClockInterface::class => Clock::class,
];

/* @var ContainerBuilder $container */

$container
    ->register(ScheduleRunCommand::class, ScheduleRunCommand::class)
    ->setPublic(true)
    ->setArguments(
        [
            new Reference(Collection::class),
            new Reference(ConfigurationInterface::class),
            new Reference(EventRunner::class),
            new Reference(Timezone::class),
            new Reference(ScheduleFactory::class),
            new Reference(LoaderInterface::class),
        ]
    )
;
$container
    ->register(ClosureRunCommand::class, ClosureRunCommand::class)
    ->setArguments(
        [
            new Reference(ClosureSerializerInterface::class),
        ]
    )
    ->setPublic(true)
;
$container
    ->register(ConfigGeneratorCommand::class, ConfigGeneratorCommand::class)
    ->setPublic(true)
    ->setArguments(
        [
            new Reference(ProviderInterface::class),
            new Reference(Filesystem::class),
            new Reference(FilesystemInterface::class),
        ]
    )
;
$container
    ->register(ScheduleListCommand::class, ScheduleListCommand::class)
    ->setPublic(true)
    ->setArguments(
        [
            new Reference(ConfigurationInterface::class),
            new Reference(Collection::class),
            new Reference(LoaderInterface::class),
        ]
    )
;
$container
    ->register(TaskGeneratorCommand::class, TaskGeneratorCommand::class)
    ->setPublic(true)
    ->setArguments(
        [
            new Reference(ConfigurationInterface::class),
            new Reference(FilesystemInterface::class),
        ]
    )
;
$container
    ->register(DebugTaskCommand::class, DebugTaskCommand::class)
    ->setPublic(true)
    ->setArguments(
        [
            new Reference(TaskInformationHandler::class),
        ]
    )
;
$container
    ->register(Collection::class, Collection::class)
    ->setPublic(false)
    ->setArguments(
        [
            new Reference(ConfigurationInterface::class),
            new Reference(FinderInterface::class),
            new Reference(ConsoleLoggerInterface::class),
        ]
    )
;
$container
    ->register(FileParser::class, FileParser::class)
    ->setPublic(false)
    ->setArguments(
        [
            new Reference(Yaml::class),
        ]
    )
;
$container
    ->register(ConfigurationInterface::class, Configuration::class)
    ->setPublic(false)
    ->setArguments(
        [
            new Reference(ConfigurationParserInterface::class),
            new Reference(FilesystemInterface::class),
        ]
    )
;
$container
    ->register(Mailer::class, Mailer::class)
    ->setPublic(false)
    ->setArguments(
        [
            new Reference(ConfigurationInterface::class),
        ]
    )
;
$container
    ->register(LoggerFactory::class, LoggerFactory::class)
    ->setPublic(false)
    ->setArguments(
        [
            new Reference(ConfigurationInterface::class),
            new Reference(Timezone::class),
            new Reference(ConsoleLoggerInterface::class),
            new Reference(ClockInterface::class),
        ]
    )
;
$container
    ->register(EventRunner::class, EventRunner::class)
    ->setPublic(false)
    ->setArguments(
        [
            new Reference(Invoker::class),
            new Reference(ConfigurationInterface::class),
            new Reference(Mailer::class),
            new Reference(LoggerFactory::class),
            new Reference(HttpClientInterface::class),
            new Reference(ConsoleLoggerInterface::class),
        ]
    )
;
$container
    ->register(Timezone::class, Timezone::class)
    ->setPublic(false)
    ->setArguments(
        [
            new Reference(ConfigurationInterface::class),
            new Reference(ConsoleLoggerInterface::class),
        ]
    )
;
$container
    ->register(OutputInterface::class, ConsoleOutput::class)
    ->setPublic(true)
    ->setFactory([new Reference(OutputFactory::class), 'createOutput'])
;
$container
    ->register(OutputFactory::class, OutputFactory::class)
    ->setPublic(false)
    ->setArguments(
        [
            new Reference(InputInterface::class),
        ]
    )
;
$container
    ->register(InputInterface::class, ArgvInput::class)
    ->setPublic(true)
;
$container
    ->register(SymfonyStyle::class, SymfonyStyle::class)
    ->setPublic(true)
    ->setArguments(
        [
            new Reference(InputInterface::class),
            new Reference(OutputInterface::class),
        ]
    )
;
$container
    ->register(ConsoleLoggerInterface::class, ConsoleLogger::class)
    ->setPublic(false)
    ->setArguments(
        [
            new Reference(SymfonyStyle::class),
        ]
    )
;
$container
    ->register(ConsoleLoggerInterface::class, ConsoleLogger::class)
    ->setPublic(false)
    ->setArguments(
        [
            new Reference(SymfonyStyle::class),
        ]
    )
;
$container
    ->register(FallbackHttpClient::class, FallbackHttpClient::class)
    ->setPublic(false)
    ->setArguments(
        [
            new Reference(StreamHttpClient::class),
            new Reference(CurlHttpClient::class),
            new Reference(ConsoleLoggerInterface::class),
        ]
    )
;
$container
    ->register(HttpClientInterface::class, HttpClientLoggerDecorator::class)
    ->setPublic(false)
    ->setArguments(
        [
            new Reference(FallbackHttpClient::class),
            new Reference(ConsoleLoggerInterface::class),
        ]
    )
;
$container
    ->register(ConfigurationParserInterface::class, ConfigurationParser::class)
    ->setPublic(false)
    ->setArguments(
        [
            new Reference(Definition::class),
            new Reference(Processor::class),
            new Reference(FileParser::class),
            new Reference(ConsoleLoggerInterface::class),
            new Reference(FilesystemInterface::class),
        ]
    )
;

$container
    ->register(TaskInformationHandler::class, TaskInformationHandler::class)
    ->setPublic(false)
    ->setArguments(
        [
            new Reference(Timezone::class),
            new Reference(ConfigurationInterface::class),
            new Reference(Collection::class),
            new Reference(LoaderInterface::class),
            new Reference(ScheduleFactory::class),
            new Reference(CronExpressionFactoryInterface::class),
        ]
    )
;

foreach ($simpleServices as $id => $simpleService) {
    if (!\is_string($id)) {
        $id = $simpleService;
    }

    $container
        ->register($id, $simpleService)
        ->setPublic(false)
    ;
}
