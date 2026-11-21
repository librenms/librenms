<?php

/**
 * rrdcached.inc.php
 *
 * Shows the graphs for rrdcached apps
 *
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
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Applications;

class Rrdcached extends ApplicationDefinition
{
    public const string TYPE = 'rrdcached';
    protected const ?string DOCS = 'RRDCached';
    protected const ?string NAME = 'RRDCached';

    protected const array GRAPHS = [
        'queue_length' => 'Queue Length',
        'events' => 'Events',
        'tree' => 'Tree',
        'journal' => 'Journal',
    ];
}
