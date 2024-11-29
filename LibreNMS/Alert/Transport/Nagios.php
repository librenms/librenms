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
 * Nagios Transport
 *
 * @author f0o <f0o@devilcode.org>
 * @copyright 2014 f0o, LibreNMS
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;

class Nagios extends Transport
{
    public function deliverAlert(array $alert_data): bool
    {
        /*
         host_perfdata_file_template=
         [HOSTPERFDATA]\t
         $TIMET$\t
         $HOSTNAME$\t
         HOST\t
         $HOSTSTATE$\t
         $HOSTEXECUTIONTIME$\t
         $HOSTLATENCY$\t
         $HOSTOUTPUT$\t
         $HOSTPERFDATA$
         */

        $format = '';
        $format .= "[HOSTPERFDATA]\t";
        $format .= strtotime($alert_data['timestamp']) . "\t";
        $format .= $alert_data['hostname'] . "\t";
        $format .= md5($alert_data['rule']) . "\t"; //FIXME: Better entity
        $format .= ($alert_data['state'] ? $alert_data['severity'] : 'ok') . "\t";
        $format .= "0\t";
        $format .= "0\t";
        $format .= str_replace("\n", '', nl2br($alert_data['msg'])) . "\t";
        $format .= 'NULL'; //FIXME: What's the HOSTPERFDATA equivalent for LibreNMS? Oo
        $format .= "\n";

        $fifo = $this->config['nagios-fifo'];
        if (filetype($fifo) !== 'fifo') {
            throw new AlertTransportDeliveryException($alert_data, 0, 'File is not a fifo file! Refused to write to it.');
        }

        return file_put_contents($fifo, $format);
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'Nagios FIFO',
                    'name' => 'nagios-fifo',
                    'descr' => 'Nagios compatible FIFO',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'nagios-fifo' => 'required',
            ],
        ];
    }
}
