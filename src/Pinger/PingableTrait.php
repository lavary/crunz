<?php

declare(strict_types=1);

namespace Crunz\Pinger;

trait PingableTrait
{
    /** @var string */
    private $pingBeforeUrl = '';
    /** @var string */
    private $pingAfterUrl = '';

    /**
     * {@inheritdoc}
     */
    public function pingBefore($url)
    {
        $this->checkUrl($url);

        $this->pingBeforeUrl = $url;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasPingBefore()
    {
        return '' !== $this->pingBeforeUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function thenPing($url)
    {
        $this->checkUrl($url);

        $this->pingAfterUrl = $url;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasPingAfter()
    {
        return '' !== $this->pingAfterUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function getPingBeforeUrl()
    {
        if (!$this->hasPingBefore()) {
            throw new PingableException('PingBeforeUrl is empty.');
        }

        return $this->pingBeforeUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function getPingAfterUrl()
    {
        if (!$this->hasPingAfter()) {
            throw new PingableException('PingAfterUrl is empty.');
        }

        return $this->pingAfterUrl;
    }

    /**
     * @param string $url
     *
     * @throws PingableException
     */
    private function checkUrl($url): void
    {
        if (!\is_string($url)) {
            $type = \gettype($url);
            throw new PingableException("Url must be of type string, '{$type}' given.");
        }

        if ('' === $url) {
            throw new PingableException('Url cannot be empty.');
        }
    }
}
