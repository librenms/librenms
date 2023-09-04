<?php
/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
*/

/**
 * SignalWire API Transport
 *
 * @author Igor Kuznetsov <igor@oczmail.com>
 * This is modifyed Twilio class from Andy Rosen <arosen@arosen.net>
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Signalwire extends Transport
{
    protected string $name = 'SignalWire';

    public function deliverAlert(array $alert_data): bool
    {
        $url = 'https://' . $this->config['signalwire-spaceUrl'] . '.signalwire.com/api/laml/2010-04-01/Accounts/' . $this->config['signalwire-project-id'] . '/Messages.json';

        $data = [
            'From' => $this->config['signalwire-sender'],
            'To' => $this->config['signalwire-to'],
            'Body' => $alert_data['title'],
        ];

        $res = Http::client()->asForm()
            ->withBasicAuth($this->config['signalwire-project-id'], $this->config['signalwire-token'])
            ->post($url, $data);

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), $alert_data['title'], $data);
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'Space URL',
                    'name' => 'signalwire-spaceUrl',
                    'descr' => 'SignalWire Space URL (Example: myspace).',
                    'type' => 'text',
                ],
                [
                    'title' => 'SignalWire Project ID',
                    'name' => 'signalwire-project-id',
                    'descr' => 'SignalWire Project ID  ',
                    'type' => 'text',
                ],
                [
                    'title' => 'Token',
                    'name' => 'signalwire-token',
                    'descr' => 'SignalWire Account Token ',
                    'type' => 'password',
                ],
                [
                    'title' => 'Mobile Number',
                    'name' => 'signalwire-to',
                    'descr' => 'Mobile number to SMS(Example: +14443332222)',
                    'type' => 'text',
                ],
                [
                    'title' => 'SignalWire SMS Number',
                    'name' => 'signalwire-sender',
                    'descr' => 'SignalWire sending number (Example: +12223334444)',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'signalwire-spaceUrl' => 'required|string',
                'signalwire-project-id' => 'required|string',
                'signalwire-token' => 'required|string',
                'signalwire-to' => 'required',
                'signalwire-sender' => 'required',
            ],
        ];
    }
}
