<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\HttpClient;

use Crunz\HttpClient\HttpClientException;
use Crunz\HttpClient\StreamHttpClient;
use PHPUnit\Framework\TestCase;

final class StreamHttpClientTest extends TestCase
{
    /** @test */
    public function pingFailWithInvalidAddress(): void
    {
        $expectedExceptionMessage = 'Ping failed with message: "fopen(http://www.wrong-address.tld): failed to open stream: php_network_getaddresses: getaddrinfo failed:';
        if (PHP_MAJOR_VERSION >= 8) {
            $expectedExceptionMessage = \str_replace(
                'failed to open',
                'Failed to open',
                $expectedExceptionMessage
            );
        }

        $this->expectException(HttpClientException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $client = new StreamHttpClient();
        $client->ping('http://www.wrong-address.tld');
    }
}
