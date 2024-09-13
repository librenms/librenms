<?php
/**
 * IBM On Call Manager API Transport
 *
 * @author Jayna Rogers rokinchikie@gmail.com
 * @copyright Jayna Rogers 2024
 * @license GPL
 * @package LibreNMS
 * @subpackage Alerts
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Util\Proxy;

class Ibmocm extends Transport
{
    protected string $name = 'IBM On Call Manager';

    public function deliverAlert(array $alert_data): bool
    {
        $url = $this->config['ocm-url'];
        $curl = curl_init();

        Proxy::applyToCurl($curl);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);

        // Build the JSON payload from the $alert_data array
        $payload = [
            'hostname' => $alert_data['hostname'] ?? null,
            'sysName' => $alert_data['sysName'] ?? null,
            'id' => $alert_data['id'] ?? null,
            'uid' => $alert_data['uid'] ?? null,
            'sysDescr' => $alert_data['sysDescr'] ?? null,
            'severity' => $alert_data['severity'] ?? null,
            'os' => $alert_data['os'] ?? null,
            'type' => $alert_data['type'] ?? null,
            'ip' => $alert_data['ip'] ?? null,
            'hardware' => $alert_data['hardware'] ?? null,
            'version' => $alert_data['version'] ?? null,
            'uptime' => $alert_data['uptime'] ?? null,
            'uptime_short' => $alert_data['uptime_short'] ?? null,
            'timestamp' => $alert_data['timestamp'] ?? time(),
            'description' => $alert_data['description'] ?? null,
            'title' => $alert_data['title'] ?? null,
            'state' => $alert_data['state'] ?? null,
        ];

        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));

        // Execute the request
        $ret = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // Handle errors
        if ($code != 200) {
            var_dump('Error when sending post request to IBM On Call Manager. Response code: ' . $code . ' Response body: ' . $ret);
            return false;
        }

        return true;
    }

    // Updated to include return type declaration
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