<?php
/**
 * Line Messaging API Transport
 *
 * @author Johnny Sung <https://github.com/j796160836>
 * @copyright 2023 Johnny Sung
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Config;
use LibreNMS\Util\Proxy;

class LineMessagingAPI extends Transport
{
    protected $name = 'Line Messaging API';

    /**
     * Deliver Alert
     *
     * @param  array<string, string>  $obj  Alert data
     * @param  array<string, string>  $opts  Transport options
     * @return bool True if message sent successfully
     */
    public function deliverAlert($obj, $opts)
    {
        $opts['token'] = $this->config['line-messaging-token'];
        $opts['to'] = $this->config['line-messaging-to'];

        return $this->contactLineMessagingAPI($obj, $opts);
    }

    /**
     * Contact Line Messaging API
     *
     * @param  array<string, string>  $obj  Alert data
     * @param  array<string, string>  $opts  Transport options
     * @return bool True if message sent successfully
     */
    public function contactLineMessagingAPI($obj, $opts)
    {
        $apiURL = 'https://api.line.me/v2/bot/message/push';
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $opts['token'],
        ];

        $data = [
            'to' => $opts['to'],
            'messages' => [
                [
                    'type' => 'text',
                    'text' => $obj['msg'],
                ],
            ],
        ];

        $alert_message = json_encode($data);

        $curl = curl_init();
        Proxy::applyToCurl($curl);
        curl_setopt($curl, CURLOPT_URL, $apiURL);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_NOBODY, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $alert_message);
        curl_exec($curl);
        $ret = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ($code != 200) {
            var_dump("API '$apiURL' returned HTTP Status code: $code");
            var_dump('Params: ' . $alert_message);
            var_dump('Return: ' . $ret);
            var_dump('Headers: ', $headers);

            return false;
        }

        return true;
    }

    /**
     * Get config template
     *
     * @return array<string, mixed> config template
     */
    public static function configTemplate()
    {
        return [
            'config' => [
                [
                    'title' => 'Access token',
                    'name' => 'line-messaging-token',
                    'descr' => 'LINE Channel access token',
                    'type' => 'text',
                ],
                [
                    'title' => 'Recipient (groupID, userID or roomID)',
                    'name' => 'line-messaging-to',
                    'descr' => 'The ID of the target recipient. Use a userId, groupId or roomID.',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'line-messaging-token' => 'required|string',
                'line-messaging-to' => 'required|string',
            ],
        ];
    }
}
