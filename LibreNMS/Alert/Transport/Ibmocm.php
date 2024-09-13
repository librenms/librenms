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
    public function deliverAlert($obj, $opts)
    {
        // Set the OCM URL if configured
        if (! empty($this->config)) {
            $opts['url'] = $this->config['ocm-url'];
        }

        return $this->contactOCM($obj, $opts);
    }

    public function contactOCM($obj, $opts)
    {
        $url = $opts['url'];

        // Initialize the cURL request
        $curl = curl_init();

        Proxy::applyToCurl($curl);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);

        // Build the JSON payload
        $payload = [
            'hostname' => $obj['hostname'] ?? null,
            'sysName' => $obj['sysName'] ?? null,
            'id' => $obj['id'] ?? null,
            'uid' => $obj['uid'] ?? null,
            'sysDescr' => $obj['sysDescr'] ?? null,
            'severity' => $obj['severity'] ?? null,
            'os' => $obj['os'] ?? null,
            'type' => $obj['type'] ?? null,
            'ip' => $obj['ip'] ?? null,
            'hardware' => $obj['hardware'] ?? null,
            'version' => $obj['version'] ?? null,
            'uptime' => $obj['uptime'] ?? null,
            'uptime_short' => $obj['uptime_short'] ?? null,
            'timestamp' => $obj['timestamp'] ?? time(),
            'description' => $obj['description'] ?? null,
            'title' => $obj['title'] ?? null,
            'state' => $obj['state'] ?? null,
        ];

        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));

        // Execute the request
        $ret = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // Handle errors
        if ($code != 200) {
            var_dump('Error when sending post request to IBM On Call Manager. Response code: ' . $code . ' Response body: ' . $ret); // Proper debugging needed

            return false;
        }

        return true;
    }

    public static function configTemplate()
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