<?php

/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage webui
 * @link       https://www.librenms.org
 * @copyright  2017 LibreNMS
 * @author     Cercel Valentin <crc@nuamchefazi.ro>
*/

namespace LibreNMS\Applications;

class PowerdnsDnsdist extends ApplicationDefinition
{
    public const string TYPE = 'powerdns-dnsdist';
    protected const ?string DOCS = 'PowerDNS-dnsdist';
    protected const ?string NAME = 'PowerDNS dnsdist';

    protected const ?array OVERVIEW = [
        'cache',
        'downstream',
        'dynamic_blocks',
        'latency',
        'queries_latency',
        'queries_stats',
        'rules_stats',
        'queries_drop',
    ];

    protected const array GRAPHS = [
        'latency' => 'Latency',
        'cache' => 'Cache',
        'downstream' => 'Downstream servers',
        'dynamic_blocks' => 'Dynamic blocks',
        'rules_stats' => 'Rules stats',
        'queries_stats' => 'Queries stats',
        'queries_latency' => 'Queries latency',
        'queries_drop' => 'Queries drop',
    ];
}
