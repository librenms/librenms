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
 * IRC Transport
 * @author f0o <f0o@devilcode.org>
 * @copyright 2014 f0o, LibreNMS
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Config;

class Irc extends Transport
{
    public function deliverAlert($obj, $opts)
    {
        return $this->contactIrc($obj, $opts);
    }

    public function contactIrc($obj, $opts)
    {
        $f = Config::get('install_dir') . '/.ircbot.alert';
        if (file_exists($f) && filetype($f) == 'fifo') {
            $f = fopen($f, 'w+');
            $r = fwrite($f, json_encode($obj) . "\n");
            $f = fclose($f);
            if ($r === false) {
                return false;
            } else {
                return true;
            }
        }

        return false;
    }

    public static function configTemplate()
    {
        return [
            'config' => [
                [
                    'title' => 'IRC',
                    'name' => 'irc',
                    'descr' => 'Enable IRC alerts',
                    'type'  => 'checkbox',
                    'default' => true,
                ],
            ],
            'validation' => [
                'irc' => 'required',
            ],
        ];
    }
}
