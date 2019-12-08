<?php

namespace Crunz\Process;

use Symfony\Component\Process\Process as SymfonyProcess;

final class Process
{
    /** @var bool */
    private static $needsInheritEnvVars;
    /** @var SymfonyProcess */
    private $process;

    private function __construct(SymfonyProcess $process)
    {
        $this->process = $process;
    }

    /**
     * @param array|string $command
     * @param string|null  $cwd
     *
     * @return self
     */
    public static function fromStringCommand($command, $cwd = null)
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

    /**
     * @param callable|null $callback
     */
    public function start($callback = null)
    {
        $this->process
            ->start($callback);
    }

    public function wait()
    {
        $this->process
            ->wait();
    }

    public function startAndWait()
    {
        $this->process
            ->start();
        $this->process
            ->wait();
    }

    public function setEnv(array $env)
    {
        $this->process
            ->setEnv($env);
    }

    /** @return int|null */
    public function getPid()
    {
        return $this->process
            ->getPid();
    }

    /** @return bool */
    public function isInheritEnvVarsSupported()
    {
        return self::needsInheritEnvVars();
    }

    /** @return bool */
    public function isRunning()
    {
        return $this->process
            ->isRunning();
    }

    /** @return bool */
    public function isSuccessful()
    {
        return $this->process
            ->isSuccessful();
    }

    /** @return string */
    public function getOutput()
    {
        return $this->process
            ->getOutput();
    }

    /** bool */
    private static function needsInheritEnvVars()
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

                self::$needsInheritEnvVars = false === \strpos($docs, '@deprecated');
            }
        }

        return self::$needsInheritEnvVars;
    }
}
