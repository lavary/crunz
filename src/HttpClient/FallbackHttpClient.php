<?php

declare(strict_types=1);

namespace Crunz\HttpClient;

use Crunz\Logger\ConsoleLoggerInterface;

final class FallbackHttpClient implements HttpClientInterface
{
    /** @var StreamHttpClient */
    private $streamHttpClient;
    /** @var CurlHttpClient */
    private $curlHttpClient;
    /** @var HttpClientInterface|null */
    private $httpClient;
    /** @var ConsoleLoggerInterface */
    private $consoleLogger;

    public function __construct(
        StreamHttpClient $streamHttpClient,
        CurlHttpClient $curlHttpClient,
        ConsoleLoggerInterface $consoleLogger
    ) {
        $this->streamHttpClient = $streamHttpClient;
        $this->curlHttpClient = $curlHttpClient;
        $this->consoleLogger = $consoleLogger;
    }

    /**
     * @param string $url
     *
     * @throws HttpClientException
     */
    public function ping($url): void
    {
        $httpClient = $this->chooseHttpClient();
        $httpClient->ping($url);
    }

    /**
     * @return HttpClientInterface
     *
     * @throws HttpClientException
     */
    private function chooseHttpClient()
    {
        if (null !== $this->httpClient) {
            return $this->httpClient;
        }

        $this->consoleLogger
            ->debug('Choosing HttpClient implementation.');

        if (\function_exists('curl_exec')) {
            $this->httpClient = $this->curlHttpClient;

            $this->consoleLogger
                ->debug('cURL available, use <info>CurlHttpClient</info>.');

            return $this->httpClient;
        }

        if ('1' === \ini_get('allow_url_fopen')) {
            $this->httpClient = $this->streamHttpClient;

            $this->consoleLogger
                ->debug("'allow_url_fopen' enabled, use <info>StreamHttpClient</info>");

            return $this->httpClient;
        }

        $this->consoleLogger
            ->debug('<error>Choosing HttpClient implementation failed.</error>');

        throw new HttpClientException(
            "Unable to choose HttpClient. Enable cURL extension (preffered) or turn on 'allow_url_fopen' in php.ini."
        );
    }
}
