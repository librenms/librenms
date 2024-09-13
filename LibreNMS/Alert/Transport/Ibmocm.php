<?php
/**
 * IBM On Call Manager API Transport
 *
 * @author Jayna Rogers rokinchikie@gmail.com
 * @copyright Jayna Rogers 2024
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Ibmocm extends Transport
{
    protected string $name = 'IBM On Call Manager';

    public function deliverAlert(array $alert_data): bool
    {
        $url = $this->config['ocm-url'];

        // Send HTTP POST request
        $res = Http::client()->post($url, $alert_data);

        // Check if request was successful
        if ($res->successful()) {
            return true;
        }

        // Throw an exception if the request failed
        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), '', $alert_data);
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'Webhook URL',
                    'name' => 'ocm-url',
                    'descr' => 'IBM On Call Manager Webhook URL',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'ocm-url' => 'required|url',
            ],
        ];
    }
}


