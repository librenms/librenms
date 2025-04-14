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
use LibreNMS\Util\Graph;
use LibreNMS\Util\Http;

class Telegram extends Transport
{
    private  const BASE_URL = 'https://api.telegram.org/bot';

    private $message = [];

    public function deliverAlert(array $alert_data): bool
    {
        $url_send_message = self::BASE_URL . "{$this->config['telegram-token']}/sendMessage";
        $url_send_photo = self::BASE_URL . "{$this->config['telegram-token']}/sendPhoto";
        $url_send_file = self::BASE_URL . "{$this->config['telegram-token']}/sendDocument";
        $send_as = "{$this->config['telegram-send-png-graph-mode']}";

        $format = $this->config['telegram-format'];
        $this->message['text'] = $format == 'Markdown'
            ? preg_replace('/([a-z0-9]+)_([a-z0-9]+)/', "$1\_$2", $alert_data['msg'])
            : $alert_data['msg'];

        $this->embedGraphs();

        $base_params['chat_id'] = $this->config['telegram-chat-id'];

        if (! empty($this->config['message-thread-id'])) {
            $base_params['message_thread_id'] = $this->config['message-thread-id'];
        }

        if ($format) {
            $base_params['parse_mode'] = $this->config['telegram-format'];
        }

        if (isset($this->message['images'])) {
            foreach ($this->message['images'] as $image) {
                $mime_type = finfo_buffer(finfo_open(), $image, FILEINFO_MIME_TYPE);
                $file_name = 'default';

                if ($mime_type == 'image/svg+xml') {
                    $file_name = 'graph.svg';
                    $send_mode = 'file';
                }

                if ($mime_type == 'image/png') {
                    $file_name = 'graph.png';
                }

                switch ($send_as) {
                    case 'photo':
                        $res = Http::client()->attach('photo', $image, $file_name)
                            ->post($url_send_photo . '?chat_id=' . $base_params['chat_id']);
                        break;
                    case 'file':
                        $res = Http::client()->attach('document', $image, $file_name)
                            ->post($url_send_file . '?chat_id=' . $base_params['chat_id']);
                        break;
                }
            }
        }

        $params = $base_params;
        $params['text'] = $this->message['text'];

        $res = Http::client()->get($url_send_message, $params);

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException(
            $alert_data,
            $res->status(),
            $res->body(),
            $this->message['text'],
            $params
        );
    }

    private function embedGraphs(): array
    {
        $regex = '#<img class="librenms-graph" src="(.*?)"\s*/>#';

        $this->message['text'] = preg_replace_callback($regex, function ($match) {
            $this->message['images'][] = Graph::getImage($match[1]);

            return '';
        }, $this->message['text']);

        return $this->message;
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
                [
                    'title' => 'Send PNG Graph as',
                    'name' => 'telegram-send-png-graph-mode',
                    'descr' => 'Telegram send graph as, only for PNG graph, SVG will always be sent as file',
                    'type' => 'select',
                    'options' => [
                        'photo' => 'photo',
                        'file' => 'file',
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
