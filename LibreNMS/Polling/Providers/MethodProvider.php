<?php

namespace LibreNMS\Polling\Providers;

use LibreNMS\Enum\PollingMethodType;

/**
 * A provider and the polling method it came from, so data can be linked to the method.
 *
 * @template-covariant T of object
 */
final readonly class MethodProvider
{
    /**
     * @param  T  $provider
     */
    public function __construct(
        public PollingMethodType $type,
        public object $provider,
    ) {
    }
}
