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
 * @author f0o <f0o@devilcode.org>
 * @copyright 2014 f0o, LibreNMS
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Config;

class Mail extends Transport
{
    public function deliverAlert($obj, $opts)
    {
        return $this->contactMail($obj);
    }

    public function contactMail($obj)
    {
        $email = $this->config['email'] ?? $obj['contacts'];
        $msg = preg_replace("/(?<!\r)\n/", "\r\n", $obj['msg']); // fix line returns for windows mail clients

        return send_mail($email, $obj['title'], $msg, (Config::get('email_html') == 'true') ? true : false);
    }

    public static function configTemplate()
    {
        return [
            'config' => [
                [
                    'title' => 'Email',
                    'name' => 'email',
                    'descr' => 'Email address of contact',
                    'type'  => 'text',
                ],
            ],
            'validation' => [
                'email' => 'required|email',
            ],
        ];
    }
}
