<?php
/**
 * LINE Notify Transport
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Linenotify extends Transport
{
    protected string $name = 'LINE Notify';

    public function deliverAlert(array $alert_data): bool
    {
        // TODO possible to attach graph images
        $lineUrl = 'https://notify-api.line.me/api/notify';
        $lineFields = ['message' => $alert_data['msg']];

        $res = Http::client()
            ->withToken($this->config['line-notify-access-token'])
            ->asForm()
            ->post($lineUrl, $lineFields);

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), $alert_data['msg'], $lineFields);
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'Token',
                    'name' => 'line-notify-access-token',
                    'descr' => 'LINE Notify Token',
                    'type' => 'password',
                ],
            ],
            'validation' => [
                'line-notify-access-token' => 'required|string',
            ],
        ];
    }
}
