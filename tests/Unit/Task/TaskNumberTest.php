<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\Task;

use Crunz\Exception\WrongTaskNumberException;
use Crunz\Task\TaskNumber;
use PHPUnit\Framework\TestCase;

class TaskNumberTest extends TestCase
{
    /**
     * @dataProvider nonStringValueProvider
     *
     * @param mixed $value
     * @test
     */
    public function can_not_create_task_number_with_non_string_value_by_from_string($value): void
    {
        $this->expectException(WrongTaskNumberException::class);
        $this->expectExceptionMessage('Passed task number is not string.');

        TaskNumber::fromString($value);
    }

    /**
     * @test
     * @dataProvider nonNumericProvider
     */
    public function task_number_can_not_be_non_numeric_string(string $value): void
    {
        $this->expectException(WrongTaskNumberException::class);
        $this->expectExceptionMessage("Task number '{$value}' is not numeric.");

        TaskNumber::fromString($value);
    }

    /**
     * @test
     * @dataProvider numericValueProvider
     */
    public function task_number_can_be_created_with_numeric_string_value(string $value, int $expectedNumber): void
    {
        $taskNumber = TaskNumber::fromString($value);

        $this->assertSame($expectedNumber, $taskNumber->asInt());
    }

    /** @test */
    public function array_index_is_one_step_lower(): void
    {
        $taskNumber = TaskNumber::fromString('14');

        $this->assertSame(13, $taskNumber->asArrayIndex());
    }

    /** @return iterable<string,array> */
    public function nonStringValueProvider(): iterable
    {
        yield 'null' => [null];
        yield 'float' => [3.14];
        yield 'int' => [7];
        yield 'array' => [[]];
        yield 'object' => [new \stdClass()];
    }

    /** @return iterable<string,array> */
    public function numericValueProvider(): iterable
    {
        yield 'int' => [
            '155',
            155,
        ];
        yield 'float' => [
            '3.14',
            3,
        ];
    }

    /** @return iterable<string,array> */
    public function nonNumericProvider(): iterable
    {
        yield 'chars' => ['abc'];
        yield 'charsWithNumber' => ['1a2b3'];
    }
}
