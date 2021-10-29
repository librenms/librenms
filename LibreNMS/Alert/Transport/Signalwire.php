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
use LibreNMS\Util\Proxy;

class Signalwire extends Transport
{
    protected $name = 'SignalWire';

    public function deliverAlert($obj, $opts)
    {
        $signalwire_opts['spaceUrl'] = $this->config['signalwire-spaceUrl'];
        $signalwire_opts['sid'] = $this->config['signalwire-project-id'];
        $signalwire_opts['token'] = $this->config['signalwire-token'];
        $signalwire_opts['sender'] = $this->config['signalwire-sender'];
        $signalwire_opts['to'] = $this->config['signalwire-to'];

        return $this->contactSignalwire($obj, $signalwire_opts);
    }

    public static function contactSignalwire($obj, $opts)
    {
        $params = [
            'spaceUrl' => $opts['spaceUrl'],
            'sid' => $opts['sid'],
            'token' => $opts['token'],
            'phone' => $opts['to'],
            'text' => $obj['title'],
            'sender' => $opts['sender'],
        ];

        $url = 'https://' . $params['spaceUrl'] . '.signalwire.com/api/laml/2010-04-01/Accounts/' . $params['sid'] . '/Messages.json';

        $data = [
            'From' => $params['sender'],
            'Body' => $params['text'],
            'To' => $params['phone'],
        ];
        $post = http_build_query($data);

        $curl = curl_init($url);

        Proxy::applyToCurl($curl);

        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, $params['sid'] . ':' . $params['token']);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post);

        curl_exec($curl);

        if (curl_getinfo($curl, CURLINFO_RESPONSE_CODE)) {
            return true;
        }
    }

    public static function configTemplate()
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
                    'type' => 'text',
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
