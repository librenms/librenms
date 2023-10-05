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
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Linemessagingapi extends Transport
{
    protected string $name = 'LINE Messaging API';

    /**
     * Deliver Alert
     *
     * @param  array<string, string>  $alert_data  Alert data
     * @return bool True if message sent successfully
     */
    public function deliverAlert($alert_data): bool
    {
        $apiURL = 'https://api.line.me/v2/bot/message/push';
        $data = [
            'to' => $this->config['line-messaging-to'],
            'messages' => [
                [
                    'type' => 'text',
                    'text' => $alert_data['msg'],
                ],
            ],
        ];

        $res = Http::client()
        ->withToken($this->config['line-messaging-token'])
        ->asForm()
        ->post($apiURL, $data);

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), $alert_data['msg'], $data);
    }

    /**
     * Get config template
     *
     * @return array<string, mixed> config template
     */
    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'Access token',
                    'name' => 'line-messaging-token',
                    'descr' => 'LINE Channel access token',
                    'type' => 'password',
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
