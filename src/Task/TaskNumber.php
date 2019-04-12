<?php

declare(strict_types=1);

namespace Crunz\Task;

use Crunz\Exception\WrongTaskNumberException;

class TaskNumber
{
    const MIN_VALUE = 1;
    /** @var int */
    private $number;

    /**
     * @throws WrongTaskNumberException
     *
     * @param int $number
     */
    private function __construct($number)
    {
        if ($number < self::MIN_VALUE) {
            throw new WrongTaskNumberException('Passed task number must be greater or equal to 1.');
        }

        $this->number = $number;
    }

    /**
     * @param string $value
     *
     * @return TaskNumber
     *
     * @throws WrongTaskNumberException
     */
    public static function fromString($value)
    {
        if (!\is_string($value)) {
            throw new WrongTaskNumberException('Passed task number is not string.');
        }

        if (!\is_numeric($value)) {
            throw new WrongTaskNumberException("Task number '{$value}' is not numeric.");
        }

        $number = (int) $value;

        return new self($number);
    }

    /**
     * @return int
     */
    public function asInt()
    {
        return $this->number;
    }

    /**
     * @return int
     */
    public function asArrayIndex()
    {
        return $this->number - 1;
    }
}
