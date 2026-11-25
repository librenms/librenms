<?php

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\AlertData;
use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Interfaces\Alert\AlertTransport;
use LibreNMS\Util\Http;

class Jsm extends Transport implements AlertTransport
{
    protected string $name = 'Jira Service Management';

    public function deliver(AlertData $alert_data): bool
    {
        $url = $this->config['jsm-url'];

        $payload = $alert_data->toTransportPayload();
        $res = Http::client()->post($url, $payload);

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
