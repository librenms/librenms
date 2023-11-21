<?php
/* Copyright (C) 2014 Daniel Preussker <f0o@devilcode.org>
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>. */

/**
 * API Transport
 *
 * @author f0o <f0o@devilcode.org>
 * @copyright 2014 f0o, LibreNMS
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use Illuminate\Support\Str;
use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Slack extends Transport
{
    public function deliverAlert(array $alert_data): bool
    {
        $slack_opts = $this->parseUserOptions($this->config['slack-options'] ?? '');
        $icon = $this->config['slack-icon_emoji'] ?? $slack_opts['icon_emoji'] ?? null;
        $slack_msg = html_entity_decode(strip_tags($alert_data['msg'] ?? ''), ENT_QUOTES);

        $data = [
            'attachments' => [
                0 => [
                    'fallback' => $slack_msg,
                    'color' => self::getColorForState($alert_data['state']),
                    'title' => $alert_data['title'] ?? null,
                    'text' => $slack_msg,
                    'mrkdwn_in' => ['text', 'fallback'],
                    'author_name' => $this->config['slack-author'] ?? $slack_opts['author'] ?? null,
                ],
            ],
            'channel' => $this->config['slack-channel'] ?? $slack_opts['channel'] ?? null,
            'icon_emoji' => $icon ? Str::finish(Str::start($icon, ':'), ':') : null,
        ];

        $res = Http::client()->post($this->config['slack-url'] ?? '', $data);

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), $slack_msg, $data);
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'Webhook URL',
                    'name' => 'slack-url',
                    'descr' => 'Slack Webhook URL',
                    'type' => 'text',
                ],
                [
                    'title' => 'Channel',
                    'name' => 'slack-channel',
                    'descr' => 'Channel to post to',
                    'type' => 'text',
                ],
                [
                    'title' => 'Author Name',
                    'name' => 'slack-author',
                    'descr' => 'Name of author',
                    'type' => 'text',
                    'default' => 'LibreNMS',
                ],
                [
                    'title' => 'Icon',
                    'name' => 'slack-icon_emoji',
                    'descr' => 'Name of emoji for icon',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'slack-url' => 'required|url',
                'slack-channel' => 'string',
                'slack-author' => 'string',
                'slack-icon_emoji' => 'string',
            ],
        ];
    }
}
