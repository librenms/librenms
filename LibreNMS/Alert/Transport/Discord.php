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
    private array $embedFieldTranslations = [
        'name' => 'Rule Name',
    ];

    private array $discord_message = [];

    /**
     * Composes a Discord JSON message and delivers it using HTTP POST
     * https://discord.com/developers/docs/resources/message#create-message
     *
     * @param  array  $alert_data
     * @return bool
     */
    public function deliverAlert(array $alert_data): bool
    {
        $this->discord_message = [
            'embeds' => [
                [
                    'title' => $this->getTitle($alert_data),
                    'color' => $this->getColorOfAlertState($alert_data),
                    'description' => $this->getDescription($alert_data),
                    'fields' => $this->getEmbedFields($alert_data),
                    'footer' => [
                        'text' => $this->getFooter($alert_data),
                    ],
                ],
            ],
        ];

        $this->includeINIFields();
        $this->embedGraphs();
        $this->stripHTMLTagsFromDescription();

        $res = Http::client()->post($this->config['url'], $this->discord_message);

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), $alert_data['msg'], $this->discord_message);
    }

    private function getTitle(array $alert_data): string
    {
        return '#' . $alert_data['uid'] . ' ' . $alert_data['title'];
    }

    private function stripHTMLTagsFromDescription(): array
    {
        $this->discord_message['embeds'][0]['description'] = strip_tags($this->discord_message['embeds'][0]['description']);

        return $this->discord_message;
    }

    private function getColorOfAlertState(array $alert_data): int
    {
        $hexColor = self::getColorForState($alert_data['state']);
        $sanitized = preg_replace('/[^\dA-Fa-f]/', '', $hexColor);

        return hexdec($sanitized);
    }

    private function getDescription(array $alert_data): string
    {
        return $alert_data['msg'];
    }

    private function getFooter(array $alert_data): string
    {
        return $alert_data['elapsed'] ? 'alert took ' . $alert_data['elapsed'] : '';
    }

    private function includeINIFields(): array
    {
        $ini_fileds = $this->parseUserOptions($this->config['options']);

        if (! empty($ini_fileds)) {
            $this->discord_message = array_merge($this->discord_message, $ini_fileds);
        }

        return $this->discord_message;
    }

    /**
     * Convert an html <img src=""> tag to a json Discord message Embed Image Structure
     * https://discord.com/developers/docs/resources/message#embed-object-embed-image-structure
     *
     * @return array
     */
    private function embedGraphs(): array
    {
        $regex = '#<img class="librenms-graph" src="(.*?)"\s*/>#';
        $count = 1;

        $this->discord_message['embeds'][0]['description'] = preg_replace_callback($regex, function ($match) use (&$count) {
            $this->discord_message['embeds'][] = [
                'image' => [
                    'url' => $match[1],
                ],
            ];

            return '[Image ' . ($count++) . ']';
        }, $this->discord_message['embeds'][0]['description']);

        return $this->discord_message;
    }

    /**
     * Converts comma-separated values into an array of name-value pairs.
     * https://discord.com/developers/docs/resources/message#embed-object-embed-field-structure
     *
     * * @param  array  $alert_data  Array containing the values.
     * @return array An array of name-value pairs.
     *
     * @example
     * Example with 'hostname,sysDescr' as fields:
     * $result will be:
     * [
     *     ['name' => 'Hostname', 'value' => 'server1'],
     *     ['name' => 'SysDescr', 'value' => 'Linux server description'],
     * ]
     */
    public function getEmbedFields(array $alert_data): array
    {
        $result = [];

        if (empty($this->config['discord-embed-fields'])) {
            return $result;
        }

        $fields = explode(',', $this->config['discord-embed-fields']);

        foreach ($fields as $field) {
            $field = trim($field);

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
                    'descr' => 'Comma seperated list from the alert to embed i.e. hostname,name,timestamp,severity',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'url' => 'required|url',
            ],
        ];
    }
}
