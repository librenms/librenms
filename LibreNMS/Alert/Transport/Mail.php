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
        $html = Config::get('email_html');

        if ($html && ! $this->isHtmlContent($obj['msg'])) {
            // if there are no html tags in the content, but we are sending an html email, use br for line returns instead
            $msg = preg_replace("/\r?\n/", "<br />\n", $obj['msg']);
        } else {
            // fix line returns for windows mail clients
            $msg = preg_replace("/(?<!\r)\n/", "\r\n", $obj['msg']);
        }

        return \LibreNMS\Util\Mail::send($email, $obj['title'], $msg, $html, $this->config['attach-graph'] ?? null);
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
                [
                    'title' => 'Include Graphs',
                    'name' => 'attach-graph',
                    'descr' => 'Include graph image data in the email.  Will be embedded if html5, otherwise attached. Template must use @signedGraphTag',
                    'type' => 'checkbox',
                    'default' => true,
                ],
            ],
            'validation' => [
                'email' => 'required|email',
            ],
        ];
    }
}
