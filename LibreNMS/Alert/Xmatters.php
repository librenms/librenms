<?php
/**
 * Xmatters API Transport
 *
 * @author Sigurdur Stefan <https://github.com/LoveSkylark>
 * @copyright 2023 Sigurdur Stefan
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Xmatters extends Transport
{
    public function deliverAlert(array $alert_data): bool
    {
        $url = $this->config['xmatters_url'] . '/api/integration/1/functions/' . $this->config['xmatters_function'] . '/triggers';

        $device_url = \Config::get('base_url');
        $device_groups = \DeviceCache::get($alert_data['device_id'])->groups->pluck('name');

        // Build request data
        $data = [
            'properties' => $alert_data,
            'recipients' => $alert_data['contacts'],
            'priority' => $this->mapSeverityToPriority($alert_data['severity']),
        ];

        $data['properties']['event_state'] = $this->mapStateToEventState($alert_data['state']);
        $data['properties']['instance_url'] = $device_url;
        $data['properties']['device_url'] = ($device_url . '/device/' . $alert_data['device_id']);
        $data['properties']['alert_url'] = ($device_url . '/device/' . $alert_data['device_id'] . '/alerts/' . $alert_data['alert_id']);
        $data['properties']['hostname'] = $alert_data['sysName'];
        $data['properties']['ip'] = $alert_data['hostname'];
        $data['properties']['device_groups'] = $device_groups;

        $res = Http::client()
            ->withBasicAuth($this->config['xmatters_api_key'], $this->config['xmatters_api_secret'])
            ->acceptJson()
            ->post($url, $data);

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), $alert_data['msg'], $data);
    }

    protected function mapSeverityToPriority(string $severity): string
    {
        switch ($severity) {
            case 'critical':
                return 'High';
            case 'warning':
                return 'Medium';
            case 'ok':
                return 'Low';
            default:
                return 'invalid severity';
        }
    }

    protected function mapStateToEventState(int $state): string
    {
        switch ($state) {
            case 0:
                return 'ok';
            case 1:
                return 'alert';
            case 2:
                return 'ack';
            case 3:
                return 'better';
            case 4:
                return 'worse';
            default:
                return 'invalid state';
        }
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'xMatters URL',
                    'name' => 'xmatters_url',
                    'descr' => 'The hostname of the xMatters instance.',
                    'type' => 'text',
                ],
                [
                    'title' => 'xMatters Function',
                    'name' => 'xmatters_function',
                    'descr' => 'The ID of the xmatters flow http trigger or legacy inbound integration.',
                    'type' => 'text',
                ],
                [
                    'title' => 'xMatters API Key',
                    'name' => 'xmatters_api_key',
                    'descr' => 'The API Key key for authenticating the flow http trigger or legacy inbound integration.',
                    'type' => 'text',
                ],
                [
                    'title' => 'xMatters API Secret',
                    'name' => 'xmatters_api_secret',
                    'descr' => 'The Secret matching the API Key for authenticating the flow http trigger or legacy inbound integration.',
                    'type' => 'password',
                ],
            ],
            'validation' => [
                'xmatters_url' => 'required|url',
                'xmatters_function' => 'required|string',
                'xmatters_api_key' => 'required|string',
                'xmatters_api_secret' => 'required|string',
            ],
        ];
    }
}
