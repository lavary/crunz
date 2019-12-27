<?php

declare(strict_types=1);

namespace Crunz\Process;

use Symfony\Component\Process\Process as SymfonyProcess;

/** @internal */
final class Process
{
    /** @var bool */
    private static $needsInheritEnvVars;
    /** @var SymfonyProcess|string[] */
    private $process;

    /** @param SymfonyProcess|string[] $process */
    private function __construct(SymfonyProcess $process)
    {
        $this->process = $process;
    }

    /** @param string[]|string $command */
    public static function fromStringCommand($command, ?string $cwd = null): self
    {
        if (\method_exists(SymfonyProcess::class, 'fromShellCommandline')) {
            $process = SymfonyProcess::fromShellCommandline($command, $cwd);
        } else {
            $process = new SymfonyProcess($command, $cwd);
        }

        if (self::needsInheritEnvVars()) {
            $process->inheritEnvironmentVariables(true);
        }

        return new self($process);
    }

    /** @param callable|null $callback */
    public function start($callback = null): void
    {
        $this->process
            ->start($callback);
    }

    public function wait(): void
    {
        $this->process
            ->wait();
    }

    public function startAndWait(): void
    {
        $this->process
            ->start();
        $this->process
            ->wait();
    }

    /** @param array<string,string> $env */
    public function setEnv(array $env): void
    {
        $this->process
            ->setEnv($env);
    }

    public function getPid(): ?int
    {
        return $this->process
            ->getPid();
    }

    public function isRunning(): bool
    {
        return $this->process
            ->isRunning();
    }

    public function isSuccessful(): bool
    {
        return $this->process
            ->isSuccessful();
    }

    public function getOutput(): string
    {
        return $this->process
            ->getOutput();
    }

    public function errorOutput(): string
    {
        return $this->process
            ->getErrorOutput();
    }

    private static function needsInheritEnvVars(): bool
    {
        if (null === self::$needsInheritEnvVars) {
            $methodName = 'inheritEnvironmentVariables';
            $symfonyProcessReflection = new \ReflectionClass(SymfonyProcess::class);

            if (!$symfonyProcessReflection->hasMethod($methodName)) {
                self::$needsInheritEnvVars = false;
            } else {
                $inheritMethodReflection = $symfonyProcessReflection->getMethod($methodName);
                $docs = $inheritMethodReflection->getDocComment();
                $docs = false !== $docs
                    ? $docs
                    : ''
                ;

                self::$needsInheritEnvVars = false === \mb_strpos($docs, '@deprecated');
            }
        }

        return self::$needsInheritEnvVars;
    }
}
