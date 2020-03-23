<?php
/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

/**
 * smsmode API Transport
 * @author Anael Mobilia
 * @license GPL
 * @package LibreNMS
 * @subpackage Alerts
 */
namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;

class Smsmode extends Transport
{
    public function deliverAlert($obj, $opts)
    {
        $smsmode_opts['token'] = $this->config['smsmode-token'];
        $smsmode_opts['to']    = $this->config['smsmode-recipients'];
        return $this->contactsmsmode($obj, $smsmode_opts);
    }

    public static function contactsmsmode($obj, $opts)
    {
        $params = [
            'accessToken' => $opts['token'],
            'numero' => $opts['to'],
            'message' => iconv("UTF-8", "ISO-8859-15", $obj['title']),
        ];

/*
        HTTPS isn't currently supported (bad server configuration) => back to HTTP
        Feel free to update to HTTPS as the next command is successfull

        curl -vvv https://api.smsmode.com
        * Connected to api.smsmode.com (31.170.8.190) port 443 (#0)
        * ALPN, offering h2
        * ALPN, offering http/1.1
        * successfully set certificate verify locations:
        *   CAfile: none
          CApath: /etc/ssl/certs
        * error:1414D172:SSL routines:tls12_check_peer_sigalg:wrong signature type
        * Closing connection 0
        Error during API call

        See https://bugs.debian.org/cgi-bin/bugreport.cgi?bug=934453
        See https://bugs.debian.org/cgi-bin/bugreport.cgi?bug=912759
*/

        $url = 'http://api.smsmode.com/http/1.6/sendSMS.do?' . http_build_query($params);
        $curl   = curl_init($url);

        set_curl_proxy($curl);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $ret = curl_exec($curl);
        curl_close($curl);

        if (substr($ret, 0, 3) == "0 |") {
            return true;
        } else {
            return false;
        }
    }

    public static function configTemplate()
    {
        return [
            'config' => [
                [
                    'title' => 'smsmode API Token',
                    'name' => 'smsmode-token',
                    'descr' => 'smsmode API token',
                    'type' => 'text',
                ],
                [
                    'title' => 'Recipients',
                    'name' => 'smsmode-recipients',
                    'descr' => 'SMS recipients (comma separated)',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'smsmode-token'     => 'required',
                'smsmode-recipients' => 'required',
            ]
        ];
    }
}
