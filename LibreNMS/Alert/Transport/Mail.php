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
 * Mail Transport
 *
 * @author f0o <f0o@devilcode.org>
 * @copyright 2014 f0o, LibreNMS
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\AlertUtil;
use LibreNMS\Alert\Transport;
use LibreNMS\Config;

class Mail extends Transport
{
    public function deliverAlert(array $alert_data): bool
    {
        $emails = match ($this->config['mail-contact'] ?? '') {
            'sysContact' => AlertUtil::findContactsSysContact($alert_data['faults']),
            'owners' => AlertUtil::findContactsOwners($alert_data['faults']),
            'role' => AlertUtil::findContactsRoles([$this->config['role']]),
            default => $this->config['email'],
        };

        $html = Config::get('email_html');

        if ($html && ! $this->isHtmlContent($alert_data['msg'])) {
            // if there are no html tags in the content, but we are sending an html email, use br for line returns instead
            $msg = preg_replace("/\r?\n/", "<br />\n", $alert_data['msg']);
        } else {
            // fix line returns for windows mail clients
            $msg = preg_replace("/(?<!\r)\n/", "\r\n", $alert_data['msg']);
        }

        return \LibreNMS\Util\Mail::send($emails, $alert_data['title'], $msg, $html, $this->config['bcc'] ?? false, $this->config['attach-graph'] ?? null);
    }

    public static function configTemplate(): array
    {
        $roles = array_merge(['None' => ''], \Bouncer::role()->pluck('name', 'title')->all());

        return [
            'config' => [
                [
                    'title' => 'Contact Type',
                    'name' => 'mail-contact',
                    'descr' => 'Method for selecting contacts',
                    'type' => 'select',
                    'options' => [
                        'Specified Email' => 'email',
                        'Device sysContact' => 'sysContact',
                        'Owner(s)' => 'owners',
                        'Role' => 'role',
                    ],
                    'default' => 'email',
                ],
                [
                    'title' => 'Email',
                    'name' => 'email',
                    'descr' => 'Email address of contact',
                    'type' => 'text',
                ],
                [
                    'title' => 'Role',
                    'name' => 'role',
                    'descr' => 'Role of users to mail',
                    'type' => 'select',
                    'options' => $roles,
                ],
                [
                    'title' => 'BCC',
                    'name' => 'bcc',
                    'descr' => 'Use BCC instead of TO',
                    'type' => 'checkbox',
                    'default' => false,
                ],
                [
                    'title' => 'Include Graphs',
                    'name' => 'attach-graph',
                    'descr' => 'Include graph image data in the email.  Will be embedded if html5, otherwise attached. Template must use @signedGraphTag',
                    'type' => 'checkbox',
                    'default' => true,
                ],
            ],
            'validation' => [
                'mail-contact' => 'required|in:email,sysContact,owners,role',
                'email' => 'required_if:mail-contact,email|prohibited_unless:mail-contact,email|email',
                'role' => 'required_if:mail-contact,role|prohibited_unless:mail-contact,role|exists:roles,name',
            ],
        ];
    }
}
