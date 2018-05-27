<?php

declare(strict_types=1);

namespace Crunz\Timezone;

interface ProviderInterface
{
    /**
     * @return \DateTimeZone
     */
    public function defaultTimezone();
}
