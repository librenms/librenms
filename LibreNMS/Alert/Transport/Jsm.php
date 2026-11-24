<?php

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\AlertData;
use LibreNMS\Alert\AlertDataTransport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Jsm extends AlertDataTransport
{
    protected string $name = 'Jira Service Management';

    /**
     * @param  array<string, mixed>|AlertData  $alert_data
     * @return bool
     */
    public function deliverAlertData(array|AlertData $alert_data): bool
    {
        $url = $this->config['jsm-url'];

        $payload = $alert_data instanceof AlertData ? $alert_data->toTransportPayload() : $alert_data;
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
