<?php

/**
 * EltexMes23xx.php
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
 * @copyright  2022 PipoCanaja
 * @author     PipoCanaja
 * @author     Peca Nesovanovic
 */

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\EntPhysical;
use App\Models\Ipv6Address;
use App\Models\Transceiver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\Exceptions\InvalidIpException;
use LibreNMS\Interfaces\Discovery\Ipv6AddressDiscovery;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\OS;
use LibreNMS\OS\Shared\Radlan;
use LibreNMS\OS\Traits\EntityMib;
use LibreNMS\Util\StringHelpers;
use LibreNMS\Util\IPv6;
use SnmpQuery;

class EltexMes23xx extends Radlan implements TransceiverDiscovery, Ipv6AddressDiscovery
{
    use EntityMib {
        EntityMib::discoverEntityPhysical as discoverBaseEntityPhysical;
    }

    public function discoverEntityPhysical(): Collection
    {
        $inventory = $this->discoverBaseEntityPhysical();

        // add in transceivers
        $trans = SnmpQuery::hideMib()->enumStrings()->cache()->walk('ELTEX-MES-PHYSICAL-DESCRIPTION-MIB::eltPhdTransceiverInfoTable')->table(1);
        $ifIndexToEntIndexMap = array_flip($this->getIfIndexEntPhysicalMap());

        foreach ($trans as $ifIndex => $data) {
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => 1000000 + $ifIndex,
                'entPhysicalDescr' => $data['eltPhdTransceiverInfoType'],
                'entPhysicalClass' => 'sfp-cage',
                'entPhysicalName' => strtoupper($data['eltPhdTransceiverInfoConnectorType']),
                'entPhysicalModelName' => $this->normData($data['eltPhdTransceiverInfoPartNumber']),
                'entPhysicalSerialNum' => $data['eltPhdTransceiverInfoSerialNumber'],
                'entPhysicalContainedIn' => $ifIndexToEntIndexMap[$ifIndex] ?? 0,
                'entPhysicalMfgName' => $data['eltPhdTransceiverInfoVendorName'],
                'entPhysicalHardwareRev' => $this->normData($data['eltPhdTransceiverInfoVendorRev']),
                'entPhysicalParentRelPos' => 0,
                'entPhysicalIsFRU' => 'true',
                'ifIndex' => $ifIndex,
            ]));
        }

        return $inventory;
    }

    public function discoverTransceivers(): Collection
    {
        return SnmpQuery::hideMib()->enumStrings()->cache()->walk('ELTEX-MES-PHYSICAL-DESCRIPTION-MIB::eltPhdTransceiverInfoTable')
            ->mapTable(function ($data, $ifIndex) {
                return new Transceiver([
                    'port_id' => PortCache::getIdFromIfIndex($ifIndex, $this->getDevice()),
                    'index' => $ifIndex,
                    'connector' => $data['eltPhdTransceiverInfoConnectorType'] ? strtoupper($data['eltPhdTransceiverInfoConnectorType']) : null,
                    'distance' => $data['eltPhdTransceiverInfoTransferDistance'] ?? null,
                    'model' => $data['eltPhdTransceiverInfoPartNumber'] ?? null,
                    'revision' => $data['eltPhdTransceiverInfoVendorRev'] ?? null,
                    'serial' => $data['eltPhdTransceiverInfoSerialNumber'] ?? null,
                    'vendor' => $data['eltPhdTransceiverInfoVendorName'] ?? null,
                    'wavelength' => $data['eltPhdTransceiverInfoWaveLength'] ?? null,
                    'entity_physical_index' => $ifIndex,
                ]);
            });
    }

    /**
     * Specific HexToString for Eltex
     */
    protected function normData(string $par = ''): string
    {
        return StringHelpers::isHex($par) ? StringHelpers::hexToAscii($par, ' ') : $par;
    }

    public function discoverIpv6Addresses(): Collection
    {
        $ips = new Collection;

        $ips = $ips->merge(SnmpQuery::enumStrings()->walk([
            'IP-MIB::ipAddressIfIndex.ipv6',
            'RADLAN-IPv6::rlIpAddressTable',
        ])->mapTable(function ($data, $addrType, $address = '') {
            if ($addrType == 'ipv6') {
                try {
                    $ip = IPv6::fromHexString($address);

                    return new Ipv6Address([
                        'ipv6_address' => $ip->uncompressed(),
                        'ipv6_compressed' => $ip->compressed(),
                        'ipv6_prefixlen' => $data['RADLAN-IPv6::rlIpAddressPrefixLength'] ?? '',
                        'ipv6_origin' => $data['RADLAN-IPv6::rlIpAddressType'] ?? 'unknown',
                        'port_id' => PortCache::getIdFromIfIndex($data['IP-MIB::ipAddressIfIndex'], $this->getDevice()),
                    ]);
                } catch (InvalidIpException $e) {
                    Log::error('Failed to parse IP: ' . $e->getMessage());

                    return null;
                }
            }
        }));
        return $ips->filter();
    }
}
