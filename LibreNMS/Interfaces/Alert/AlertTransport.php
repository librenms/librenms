<?php

namespace LibreNMS\Interfaces\Alert;

use LibreNMS\Alert\AlertData;

/**
 * A transport that receives alerts as AlertData.
 * Transports without it are legacy and receive the array payload through deliverAlert().
 */
interface AlertTransport
{
    /**
     * @throws \LibreNMS\Exceptions\AlertTransportDeliveryException
     */
    public function deliver(AlertData $alert_data): bool;
}
