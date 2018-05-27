<?php

namespace Crunz\Task;

use Crunz\Configuration\Configuration;
use Crunz\Timezone\ProviderInterface;

final class Timezone
{
    /** @var Configuration */
    private $configuration;
    /** @var ProviderInterface */
    private $timezoneProvider;

    public function __construct(Configuration $configuration, ProviderInterface $timezoneProvider)
    {
        $this->configuration = $configuration;
        $this->timezoneProvider = $timezoneProvider;
    }

    public function timezoneForComparisons()
    {
        $newTimezone = $this->configuration
            ->get('timezone')
        ;

        /* @TODO Throw Exception in Crunz v2. */
        if (empty($newTimezone)) {
            @trigger_error(
                'Timezone is not configured and this is deprecated from 1.7 and will result in exception in 2.0 version. Add `timezone` key to your YAML config file.',
                E_USER_DEPRECATED
            );

            $newTimezone = $this->timezoneProvider
                ->defaultTimezone()
                ->getName()
            ;
        }

        return new \DateTimeZone($newTimezone);
    }
}
