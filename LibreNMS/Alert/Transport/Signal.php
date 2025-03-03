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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>. */

/**
 * Signal Transport
 *
 * @author kzink <kevin.zink@mpi-hd.mpg.de>
 * @copyright 2021 kzink, LibreNMS
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;

class Signal extends Transport
{
    public function deliverAlert(array $alert_data): bool
    {
        exec(escapeshellarg($this->config['path'])
           . ' --dbus-system send'
           . (($this->config['recipient-type'] == 'group') ? ' -g ' : ' ')
           . escapeshellarg($this->config['recipient'])
           . ' -m ' . escapeshellarg($alert_data['title']));

        return true;
    }

    public static function configTemplate(): array
    {
        return [
            'validation' => [],
            'config' => [
                [
                    'title' => 'Path',
                    'name' => 'path',
                    'descr' => 'Local Path to CLI',
                    'type' => 'text',
                ],
                [
                    'title' => 'Recipient type',
                    'name' => 'recipient-type',
                    'descr' => 'Phonenumber ',
                    'type' => 'select',
                    'options' => [
                        'Mobile number' => 'single',
                        'Group' => 'group',
                    ],
                ],
                [
                    'title' => 'Recipient',
                    'name' => 'recipient',
                    'descr' => 'Message recipient',
                    'type' => 'text',
                ],
            ],
        ];
    }
}
