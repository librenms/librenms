<?php

/**
 * Zxa10.php
 *
 * ZTE ZXA10 GPON OLT transceiver discovery.
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
 */

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\Transceiver;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use SnmpQuery;

class Zxa10 extends \LibreNMS\OS implements TransceiverDiscovery
{
    /**
     * ZXA10 exposes optical module data through two mutually exclusive tables.
     * C620/C650/C650E use zxAnOpticalModuleInfoTable and report the full SFF
     * inventory. C300 uses the older zxAnOpticalModuleMonTable, which carries
     * only the vendor, part number, wavelength and fibre type.
     *
     * C300 answers on both branches, but the newer one is indexed with an
     * ifIndex numbering that does not match the ports, so rows without a
     * matching port are dropped.
     *
     * @return Collection<int, Transceiver>
     */
    public function discoverTransceivers(): Collection
    {
        $transceivers = $this->discoverInfoTable();

        return $transceivers->isNotEmpty() ? $transceivers : $this->discoverMonTable();
    }

    /**
     * @return Collection<int, Transceiver>
     */
    private function discoverInfoTable(): Collection
    {
        return SnmpQuery::cache()->walk('ZTE-AN-OPTICAL-MODULE-MIB::zxAnOpticalModuleInfoTable')
            ->mapTable(function ($data, $ifIndex) {
                $portId = PortCache::getIdFromIfIndex($ifIndex, $this->getDevice());
                $vendor = $this->clean($data['ZTE-AN-OPTICAL-MODULE-MIB::zxAnOpticalVendorName'] ?? null);
                $model = $this->clean($data['ZTE-AN-OPTICAL-MODULE-MIB::zxAnOpticalVendorPn'] ?? null);

                if (! $portId || ($vendor === null && $model === null)) {
                    return null; // no such port, or an empty cage
                }

                return new Transceiver([
                    'port_id' => (int) $portId,
                    'index' => $ifIndex,
                    'type' => $this->clean($data['ZTE-AN-OPTICAL-MODULE-MIB::zxAnOpticalModuleType'] ?? null),
                    'vendor' => $vendor,
                    'model' => $model,
                    'revision' => $this->clean($data['ZTE-AN-OPTICAL-MODULE-MIB::zxAnOpticalVersionLevel'] ?? null),
                    'serial' => $this->clean($data['ZTE-AN-OPTICAL-MODULE-MIB::zxAnOpticalVendorSn'] ?? null),
                    'connector' => $this->clean($data['ZTE-AN-OPTICAL-MODULE-MIB::zxAnOpticalFiberInterfaceType'] ?? null),
                    'cable' => $this->fibreType($data['ZTE-AN-OPTICAL-MODULE-MIB::zxAnOpticalFiberType'] ?? null),
                    'wavelength' => $this->sane($data['ZTE-AN-OPTICAL-MODULE-MIB::zxAnOpticalWavelength'] ?? null),
                    'ddm' => 1,
                    'entity_physical_index' => $ifIndex,
                ]);
            })->filter();
    }

    /**
     * @return Collection<int, Transceiver>
     */
    private function discoverMonTable(): Collection
    {
        return SnmpQuery::cache()->walk('ZTE-AN-INTERFACE-MIB::zxAnOpticalModuleMonTable')
            ->mapTable(function ($data, $ifIndex) {
                $portId = PortCache::getIdFromIfIndex($ifIndex, $this->getDevice());
                $vendor = $this->clean($data['ZTE-AN-INTERFACE-MIB::zxAnOpticalVenderName'] ?? null);
                $model = $this->clean($data['ZTE-AN-INTERFACE-MIB::zxAnOpticalVenderPn'] ?? null);

                if (! $portId || ($vendor === null && $model === null)) {
                    return null;
                }

                // this platform has no module type string, only a line rate in 0.1 Gbps
                $rate = $this->sane($data['ZTE-AN-INTERFACE-MIB::zxAnOpticalIfRate'] ?? null);

                return new Transceiver([
                    'port_id' => (int) $portId,
                    'index' => $ifIndex,
                    'type' => $rate ? rtrim(rtrim(sprintf('%.1f', $rate / 10), '0'), '.') . 'G' : null,
                    'vendor' => $vendor,
                    'model' => $model,
                    'cable' => $this->fibreType($data['ZTE-AN-INTERFACE-MIB::zxAnOpticalFiberType'] ?? null),
                    'wavelength' => $this->sane($data['ZTE-AN-INTERFACE-MIB::zxAnOpticalWavelength'] ?? null),
                    'ddm' => 1,
                    'entity_physical_index' => $ifIndex,
                ]);
            })->filter();
    }

    /**
     * ZXA10 pads these fields to a fixed width with spaces followed by non
     * printable bytes, and an empty cage contains nothing else. Depending on the
     * agent, those bytes are rendered either as dots or as a "00 00 00 00" hex
     * run, so both tails are trimmed. A legitimate single trailing dot survives,
     * as in "SUPERXON LTD.".
     */
    private function clean(mixed $value): ?string
    {
        $text = preg_replace('/[^\x20-\x7e]/', '', (string) $value);
        $text = preg_replace('/\s*00(?:[\s:]+00)+$/', '', $text);
        $text = trim(preg_replace('/(?:\s+\.+|\.{2,})$/', '', $text));

        return ($text === '' || preg_match('/^[.\s]+$/', $text)) ? null : $text;
    }

    /**
     * ZXA10 returns 0x7fffffff for a value the module does not support.
     */
    private function sane(mixed $value): ?int
    {
        return (is_numeric($value) && (int) $value !== 2147483647) ? (int) $value : null;
    }

    private function fibreType(mixed $value): ?string
    {
        return match ((int) $value) {
            1 => 'SM',
            2 => 'MM',
            default => null,
        };
    }
}
