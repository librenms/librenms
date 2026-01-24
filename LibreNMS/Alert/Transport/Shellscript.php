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
 * @author shrank <info@murxs.ch>
 * @copyright 2025 shrank, LibreNMS
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use App\View\SimpleTemplate;
use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;

class Shellscript extends Transport
{
    protected string $name = 'Shell Script';

    public function deliverAlert(array $alert_data): bool
    {
        $cli = SimpleTemplate::parse($this->config['shellscript-cli'], $alert_data);

        $output = exec($cli, $output, $res_code);

        if($res_code > 0) {
            throw new AlertTransportDeliveryException($alert_data, $res_code, $cli . "\n" . $output, $cli );
        }
        return true;
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'Command',
                    'name' => 'shellscript-cli',
                    'descr' => 'Shell command with arguments ',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'shellscript-cli' => 'required',
            ],
        ];
    }
}
