<?php
/**
 * Discord.php
 *
 * LibreNMS Discord API Tranport
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
 * @copyright  2018 Ryan Finney
 * @author     https://github.com/theherodied/
 * @contributer f0o, sdef2
 * Thanks to F0o <f0o@devilcode.org> for creating the Slack transport which is the majority of this code.
 * Thanks to sdef2 for figuring out the differences needed to make Discord work.
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Proxy;

class Discord extends Transport
{
    public const ALERT_FIELDS_TO_DISCORD_FIELDS = [
        'timestamp' => 'Timestamp',
        'severity' => 'Severity',
        'hostname' => 'Hostname',
        'name' => 'Rule Name',
        'rule' => 'Rule',
    ];

    public function deliverAlert($obj, $opts)
    {
        $discord_opts = [
            'url' => $this->config['url'],
            'options' => $this->parseUserOptions($this->config['options']),
        ];

        return $this->contactDiscord($obj, $discord_opts);
    }

    public function contactDiscord($obj, $discord_opts)
    {
        $host = $discord_opts['url'];
        $curl = curl_init();
        $discord_title = '#' . $obj['uid'] . ' ' . $obj['title'];
        $discord_msg = $obj['msg'];
        $color = hexdec(preg_replace('/[^\dA-Fa-f]/', '', self::getColorForState($obj['state'])));

        // Special handling for the elapsed text in the footer if the elapsed is not set.
        $footer_text = $obj['elapsed'] ? 'alert took ' . $obj['elapsed'] : '';

        $data = [
            'embeds' => [
                [
                    'title' => $discord_title,
                    'color' => $color,
                    'description' => $discord_msg,
                    'fields' => $this->createDiscordFields($obj, $discord_opts),
                    'footer' => [
                        'text' => $footer_text,
                    ],
                ],
            ],
        ];
        if (! empty($discord_opts['options'])) {
            $data = array_merge($data, $discord_opts['options']);
        }

        $data = $this->embedGraphs($data);

        // remove all remaining HTML tags
        $data['embeds'][0]['description'] = strip_tags($data['embeds'][0]['description']);
        $alert_message = json_encode($data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        Proxy::applyToCurl($curl);
        curl_setopt($curl, CURLOPT_URL, $host);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $alert_message);

        $ret = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($code != 204) {
            throw new AlertTransportDeliveryException($obj, $code, $ret, $alert_message, $data);
        }

        return true;
    }

    private function embedGraphs(array $data): array
    {
        $count = 1;
        $data['embeds'][0]['description'] = preg_replace_callback('#<img class="librenms-graph" src="(.*?)" />#', function ($match) use (&$data, &$count) {
            $data['embeds'][] = [
                'image' => [
                    'url' => $match[1],
                ],
            ];

            return '[Image ' . ($count++) . ']';
        }, $data['embeds'][0]['description']);

        return $data;
    }

    public function createDiscordFields($obj, $discord_opts)
    {
        $result = [];

        foreach (self::ALERT_FIELDS_TO_DISCORD_FIELDS as $objKey => $discordKey) {
            // Skip over keys that do not exist so Discord does not give us a 400.
            if (! $obj[$objKey]) {
                continue;
            }

            array_push($result, [
                'name' => $discordKey,
                'value' => $obj[$objKey],
            ]);
        }

        return $result;
    }

    public static function configTemplate()
    {
        return [
            'config' => [
                [
                    'title' => 'Discord URL',
                    'name' => 'url',
                    'descr' => 'Discord URL',
                    'type' => 'text',
                ],
                [
                    'title' => 'Options',
                    'name' => 'options',
                    'descr' => 'Enter the config options (format: option=value separated by new lines)',
                    'type' => 'textarea',
                ],
            ],
            'validation' => [
                'url' => 'required|url',
            ],
        ];
    }
}
