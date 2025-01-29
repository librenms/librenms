<?php
/**
 * Exa.php
 *
 * Calix EXA OS
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\Device;
use App\Models\Transceiver;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\OS;
use SnmpQuery;

class Exa extends OS implements OSDiscovery, TransceiverDiscovery
{
    public function discoverOS(Device $device): void
    {
        $response = SnmpQuery::next([
            'E7-Calix-MIB::e7CardSoftwareVersion',
            'E7-Calix-MIB::e7CardSerialNumber',
        ]);

        $device->version = $response->value('E7-Calix-MIB::e7CardSoftwareVersion') ?: null;
        $device->serial = $response->value('E7-Calix-MIB::e7CardSerialNumber') ?: null;
        $device->hardware = 'Calix ' . $device->sysDescr;

        $cards = SnmpQuery::enumStrings()->walk('E7-Calix-MIB::e7CardProvType')->values();
        $card_count = [];
        foreach ($cards as $card) {
            $card_count[$card] = ($card_count[$card] ?? 0) + 1;
        }
        $device->features = implode(', ', array_map(function ($card) use ($card_count) {
            return ($card_count[$card] > 1 ? $card_count[$card] . 'x ' : '') . $card;
        }, array_keys($card_count)));
    }

    public function discoverTransceivers(): Collection
    {
        return \SnmpQuery::cache()->walk('E7-Calix-MIB::e7OltPonPortTable')->mapTable(function ($data, $shelf, $card, $port) {
            if ($data['E7-Calix-MIB::e7OltPonPortStatus'] == 0) {
                return null;
            }

            $ifIndex = self::getIfIndex($shelf, $card, $port, 'gpon');

            return new Transceiver([
                'port_id' => (int) PortCache::getIdFromIfIndex($ifIndex, $this->getDevice()),
                'index' => "$shelf.$card.$port",
                'entity_physical_index' => $ifIndex,
            ]);
        })->filter();
    }

    public static function getIfIndex(int $chassis, int $slot, int $id, string $type): int
    {
        // doesn't work for stacked chassis, I don't have enough info to figure out how it works
        $offset = match ($type) {
            'gpon' => 20000,
            default => 0,
        };

        return $offset + (10000 * $chassis) + ($slot * 100) + $id;
    }
}
