<?php

/*
 * Jetdirect.php
 *
 * -Description-
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
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\Interfaces\PrinterSuppliesContext;
use LibreNMS\Util\StringHelpers;
use SnmpQuery;

class Jetdirect extends Shared\Printer implements PrinterSuppliesContext
{
    public function getPrinterSuppliesContexts(): array
    {
        return ['', 'Jetdirect'];
    }

    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml
        $device = $this->getDevice();

        // subclasses (e.g. okilan) may already have set features from their own yaml
        if ($device->features === null) {
            $mio = SnmpQuery::get([
                'HP-LASERJET-COMMON-MIB::mio1-manufacturing-info.0',
                'HP-LASERJET-COMMON-MIB::mio2-manufacturing-info.0',
                'HP-LASERJET-COMMON-MIB::mio3-manufacturing-info.0',
                'HP-LASERJET-COMMON-MIB::mio4-manufacturing-info.0',
            ])->values();
            $device->features = collect($mio)
                ->map(self::parseMioInfo(...))
                ->first(fn ($info) => $info !== null);
        }

        $jetdirect_id = SnmpQuery::get('HP-LASERJET-COMMON-MIB::gdStatusId.0')->value()
            ?: SnmpQuery::context('Jetdirect')->get('HP-LASERJET-COMMON-MIB::gdStatusId.0')->value();
        $info = $this->parseDeviceId($jetdirect_id);

        $hardware = $info['MDL'] ?? $info['MODEL'] ?? $info['DES'] ?? $info['DESCRIPTION'] ?? null;
        if (! empty($hardware)) {
            $hardware = str_ireplace([
                'HP ',
                'Hewlett-Packard ',
                ' Series',
            ], '', $hardware);
            $device->hardware = ucfirst($hardware);
        }
    }

    /**
     * Text of an HP-LASERJET-COMMON-MIB mio*-manufacturing-info value, or null if it holds no readable text.
     * Some firmwares (e.g. Color LaserJet MFP M480) fill it with '?' placeholders and a few bytes that change
     * on every query, which would otherwise be logged as an OS features change on each discovery.
     */
    public static function parseMioInfo(string $value): ?string
    {
        $text = (string) StringHelpers::inferEncoding(StringHelpers::decodeSnmpHexText($value));

        // strings in this MIB start with a 2 byte symbol set (0x0115 is Roman-8), see the MIB header
        $text = (string) preg_replace('/^[^\x20-\x7E]./su', '', $text);

        return preg_match('/^[\x20-\x7E]+$/', $text) && ! str_contains($text, '??') ? $text : null;
    }
}
