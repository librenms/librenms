<?php

/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage pi-hole
 * @link       https://www.librenms.org
 * @copyright  2017 LibreNMS
 * @author     crcro <crc@nuamchefazi.ro>
*/

namespace LibreNMS\Applications;

class PiHole extends ApplicationDefinition
{
    public const string TYPE = 'pi-hole';
    protected const ?string DOCS = 'Pi-hole';
    protected const ?string NAME = 'Pi-hole';

    protected const array GRAPHS = [
        'query_types' => 'Query Types',
        'destinations' => 'Destinations',
        'query_results' => 'Query Results',
        'block_percent' => 'Block Percentage',
        'blocklist' => 'Blocklist Domains',
    ];
}
