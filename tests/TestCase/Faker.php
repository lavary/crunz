<?php

declare(strict_types=1);

namespace Crunz\Tests\TestCase;

use Crunz\Exception\CrunzException;

final class Faker
{
    private const WORDS_ARRAY = [
        'lorem',
        'ipsum',
        'dolor',
        'sit',
        'amet',
        'consectetur',
        'adipiscing',
        'elit',
        'sed',
        'tincidunt',
        'neque',
        'massa',
    ];

    public static function timeZone(): \DateTimeZone
    {
        $timeZoneId = self::elementFromArray(\DateTimeZone::listIdentifiers());

        return new \DateTimeZone($timeZoneId);
    }

    /**
     * @param array<mixed> $elements
     *
     * @return mixed
     *
     * @throws CrunzException
     */
    public static function elementFromArray(array $elements)
    {
        $itemsCount = \count($elements);
        if (0 === $itemsCount) {
            throw new CrunzException('Passed array is empty.');
        }

        $normalizedElements = \array_values($elements);
        $index = self::int(0, ($itemsCount - 1));

        return $normalizedElements[$index];
    }

    public static function int(int $min = PHP_INT_MIN, int $max = PHP_INT_MAX): int
    {
        return \random_int($min, $max);
    }

    public static function dateTime(string $start = '-20 years', string $end = 'now'): \DateTimeImmutable
    {
        $min = new \DateTimeImmutable($start);
        $max = new \DateTimeImmutable($end);

        if ($min > $max) {
            throw new CrunzException("'start' is higher than 'end'.");
        }

        $dateTimestamp = self::int($min->getTimestamp(), $max->getTimestamp());

        return new \DateTimeImmutable("@{$dateTimestamp}");
    }

    public static function words(int $count = 3): string
    {
        $lastWord = \count(self::WORDS_ARRAY) - 1;
        $words = [];
        for ($i = 0; $i < $count; ++$i) {
            $wordIndex = self::int(0, $lastWord);
            $words[] = self::WORDS_ARRAY[$wordIndex];
        }

        return \implode(' ', $words);
    }
}
