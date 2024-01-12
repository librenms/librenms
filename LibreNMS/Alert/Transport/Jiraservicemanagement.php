<?php

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Jiraservicemanagement extends Transport
{
    protected string $name = 'Jira Service Management';

    public function deliverAlert(array $alert_data): bool
    {
        $url = $this->config['jsm-url'];

        $res = Http::client()->post($url, $alert_data);

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), '', $alert_data);
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'Webhook URL',
                    'name' => 'jsm-url',
                    'descr' => 'Jira Service Management Webhook URL',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'jsm-url' => 'required|url',
            ],
        ];
    }
}
