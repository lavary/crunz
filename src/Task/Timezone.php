<?php

namespace Crunz\Task;

use Crunz\Configuration\Configuration;
use Crunz\Exception\EmptyTimezoneException;

final class Timezone
{
    /** @var Configuration */
    private $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @throws EmptyTimezoneException
     */
    public function timezoneForComparisons(): \DateTimeZone
    {
        $newTimezone = $this->configuration
            ->get('timezone')
        ;

        if (empty($newTimezone)) {
            throw new EmptyTimezoneException(
                'Timezone must be configured. Please add it to your config file.'
            );
        }

        return new \DateTimeZone($newTimezone);
    }
}
