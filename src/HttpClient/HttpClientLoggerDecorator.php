<?php

declare(strict_types=1);

namespace Crunz\HttpClient;

use Crunz\Logger\ConsoleLoggerInterface;

final class HttpClientLoggerDecorator implements HttpClientInterface
{
    /** @var HttpClientInterface */
    private $httpClient;
    /** @var ConsoleLoggerInterface */
    private $logger;

    public function __construct(HttpClientInterface $httpClient, ConsoleLoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function ping($url): void
    {
        $this->logger
            ->verbose("Trying to ping <info>{$url}</info>.");

        $this->httpClient
            ->ping($url);

        $this->logger
            ->verbose("Pinging url: <info>{$url}</info> was <info>successful</info>.");
    }
}
