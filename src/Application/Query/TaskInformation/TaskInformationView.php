<?php

declare(strict_types=1);

namespace Crunz\Application\Query\TaskInformation;

final class TaskInformationView
{
    /** @var string|object */
    private $command;
    /** @var string */
    private $description;
    /** @var string */
    private $cronExpression;
    /** @var \DateTimeZone|null */
    private $timeZone;
    /** @var \DateTimeZone */
    private $configTimeZone;
    /** @var \DateTimeImmutable[] */
    private $nextRuns;
    /** @var bool */
    private $preventOverlapping;

    /** @param string|object $command */
    public function __construct(
        $command,
        string $description,
        string $cronExpression,
        bool $preventOverlapping,
        ?\DateTimeZone $timeZone,
        \DateTimeZone $configTimeZone,
        \DateTimeImmutable ...$nextRuns
    ) {
        $this->command = $command;
        $this->description = $description;
        $this->cronExpression = $cronExpression;
        $this->timeZone = $timeZone;
        $this->configTimeZone = $configTimeZone;
        $this->nextRuns = $nextRuns;
        $this->preventOverlapping = $preventOverlapping;
    }

    /** @return string|object */
    public function command()
    {
        return $this->command;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function cronExpression(): string
    {
        return $this->cronExpression;
    }

    public function timeZone(): ?\DateTimeZone
    {
        return $this->timeZone;
    }

    public function configTimeZone(): \DateTimeZone
    {
        return $this->configTimeZone;
    }

    /** @return \DateTimeImmutable[] */
    public function nextRuns(): array
    {
        return $this->nextRuns;
    }

    public function preventOverlapping(): bool
    {
        return $this->preventOverlapping;
    }
}
