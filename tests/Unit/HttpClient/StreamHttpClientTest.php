<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit\HttpClient;

use Crunz\HttpClient\HttpClientException;
use Crunz\HttpClient\StreamHttpClient;
use PHPUnit\Framework\TestCase;

final class StreamHttpClientTest extends TestCase
{
    /** @test */
    public function pingFailWithInvalidAddress()
    {
        $this->expectException(HttpClientException::class);
        $this->expectExceptionMessage(
            'Ping failed with message: "fopen(http://www.wrong-address.tld): failed to open stream: php_network_getaddresses: getaddrinfo failed:'
        );

        $client = new StreamHttpClient();
        $client->ping('http://www.wrong-address.tld');
    }
}
