<?php

namespace LibreNMS\Tests\Mocks;

use LibreNMS\Alert\AlertData;
use LibreNMS\Alert\Transport;
use LibreNMS\Interfaces\Alert\AlertTransport;

/**
 * A modern transport that records every alert it receives.
 */
class CapturingAlertTransport extends Transport implements AlertTransport
{
    /** @var list<AlertData> */
    public static array $received = [];

    public function deliver(AlertData $alert_data): bool
    {
        self::$received[] = $alert_data;

        return true;
    }

    public static function configTemplate(): array
    {
        return [];
    }
}
