<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\HttpClient;

use Crunz\HttpClient\HttpClientException;
use Crunz\HttpClient\StreamHttpClient;
use PHPUnit\Framework\TestCase;

final class StreamHttpClientTest extends TestCase
{
    /** @test */
    public function ping_fail_with_invalid_address(): void
    {
        // Arrange
        $url = 'http://www.wrong-address.tld';
        $client = new StreamHttpClient();
        $expectedExceptionMessage = "Ping failed with message: \"fopen({$url}): failed to open stream";
        if (PHP_MAJOR_VERSION >= 8) {
            $expectedExceptionMessage = \str_replace(
                'failed to open',
                'Failed to open',
                $expectedExceptionMessage
            );
        }

        // Expect
        $this->expectException(HttpClientException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        // Act
        $client->ping('http://www.wrong-address.tld');
    }
}
