<?php

namespace LibreNMS\Alert;

abstract class AlertDataTransport extends Transport
{
    public function deliverAlert(array $alert_data): bool
    {
        return $this->deliverAlertData($alert_data);
    }

    /**
     * @param  array<string, mixed>|AlertData  $alert_data
     */
    abstract public function deliverAlertData(array|AlertData $alert_data): bool;
}
