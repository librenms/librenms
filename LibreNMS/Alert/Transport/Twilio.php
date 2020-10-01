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
 * @author Andy Rosen <arosen@arosen.net>
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;

class Twilio extends Transport
{
    public function deliverAlert($obj, $opts)
    {
        $twilio_opts['sid'] = $this->config['twilio-sid'];
        $twilio_opts['token'] = $this->config['twilio-token'];
        $twilio_opts['sender'] = $this->config['twilio-sender'];
        $twilio_opts['to'] = $this->config['twilio-to'];

        return $this->contacttwilio($obj, $twilio_opts);
    }

    public static function contactTwilio($obj, $opts)
    {
        $params = [
            'sid' => $opts['sid'],
            'token' => $opts['token'],
            'phone' => $opts['to'],
            'text' => $obj['title'],
            'sender' => $opts['sender'],
        ];

        $url = 'https://api.twilio.com/2010-04-01/Accounts/' . $params['sid'] . '/Messages.json';

        $data = [
            'From' => $params['sender'],
            'Body' => $params['text'],
            'To' => $params['phone'],
        ];
        $post = http_build_query($data);

        $curl = curl_init($url);

        // set_curl_proxy($curl);

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
                    'title' => 'SID',
                    'name' => 'twilio-sid',
                    'descr' => 'Twilio SID',
                    'type' => 'text',
                ],
                [
                    'title' => 'Token',
                    'name' => 'twilio-token',
                    'descr' => 'Twilio Account Token',
                    'type' => 'text',
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
                'twilio-sid'    => 'required|string',
                'twilio-token'    => 'required|string',
                'twilio-to' => 'required',
                'twilio-sender' => 'required',
            ],
        ];
    }
}
