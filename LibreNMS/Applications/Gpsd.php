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
* @copyright  2016 Karl Shea, LibreNMS
* @author     Karl Shea <karl@karlshea.com>
*
*/

namespace LibreNMS\Applications;

use App\Models\Application;

class Gpsd extends ApplicationDefinition
{
    public const string TYPE = 'gpsd';
    protected const ?string DOCS = 'GPSD';
    protected const ?string NAME = 'GPSD';

    protected const ?array OVERVIEW = [
        'satellites',
        'dop',
        'mode',
        'altitude',
        'location',
        'latitude',
        'longitude',
    ];

    protected const array GRAPHS = [
        'satellites' => 'Satellites',
        'dop' => 'Dilution of Precision',
        'mode' => 'Fix type :: 0=unknown, 1=no fix, 2=2D, 3=3D',
    ];

    protected const array LOCATION_GRAPHS = [
        'altitude' => 'Altitude',
        'location' => 'Location',
        'latitude' => 'Latitude',
        'longitude' => 'Longitude',
    ];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        $data = $application->data ?? [];
        parent::buildDevicePage($page, $application);

        if (empty($data['has_location'])) {
            return;
        }

        $page->details['Status'] = match ((int) ($data['mode'] ?? 0)) {
            1 => 'NO FIX',
            2 => '2D',
            3 => '3D',
            default => 'UNKNOWN',
        };
        if (is_numeric($data['altitude'] ?? null)) {
            $page->details['Altitude'] = $data['altitude'] . ' meters';
        }
        if (is_numeric($data['latitude'] ?? null) && is_numeric($data['longitude'] ?? null)) {
            $page->details['Lat / Lng'] = $data['latitude'] . ', ' . $data['longitude'];
        }
        $page->addGraphs(self::LOCATION_GRAPHS);
    }
}
