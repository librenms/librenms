<?php

/*
 * Copyright (C) 2015 Daniel Preussker <f0o@devilcode.org>
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/*
 * TinyDNS Application
 * @author Daniel Preussker <f0o@devilcode.org>
 * @copyright 2015 f0o, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Apps
 */

namespace LibreNMS\Applications;

class Tinydns extends ApplicationDefinition
{
    public const string TYPE = 'tinydns';
    protected const ?string DOCS = 'TinyDNS aka djbdns';

    protected const array GRAPHS = [
        'queries' => 'Queries',
        'errors' => 'Errors',
        'dnssec' => 'DNSSec',
        'other' => 'Other',
    ];
}
