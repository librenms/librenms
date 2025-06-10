<?php

/* This program is free software: you can redistribute it and/or modify
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>. */

/**
 * Weechat Bot Transport
 *
 * @author RobJE <epping@renf.us>
 * @copyright 2025 RobJE
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;

class WeechatBot extends Transport
{
    protected string $name = 'Weechat Bot';

    public function deliverAlert(array $alert_data): bool
    {
        $pre = $this->config['bot-password'] . ' ';
        if (isset($this->config['irc-server']) && strlen($this->config['irc-server']) !== 0) {
            $pre = $pre . $this->config['irc-server'] . ' ';
        }
        $pre = $pre . $this->config['irc-channel'] . ' ';

        // https://www.php.net/manual/en/function.fsockopen.php example #2
        $fp = fsockopen('udp://' . $this->config['bot-hostname'], $this->config['bot-port'], $errno, $errstr);

        if (! $fp) {
            throw new AlertTransportDeliveryException($alert_data, $errno, $errstr);
        } else {
            fwrite($fp, $pre . $alert_data['title']);
            foreach (preg_split('/((\r?\n)|(\r\n?))/', $alert_data['msg']) as $line) {
                fwrite($fp, $pre . $line);
            }
            fclose($fp);
        }

        return true;
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'Weechat Bot server',
                    'name' => 'bot-hostname',
                    'descr' => 'hostname or IP address of Weechat Bot server',
                    'type' => 'text',
                ],
                [
                    'title' => 'Weechat Bot port',
                    'name' => 'bot-port',
                    'descr' => 'port Weechat Bot server listens',
                    'type' => 'text',
                ],
                [
                    'title' => 'UDP listener Password',
                    'name' => 'bot-password',
                    'descr' => 'Weechat Bot UDP listener password',
                    'type' => 'password',
                ],
                [
                    'title' => 'IRC server',
                    'name' => 'irc-server',
                    'descr' => 'IRC server to send alert',
                    'type' => 'text',
                ],
                [
                    'title' => 'IRC channel',
                    'name' => 'irc-channel',
                    'descr' => 'IRC channel to send alert',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'bot-hostname' => 'required|string',
                'bot-port' => 'required|integer|between:1,65535',
                'bot-password' => 'required|string',
                'irc-server' => 'string',
                'irc-channel' => 'required|string',
            ],
        ];
    }
}
