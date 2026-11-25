<?php

/*
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
* @package    LibreNMS
* @link       https://www.librenms.org
* @copyright  2016 crcro
* @author     Cercel Valentin <crc@nuamchefazi.ro>
*
*/

namespace LibreNMS\Applications;

class UpsNut extends ApplicationDefinition
{
    public const string TYPE = 'ups-nut';
    protected const ?string DOCS = 'UPS-nut';
    protected const ?string NAME = 'UPS nut';

    protected const array GRAPHS = [
        'remaining' => 'Remaining time:',
        'load' => 'Load:',
        'voltage_battery' => 'Battery voltage:',
        'charge' => 'Charge:',
        'voltage_input' => 'Input voltage:',
    ];
}
