<?php
/**
 * transport-telegram.inc.php
 *
 * LibreNMS Telegram alerting transport
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
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Telegram extends Transport
{
    public function deliverAlert(array $alert_data): bool
    {
        $url = "https://api.telegram.org/bot{$this->config['telegram-token']}/sendMessage";
        $format = $this->config['telegram-format'];
        $text = $format == 'Markdown'
            ? preg_replace('/([a-z0-9]+)_([a-z0-9]+)/', "$1\_$2", $alert_data['msg'])
            : $alert_data['msg'];

        $params = [
            'chat_id' => $this->config['telegram-chat-id'],
            'text' => $text,
        ];

        if ($format) {
            $params['parse_mode'] = $this->config['telegram-format'];
        }

        if (! empty($this->config['message-thread-id'])) {
            $params['message_thread_id'] = $this->config['message-thread-id'];
        }

        $res = Http::client()->get($url, $params);

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), $text, $params);
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'Chat ID',
                    'name' => 'telegram-chat-id',
                    'descr' => 'Telegram Chat ID',
                    'type' => 'text',
                ],
                [
                    'title' => 'Thread ID',
                    'name' => 'message-thread-id',
                    'descr' => 'If your group support topics, you can put the topicId here',
                    'type' => 'text',
                ],
                [
                    'title' => 'Token',
                    'name' => 'telegram-token',
                    'descr' => 'Telegram Token',
                    'type' => 'password',
                ],
                [
                    'title' => 'Format',
                    'name' => 'telegram-format',
                    'descr' => 'Telegram format',
                    'type' => 'select',
                    'options' => [
                        '' => '',
                        'Markdown' => 'Markdown',
                        'HTML' => 'HTML',
                    ],
                ],
            ],
            'validation' => [
                'telegram-chat-id' => 'required|string',
                'message-thread-id' => 'integer',
                'telegram-token' => 'required|string',
                'telegram-format' => 'string',
            ],
        ];
    }
}
