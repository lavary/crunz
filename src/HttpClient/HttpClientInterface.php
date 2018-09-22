<?php

namespace Crunz\HttpClient;

interface HttpClientInterface
{
    /**
     * @param string $url
     *
     * @throws HttpClientException
     */
    public function ping($url);
}
