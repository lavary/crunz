<?php

declare(strict_types=1);

namespace Crunz\Application\Service;

use Crunz\Configuration\Configuration;
use Psr\Log\LoggerInterface;

/**
 * @experimental
 */
interface LoggerFactoryInterface
{
    public function create(Configuration $configuration): LoggerInterface;
}
