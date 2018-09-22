<?php

declare(strict_types=1);

namespace Crunz\Pinger;

interface PingableInterface
{
    /**
     * @param string $url
     *
     * @return $this
     */
    public function pingBefore($url);

    /**
     * @return bool
     *
     * @internal
     */
    public function hasPingBefore();

    /**
     * @param string $url
     *
     * @return $this
     */
    public function thenPing($url);

    /**
     * @return bool
     *
     * @internal
     */
    public function hasPingAfter();

    /**
     * @return string
     *
     * @internal
     */
    public function getPingBeforeUrl();

    /**
     * @return string
     *
     * @internal
     */
    public function getPingAfterUrl();
}
