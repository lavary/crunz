<?php

declare(strict_types=1);

namespace Crunz\Application\Service;

use Psr\Log\LoggerInterface;

/**
 * @experimental
 */
interface LoggerFactoryInterface
{
    public function create(ConfigurationInterface $configuration): LoggerInterface;
}
