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
 *
 * @contributer f0o, sdef2
 * Thanks to F0o <f0o@devilcode.org> for creating the Slack transport which is the majority of this code.
 * Thanks to sdef2 for figuring out the differences needed to make Discord work.
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Discord extends Transport
{
    public const DEFAULT_EMBEDS = 'hostname,name,timestamp,severity';
    private array $embedFieldTranslations = [
        'name' => 'Rule Name',
    ];

    public function deliverAlert(array $alert_data): bool
    {
        $added_fields = $this->parseUserOptions($this->config['options']);

        $discord_title = '#' . $alert_data['uid'] . ' ' . $alert_data['title'];
        $discord_msg = $alert_data['msg'];
        $color = hexdec(preg_replace('/[^\dA-Fa-f]/', '', self::getColorForState($alert_data['state'])));

        // Special handling for the elapsed text in the footer if the elapsed is not set.
        $footer_text = $alert_data['elapsed'] ? 'alert took ' . $alert_data['elapsed'] : '';

        $data = [
            'embeds' => [
                [
                    'title' => $discord_title,
                    'color' => $color,
                    'description' => $discord_msg,
                    'fields' => $this->createDiscordFields($alert_data),
                    'footer' => [
                        'text' => $footer_text,
                    ],
                ],
            ],
        ];
        if (! empty($added_fields)) {
            $data = array_merge($data, $added_fields);
        }

        $data = $this->embedGraphs($data);

        // remove all remaining HTML tags
        $data['embeds'][0]['description'] = strip_tags($data['embeds'][0]['description']);

        $res = Http::client()->post($this->config['url'], $data);

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), $discord_msg, $data);
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

    public function createDiscordFields(array $alert_data): array
    {
        $result = [];

        $fields = explode(',', $this->config['discord-embed-fields'] ?? self::DEFAULT_EMBEDS);

        foreach ($fields as $field) {
            $result[] = [
                'name' => $this->embedFieldTranslations[$field] ?? ucfirst($field),
                'value' => $alert_data[$field] ?? 'Error: Invalid Field',
            ];
        }

        return $result;
    }

    public static function configTemplate(): array
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
                [
                    'title' => 'Fields to embed in the alert',
                    'name' => 'discord-embed-fields',
                    'descr' => 'Comma seperated list of fields from the alert to attach to the Discord message',
                    'type' => 'text',
                    'default' => self::DEFAULT_EMBEDS,
                ],
            ],
            'validation' => [
                'url' => 'required|url',
            ],
        ];
    }
}
