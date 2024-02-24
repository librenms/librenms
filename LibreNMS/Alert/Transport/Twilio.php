<?php
/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
*/
/**
 * Twilio API Transport
 *
 * @author Andy Rosen <arosen@arosen.net>
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Twilio extends Transport
{
    public function deliverAlert(array $alert_data): bool
    {
        $url = 'https://api.twilio.com/2010-04-01/Accounts/' . $this->config['twilio-sid'] . '/Messages.json';

        $data = [
            'From' => $this->config['twilio-sender'],
            'Body' => $alert_data['msg'],
            'To' => $this->config['twilio-to'],
        ];

        $res = Http::client()->asForm()
            ->withBasicAuth($this->config['twilio-sid'], $this->config['twilio-token'])
            ->post($url, $data);

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), $alert_data['msg'], $data);
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'SID',
                    'name' => 'twilio-sid',
                    'descr' => 'Twilio SID',
                    'type' => 'text',
                ],
                [
                    'title' => 'Token',
                    'name' => 'twilio-token',
                    'descr' => 'Twilio Account Token',
                    'type' => 'password',
                ],
                [
                    'title' => 'Mobile Number',
                    'name' => 'twilio-to',
                    'descr' => 'Mobile number to SMS',
                    'type' => 'text',
                ],
                [
                    'title' => 'Twilio SMS Number',
                    'name' => 'twilio-sender',
                    'descr' => 'Twilio sending number',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'twilio-sid' => 'required|string',
                'twilio-token' => 'required|string',
                'twilio-to' => 'required',
                'twilio-sender' => 'required',
            ],
        ];
    }
}
