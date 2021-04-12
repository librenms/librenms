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
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;

class Telegram extends Transport
{
    public function deliverAlert($obj, $opts)
    {
        $telegram_opts['chat_id'] = $this->config['telegram-chat-id'];
        $telegram_opts['token'] = $this->config['telegram-token'];
        $telegram_opts['format'] = $this->config['telegram-format'];

        return $this->contactTelegram($obj, $telegram_opts);
    }

    public static function contactTelegram($obj, $data)
    {
        $curl = curl_init();
        set_curl_proxy($curl);
        $text = urlencode($obj['msg']);
        $format = '';
        if ($data['format']) {
            $format = '&parse_mode=' . $data['format'];
            if ($data['format'] == 'Markdown') {
                $text = urlencode(preg_replace('/([a-z0-9]+)_([a-z0-9]+)/', "$1\_$2", $obj['msg']));
            }
        }
        curl_setopt($curl, CURLOPT_URL, ("https://api.telegram.org/bot{$data['token']}/sendMessage?chat_id={$data['chat_id']}&text=$text{$format}"));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $ret = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($code != 200) {
            var_dump('Telegram returned Error'); //FIXME: propper debuging
            var_dump('Return: ' . $ret); //FIXME: propper debuging

            return 'HTTP Status code ' . $code . ', Body ' . $ret;
        }

        return true;
    }

    public static function configTemplate()
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
                    'title' => 'Token',
                    'name' => 'telegram-token',
                    'descr' => 'Telegram Token',
                    'type' => 'text',
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
                'telegram-token' => 'required|string',
                'telegram-format' => 'string',
            ],
        ];
    }
}
