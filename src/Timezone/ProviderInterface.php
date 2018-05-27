<?php

namespace Crunz\Timezone;

interface ProviderInterface
{
    /**
     * @return \DateTimeZone
     */
    public function defaultTimezone();
}
