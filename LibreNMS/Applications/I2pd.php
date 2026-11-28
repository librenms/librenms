<?php

/*

LibreNMS Application for I2PD
Application page

@author     Kossusukka <kossusukka@kossulab.net>

LICENSE - GPLv3

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
version 3. See https://www.gnu.org/licenses/gpl-3.0.txt

*/

namespace LibreNMS\Applications;

use App\Models\Application;

class I2pd extends ApplicationDefinition
{
    public const string TYPE = 'i2pd';
    protected const ?string DOCS = 'I2PD';
    protected const ?string NAME = 'I2PD Router';

    protected const ?array OVERVIEW = [
        'uptime',
        'bw_1s',
        'bw_15s',
        'net_status',
        'tunnels_participating',
        'tunnels_successrate',
        'peers',
        'total_bytes',
    ];

    protected const array GRAPHS = [
        'uptime' => 'I2PD - Uptime',
        'total_bytes' => 'I2PD - Data transferred',
        'bw_1s' => 'I2PD - Bandwidth (1s avg)',
        'bw_15s' => 'I2PD - Bandwidth (15s avg)',
        'net_status' => 'I2PD - Network status code',
        'tunnels_participating' => 'I2PD - Tunnels active',
        'tunnels_successrate' => 'I2PD - Tunnel success rate',
        'peers' => 'I2PD - Peers known/active',
    ];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        $code = $application->data['net_stat_code'] ?? null;
        $message = $application->data['net_stat_msg'] ?? '';
        $prefix = match (true) {
            $code >= 8 => 'ERROR: ',
            $code > 1 => 'WARN: ',
            default => '',
        };
        $page->details['Network status'] = $code === null ? 'NO DATA' : "$prefix$message ($code)";
        parent::buildDevicePage($page, $application);
    }
}
