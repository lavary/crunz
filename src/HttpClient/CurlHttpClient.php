<?php

declare(strict_types=1);

namespace Crunz\HttpClient;

final class CurlHttpClient implements HttpClientInterface
{
    /**
     * {@inheritdoc}
     */
    public function ping($url): void
    {
        $curlResource = \curl_init();
        \curl_setopt(
            $curlResource,
            CURLOPT_RETURNTRANSFER,
            1
        );
        \curl_setopt(
            $curlResource,
            CURLOPT_URL,
            $url
        );
        \curl_setopt(
            $curlResource,
            CURLOPT_CONNECTTIMEOUT,
            5
        );
        \curl_setopt(
            $curlResource,
            CURLOPT_USERAGENT,
            'Crunz CurlHttpClient'
        );
        \curl_setopt(
            $curlResource,
            CURLOPT_FOLLOWLOCATION,
            true
        );
        \curl_setopt(
            $curlResource,
            CURLOPT_NOBODY,
            true
        );

        $result = \curl_exec($curlResource);

        if (false === $result) {
            $errorMessage = \curl_error($curlResource);

            throw new HttpClientException("Ping failed with message: \"{$errorMessage}\".");
        }

        \curl_close($curlResource);
    }
}
