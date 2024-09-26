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
use Ramsey\Uuid\Type\Integer;

class Discord extends Transport
{
    public const DEFAULT_EMBEDS = 'hostname,name,timestamp,severity';
    private array $embedFieldTranslations = [
        'name' => 'Rule Name',
    ];

    /**
     * Composes a Discord JSON message and delivers it using HTTP POST
     * Uses https://discord.com/developers/docs/resources/message#embed-object
     *
     * @param array $alert_data
     * @return bool
     */
    public function deliverAlert(array $alert_data): bool
    {

        $data = [
            'embeds' => [
                [
                    'title' => $this->createTitle($alert_data),
                    'color' => $this->colorOfAlertState($alert_data),
                    'description' => $alert_data['msg'],
                    'fields' => $this->addEmbedFields($alert_data),
                    'footer' => [
                        'text' => $this->createFooter($alert_data),
                    ],
                ],
            ],
        ];

        // options INI fields
        $added_fields = $this->parseUserOptions($this->config['options']);

        // add INI option fields to the message
        if (! empty($added_fields)) {
            $data = array_merge($data, $added_fields);
        }

        //convert html img to json @todo renombrar método
        $data = $this->embedGraphs($data);

        // remove all remaining HTML tags
        $data['embeds'][0]['description'] = strip_tags($data['embeds'][0]['description']);

        $res = Http::client()->post($this->config['url'], $data);

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), $alert_data['msg'], $data);
    }



    private function createTitle(array $alert_data): string
    {
        return '#' . $alert_data['uid'] . ' ' . $alert_data['title'];
    }

    private function colorOfAlertState(array $alert_data): int
    {
        $hexColor = self::getColorForState($alert_data['state']);
        $sanitized = preg_replace('/[^\dA-Fa-f]/', '', $hexColor);
        return hexdec($sanitized);
    }

    private function createFooter(array $alert_data): string
    {
        return $alert_data['elapsed'] ? 'alert took ' . $alert_data['elapsed'] : '';
    }

    /**
     * Convert an html <img src=""> tag to a json Discord message Embed Image Structure
     * https://discord.com/developers/docs/resources/message#embed-object-embed-image-structure
     *
     * @param array $data
     * @return array
     */
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

    /**
     * Converts comma-separated values into an array of name-value pairs.
     * https://discord.com/developers/docs/resources/message#embed-object-embed-field-structure
     *
     * @param array $alert_data Array containing the values.
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
    public function addEmbedFields(array $alert_data): array
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
