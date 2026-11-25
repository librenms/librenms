<?php

namespace LibreNMS\Tests\Mocks;

use LibreNMS\Alert\Transport;

/**
 * A legacy (array based) transport, like a custom transport in a local install,
 * that records every payload it receives.
 */
class CapturingLegacyAlertTransport extends Transport
{
    /** @var list<array<string, mixed>> */
    public static array $received = [];

    public function deliverAlert(array $alert_data): bool
    {
        self::$received[] = $alert_data;

        return true;
    }

    public static function configTemplate(): array
    {
        return [];
    }
}
