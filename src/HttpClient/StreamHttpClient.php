<?php

namespace Crunz\HttpClient;

final class StreamHttpClient implements HttpClientInterface
{
    /**
     * @param string $url
     *
     * @throws HttpClientException
     */
    public function ping($url)
    {
        $context = \stream_context_create(
            [
                'http' => [
                    'user_agent' => 'Crunz StreamHttpClient',
                    'timeout' => 5,
                ],
            ]
        );
        $resource = @\fopen(
            $url,
            'rb',
            false,
            $context
        );

        if (false === $resource) {
            $error = \error_get_last();
            $errorMessage = isset($error['message']) ? $error['message'] : 'Unknown error';

            throw new HttpClientException("Ping failed with message: \"{$errorMessage}\".");
        }

        if ($resource) {
            \fclose($resource);
        }
    }
}
