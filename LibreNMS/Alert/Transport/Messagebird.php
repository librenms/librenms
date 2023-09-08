<?php
/**
 * Messagebird.php
 *
 * LibreNMS Messagebird API Tranport
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2023 Sjef van Zeeland
 * @author     https://github.com/jepke/
 *
 * Messagebird will return 201 status if the text message was send if no 201 LibreNMS will log the full error
 *
 * @contributer f0o, sdef2
 * Thanks to F0o <f0o@devilcode.org> for creating the Slack transport which is the majority of this code.
 * Thanks to sdef2 for figuring out the differences needed to make Discord work.
 * Thanks to theherodied for discord transport used as a base for messagebird.
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Messagebird extends Transport
{
    public function deliverAlert(array $alert_data): bool
    {
        $messagebird_msg = mb_strimwidth($alert_data['msg'], 0, $this->config['messagebird-limit'] - 3, '...');
        $api_url = 'https://rest.messagebird.com/messages';
        $fields = [
            'recipients' => $this->config['messagebird-recipient'],
            'originator' => $this->config['messagebird-origin'],
            'body' => $messagebird_msg,
        ];

        $res = Http::client()
            ->withHeaders([
                'Authorization' => 'AccessKey ' . $this->config['messagebird-key'],
            ])
            ->post($api_url, $fields);

        if ($res->successful() && $res->status() == '201') {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), $messagebird_msg, $fields);
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'Messagebird API key',
                    'name' => 'messagebird-key',
                    'descr' => 'Messagebird API REST key',
                    'type' => 'password',
                ],
                [
                    'title' => 'Messagebird originator',
                    'name' => 'messagebird-origin',
                    'descr' => 'Originator in E.164 format eg. +1555###****',
                    'type' => 'text',
                ],
                [
                    'title' => 'Messagebird recipients',
                    'name' => 'messagebird-recipient',
                    'descr' => 'Recipient in E.164 format eg. +1555###****',
                    'type' => 'text',
                ],
                [
                    'title' => 'Limit characters in text message',
                    'name' => 'messagebird-limit',
                    'descr' => 'Limit max characters',
                    'type' => 'text',
                    'default' => 120,
                ],
            ],
            'validation' => [
                'messagebird-key' => 'required',
                'messagebird-origin' => 'required',
                'messagebird-recipient' => 'required',
                'messagebird-limit' => 'integer|between:1,480',
            ],
        ];
    }
}
