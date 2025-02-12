<?php
/**
 * Timos.php
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
 * @link       https://www.librenms.org
 *
 * @copyright  2019 Vitali Kari
 * @copyright  2019 Tony Murray
 * @author     Vitali Kari <vitali.kari@gmail.com>
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use App\Models\EntPhysical;
use App\Models\MplsLsp;
use App\Models\MplsLspPath;
use App\Models\MplsSap;
use App\Models\MplsSdp;
use App\Models\MplsSdpBind;
use App\Models\MplsService;
use App\Models\MplsTunnelArHop;
use App\Models\MplsTunnelCHop;
use Illuminate\Support\Collection;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Exceptions\InvalidIpException;
use LibreNMS\Interfaces\Discovery\MplsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessChannelDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRsrpDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRsrqDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\Interfaces\Polling\MplsPolling;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\IP;
use SnmpQuery;

class Timos extends OS implements MplsDiscovery, MplsPolling, WirelessPowerDiscovery, WirelessSnrDiscovery, WirelessRsrqDiscovery, WirelessRssiDiscovery, WirelessRsrpDiscovery, WirelessChannelDiscovery
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml

        $hardware_index = SnmpQuery::get('TIMETRA-CHASSIS-MIB::tmnxChassisType.1')->value();
        $device->hardware = SnmpQuery::get("TIMETRA-CHASSIS-MIB::tmnxChassisTypeName.$hardware_index")->value();

        // find physical chassis and fetch the serial for it
        $hw = SnmpQuery::enumStrings()->walk('TIMETRA-CHASSIS-MIB::tmnxHwClass')->pluck();
        foreach ($hw as $index => $class) {
            if ($class == 'physChassis') {
                $device->serial = SnmpQuery::get("TIMETRA-CHASSIS-MIB::tmnxHwSerialNumber.$index")->value();

                return;
            }
        }
    }

    /**
     * Discover wireless Rx & Tx (Signal Strength). This is in dBm. Type is power.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     * ALU-MICROWAVE-MIB::aluMwRadioLocalRxMainPower
     * ALU-MICROWAVE-MIB::aluMwRadioLocalTxPower
     *
     * @return array
     */
    public function discoverWirelessPower(): array
    {
        $snmp = SnmpQuery::walk([
            'ALU-MICROWAVE-MIB::aluMwRadioName',
            'ALU-MICROWAVE-MIB::aluMwRadioLocalRxMainPower',
            'ALU-MICROWAVE-MIB::aluMwRadioLocalTxPower',
        ])->valuesByIndex();

        $sensors = [];
        $divisor = 10;

        foreach ($snmp as $index => $data) {
            if (isset($data['ALU-MICROWAVE-MIB::aluMwRadioLocalRxMainPower'])) {
                $sensors[] = new WirelessSensor(
                    'power',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.6527.6.1.2.2.7.1.3.1.2.' . $index,
                    'Nokia-Packet-MW-Rx',
                    $index,
                    "Rx ({$data['ALU-MICROWAVE-MIB::aluMwRadioName']})",
                    $data['ALU-MICROWAVE-MIB::aluMwRadioLocalRxMainPower'] / $divisor,
                    1,
                    $divisor
                );
            }
        }

        foreach ($snmp as $index => $data) {
            if (isset($data['ALU-MICROWAVE-MIB::aluMwRadioLocalTxPower'])) {
                $sensors[] = new WirelessSensor(
                    'power',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.6527.6.1.2.2.7.1.3.1.1.' . $index,
                    'Nokia-Packet-MW-Tx',
                    $index,
                    "Tx ({$data['ALU-MICROWAVE-MIB::aluMwRadioName']})",
                    $data['ALU-MICROWAVE-MIB::aluMwRadioLocalTxPower'] / $divisor,
                    1,
                    $divisor
                );
            }
        }

        return $sensors;
    }

    /**
     * @param  mixed  $tmnxEncapVal
     * @return string encapsulation
     *
     * @see TIMETRA-TC-MIB::TmnxEncapVal
     */
    private function nokiaEncap($tmnxEncapVal)
    {
        // implement other encapsulation values
        $map = sprintf('%032b', $tmnxEncapVal);

        if (substr($map, -32, 20) == '00000000000000000000') { // 12-bit IEEE 802.1Q VLAN ID
            if ($tmnxEncapVal == 4095) {
                return '*';
            }
        }

        return $tmnxEncapVal;
    }

    /**
     * @return Collection MplsLsp objects
     */
    public function discoverMplsLsps(): Collection
    {
        return SnmpQuery::hideMib()->abortOnFailure()->walk([
            'TIMETRA-MPLS-MIB::vRtrMplsLspTable',
            'TIMETRA-MPLS-MIB::vRtrMplsLspLastChange',
        ])->mapTable(function ($value, $vrf_oid, $lsp_oid) {
            return new MplsLsp([
                'vrf_oid' => $vrf_oid,
                'lsp_oid' => $lsp_oid,
                'device_id' => $this->getDeviceId(),
                'mplsLspRowStatus' => $value['vRtrMplsLspRowStatus'] ?? null,
                'mplsLspLastChange' => round(($value['vRtrMplsLspLastChange'] ?? 0) / 100),
                'mplsLspName' => $value['vRtrMplsLspName'] ?? null,
                'mplsLspAdminState' => $value['vRtrMplsLspAdminState'] ?? null,
                'mplsLspOperState' => $value['vRtrMplsLspOperState'] ?? null,
                'mplsLspFromAddr' => $this->parseIpField($value, 'vRtrMplsLspNgFromAddr'),
                'mplsLspToAddr' => $this->parseIpField($value, 'vRtrMplsLspNgToAddr'),
                'mplsLspType' => $value['vRtrMplsLspType'] ?? null,
                'mplsLspFastReroute' => $value['vRtrMplsLspFastReroute'] ?? null,
            ]);
        });
    }

    /**
     * @param  Collection  $lsps  collecton of synchronized lsp objects from discoverMplsLsps()
     * @return Collection MplsLspPath objects
     */
    public function discoverMplsPaths($lsps): Collection
    {
        return SnmpQuery::hideMib()->enumStrings()->abortOnFailure()->walk([
            'TIMETRA-MPLS-MIB::vRtrMplsLspPathTable',
            'TIMETRA-MPLS-MIB::vRtrMplsLspPathLastChange',
        ])->mapTable(function ($value, $vrf_oid, $lsp_oid, $path_oid) use ($lsps) {
            $lsp_id = $lsps->where('lsp_oid', $lsp_oid)->firstWhere('vrf_oid', $vrf_oid)->lsp_id;

            return new MplsLspPath([
                'lsp_id' => $lsp_id,
                'path_oid' => $path_oid,
                'device_id' => $this->getDeviceId(),
                'mplsLspPathRowStatus' => $value['vRtrMplsLspPathRowStatus'] ?? null,
                'mplsLspPathLastChange' => round(($value['vRtrMplsLspPathLastChange'] ?? 0) / 100),
                'mplsLspPathType' => $value['vRtrMplsLspPathType'] ?? null,
                'mplsLspPathBandwidth' => $value['vRtrMplsLspPathBandwidth'] ?? null,
                'mplsLspPathOperBandwidth' => $value['vRtrMplsLspPathOperBandwidth'] ?? null,
                'mplsLspPathAdminState' => $value['vRtrMplsLspPathAdminState'] ?? null,
                'mplsLspPathOperState' => $value['vRtrMplsLspPathOperState'] ?? null,
                'mplsLspPathState' => $value['vRtrMplsLspPathState'] ?? null,
                'mplsLspPathFailCode' => $value['vRtrMplsLspPathFailCode'] ?? null,
                'mplsLspPathFailNodeAddr' => $value['vRtrMplsLspPathFailNodeAddr'] ?? null,
                'mplsLspPathMetric' => $value['vRtrMplsLspPathMetric'] ?? null,
                'mplsLspPathOperMetric' => $value['vRtrMplsLspPathOperMetric'] ?? null,
                'mplsLspPathTunnelARHopListIndex' => $value['vRtrMplsLspPathTunnelARHopListIndex'] ?? null,
                'mplsLspPathTunnelCHopListIndex' => $value['vRtrMplsLspPathTunnelCRHopListIndex'] ?? null,
            ]);
        });
    }

    /**
     * @return Collection MplsSdp objects
     */
    public function discoverMplsSdps(): Collection
    {
        return SnmpQuery::hideMib()->enumStrings()->walk('TIMETRA-SDP-MIB::sdpInfoTable')->mapTable(function ($value) {
            return new MplsSdp([
                'sdp_oid' => $value['sdpId'],
                'device_id' => $this->getDeviceId(),
                'sdpRowStatus' => $value['sdpRowStatus'] ?? null,
                'sdpDelivery' => $value['sdpDelivery'] ?? null,
                'sdpDescription' => $value['sdpDescription'] ?? null,
                'sdpAdminStatus' => $value['sdpAdminStatus'] ?? null,
                'sdpOperStatus' => $value['sdpOperStatus'] ?? null,
                'sdpAdminPathMtu' => $value['sdpAdminPathMtu'] ?? null,
                'sdpOperPathMtu' => $value['sdpOperPathMtu'] ?? null,
                'sdpLastMgmtChange' => round(($value['sdpLastMgmtChange'] ?? 0) / 100),
                'sdpLastStatusChange' => round(($value['sdpLastStatusChange'] ?? 0) / 100),
                'sdpActiveLspType' => $value['sdpActiveLspType'] ?? null,
                'sdpFarEndInetAddressType' => $value['sdpFarEndInetAddressType'] ?? null,
                'sdpFarEndInetAddress' => IP::fromHexString($value['sdpFarEndInetAddress'], true),
            ]);
        });
    }

    /**
     * @return Collection MplsService objects
     */
    public function discoverMplsServices(): Collection
    {
        return SnmpQuery::hideMib()->enumStrings()->abortOnFailure()->walk([
            'TIMETRA-SERV-MIB::svcBaseInfoTable',
            'TIMETRA-SERV-MIB::svcTlsInfoTable',
        ])->mapTable(function ($value) {
            // Workaround, remove some default entries we do not want to see
            if (preg_match('/^\w* Service for internal purposes only/', $value['svcDescription'])) {
                return null;
            }

            return new MplsService([
                'svc_oid' => $value['svcId'],
                'device_id' => $this->getDeviceId(),
                'svcRowStatus' => $value['svcRowStatus'] ?? null,
                'svcType' => $value['svcType'] ?? null,
                'svcCustId' => $value['svcCustId'] ?? null,
                'svcAdminStatus' => $value['svcAdminStatus'] ?? null,
                'svcOperStatus' => $value['svcOperStatus'] ?? null,
                'svcDescription' => $value['svcDescription'] ?? null,
                'svcMtu' => $value['svcMtu'] ?? null,
                'svcNumSaps' => $value['svcNumSaps'] ?? null,
                'svcNumSdps' => $value['svcNumSdps'] ?? null,
                'svcLastMgmtChange' => round(($value['svcLastMgmtChange'] ?? 0) / 100),
                'svcLastStatusChange' => round(($value['svcLastStatusChange'] ?? 0) / 100),
                'svcVRouterId' => $value['svcVRouterId'] ?? null,
                'svcTlsMacLearning' => $value['svcTlsMacLearning'] ?? null,
                'svcTlsStpAdminStatus' => $value['svcTlsStpAdminStatus'] ?? null,
                'svcTlsStpOperStatus' => $value['svcTlsStpOperStatus'] ?? null,
                'svcTlsFdbTableSize' => $value['svcTlsFdbTableSize'] ?? null,
                'svcTlsFdbNumEntries' => $value['svcTlsFdbNumEntries'] ?? null,
            ]);
        })->filter();
    }

    /**
     * @return Collection MplsSap objects
     */
    public function discoverMplsSaps($svcs): Collection
    {
        return SnmpQuery::hideMib()->enumStrings()->abortOnFailure()->walk([
            'TIMETRA-SAP-MIB::sapBaseInfoTable',
            'TIMETRA-SAP-MIB::sapBaseStatsTable',
        ])->mapTable(function ($value, $svcId, $sapPortId, $sapEncapValue) use ($svcs) {
            // Workaround, there are some oids not covered by actual MIB, try to filter them
            // i.e. sapBaseInfoEntry.300.118208001.1342177283.10
            if (! isset($value['sapDescription'])) {
                return null;
            }

            // remove some default entries we do not want to see
            if (str_starts_with($value['sapDescription'], 'Internal SAP')) {
                return null;
            }

            return new MplsSap([
                'svc_id' => $svcs->firstWhere('svc_oid', $svcId)->svc_id,
                'svc_oid' => $svcId,
                'sapPortId' => $sapPortId,
                'device_id' => $this->getDeviceId(),
                'sapEncapValue' => $this->nokiaEncap($sapEncapValue),
                'sapRowStatus' => $value['sapRowStatus'],
                'sapType' => $value['sapType'],
                'sapDescription' => $value['sapDescription'],
                'sapAdminStatus' => $value['sapAdminStatus'],
                'sapOperStatus' => $value['sapOperStatus'],
                'sapLastMgmtChange' => round(($value['sapLastMgmtChange'] ?? 0) / 100),
                'sapLastStatusChange' => round(($value['sapLastStatusChange'] ?? 0) / 100),
                'sapIngressBytes' => ($value['sapBaseStatsIngressPchipOfferedLoPrioOctets'] ?? 0) + ($value['sapBaseStatsIngressPchipOfferedHiPrioOctets'] ?? 0),
                'sapEgressBytes' => ($value['sapBaseStatsEgressQchipForwardedOutProfOctets'] ?? 0) + ($value['sapBaseStatsEgressQchipForwardedInProfOctets'] ?? 0),
                'sapIngressDroppedBytes' => ($value['sapBaseStatsIngressQchipDroppedLoPrioOctets'] ?? 0) + ($value['sapBaseStatsIngressQchipDroppedHiPrioOctets'] ?? 0),
                'nsapEgressDroppedBytes' => ($value['sapBaseStatsEgressQchipDroppedOutProfOctets'] ?? 0) + ($value['sapBaseStatsEgressQchipDroppedInProfOctets'] ?? 0),
            ]);
        })->filter();
    }

    /**
     * @return Collection MplsSdpBind objects
     */
    public function discoverMplsSdpBinds($sdps, $svcs): Collection
    {
        return SnmpQuery::hideMib()->enumStrings()->numericIndex()->abortOnFailure()->walk([
            'TIMETRA-SDP-MIB::sdpBindTable',
            'TIMETRA-SDP-MIB::sdpBindBaseStatsTable',
        ])->mapTable(function ($value, $svcId) use ($sdps, $svcs) {
            $bind_id = str_replace(' ', '', $value['sdpBindId'] ?? '');
            $sdp_oid = hexdec(substr($bind_id, 0, 8));
            $svc_oid = hexdec(substr($bind_id, 9, 16));
            $sdp_id = $sdps->firstWhere('sdp_oid', $sdp_oid)->sdp_id;
            $svc_id = $svcs->firstWhere('svc_oid', $svcId)->svc_id;

            if ($sdp_id && $svc_id && $sdp_oid && $svc_oid) {
                return new MplsSdpBind([
                    'sdp_id' => $sdp_id,
                    'svc_id' => $svc_id,
                    'sdp_oid' => $sdp_oid,
                    'svc_oid' => $svc_oid,
                    'device_id' => $this->getDeviceId(),
                    'sdpBindRowStatus' => $value['sdpBindRowStatus'],
                    'sdpBindAdminStatus' => $value['sdpBindAdminStatus'] ?? null,
                    'sdpBindOperStatus' => $value['sdpBindOperStatus'] ?? null,
                    'sdpBindLastMgmtChange' => round(($value['sdpBindLastMgmtChange'] ?? 0) / 100),
                    'sdpBindLastStatusChange' => round(($value['sdpBindLastStatusChange'] ?? 0) / 100),
                    'sdpBindType' => $value['sdpBindType'],
                    'sdpBindVcType' => $value['sdpBindVcType'],
                    'sdpBindBaseStatsIngFwdPackets' => $value['sdpBindBaseStatsIngressForwardedPackets'] ?? null,
                    'sdpBindBaseStatsIngFwdOctets' => $value['sdpBindBaseStatsIngFwdOctets'] ?? null,
                    'sdpBindBaseStatsEgrFwdPackets' => $value['sdpBindBaseStatsEgressForwardedPackets'] ?? null,
                    'sdpBindBaseStatsEgrFwdOctets' => $value['sdpBindBaseStatsEgressForwardedOctets'] ?? null,
                ]);
            }

            return null;
        })->filter();
    }

    /**
     * @return Collection MplsTunnelArHop objects
     */
    public function discoverMplsTunnelArHops($paths): Collection
    {
        return SnmpQuery::hideMib()->abortOnFailure()->walk([
            'MPLS-TE-MIB::mplsTunnelARHopTable',
            'TIMETRA-MPLS-MIB::vRtrMplsTunnelARHopTable',
        ])->mapTable(function ($value, $mplsTunnelARHopListIndex, $mplsTunnelARHopIndex) use ($paths) {
            $lsp_path_id = $paths->firstWhere('mplsLspPathTunnelARHopListIndex', $mplsTunnelARHopListIndex)->lsp_path_id;
            $protection = intval($value['vRtrMplsTunnelARHopProtection'], 16);

            // vRtrMplsTunnelARHopProtection Bits
            $localAvailable = 0b10000000;
            $localInUse = 0b01000000;
            $bandwidthProtected = 0b00100000;
            $nodeProtected = 0b00010000;
            $preemptionPending = 0b00001000;
            $nodeId = 0b00000100;

            $localLinkProtection = ($protection & $localAvailable) ? 'true' : 'false';
            $linkProtectionInUse = ($protection & $localInUse) ? 'true' : 'false';
            $bandwidthProtection = ($protection & $bandwidthProtected) ? 'true' : 'false';
            $nextNodeProtection = ($protection & $nodeProtected) ? 'true' : 'false';

            if (isset($mplsTunnelARHopListIndex, $mplsTunnelARHopIndex, $lsp_path_id)) {
                return new MplsTunnelArHop([
                    'mplsTunnelARHopListIndex' => $mplsTunnelARHopListIndex,
                    'mplsTunnelARHopIndex' => $mplsTunnelARHopIndex,
                    'lsp_path_id' => $lsp_path_id,
                    'device_id' => $this->getDeviceId(),
                    'mplsTunnelARHopAddrType' => $value['mplsTunnelARHopAddrType'] ?? null,
                    'mplsTunnelARHopIpv4Addr' => $value['mplsTunnelARHopIpv4Addr'] ?? null,
                    'mplsTunnelARHopIpv6Addr' => $value['mplsTunnelARHopIpv6Addr'] ?? null,
                    'mplsTunnelARHopAsNumber' => $value['mplsTunnelARHopAsNumber'] ?? null,
                    'mplsTunnelARHopStrictOrLoose' => $value['mplsTunnelARHopStrictOrLoose'] ?? null,
                    'mplsTunnelARHopRouterId' => $this->parseIpField($value, 'vRtrMplsTunnelARHopNgRouterId'),
                    'localProtected' => $localLinkProtection,
                    'linkProtectionInUse' => $linkProtectionInUse,
                    'bandwidthProtected' => $bandwidthProtection,
                    'nextNodeProtected' => $nextNodeProtection,
                ]);
            }

            return null;
        })->filter();
    }

    /**
     * @return Collection MplsTunnelCHop objects
     */
    public function discoverMplsTunnelCHops($paths): Collection
    {
        $lsp_ids = $paths->pluck('lsp_path_id', 'mplsLspPathTunnelCHopListIndex');

        return SnmpQuery::hideMib()
            ->walk('TIMETRA-MPLS-MIB::vRtrMplsTunnelCHopTable')
            ->mapTable(function ($value, $mplsTunnelCHopListIndex, $mplsTunnelCHopIndex) use ($lsp_ids) {
                $lsp_path_id = $lsp_ids->get($mplsTunnelCHopListIndex);

                return new MplsTunnelCHop([
                    'mplsTunnelCHopListIndex' => $mplsTunnelCHopListIndex,
                    'mplsTunnelCHopIndex' => $mplsTunnelCHopIndex,
                    'lsp_path_id' => $lsp_path_id,
                    'device_id' => $this->getDeviceId(),
                    'mplsTunnelCHopAddrType' => $value['vRtrMplsTunnelCHopAddrType'],
                    'mplsTunnelCHopIpv4Addr' => $value['vRtrMplsTunnelCHopIpv4Addr'],
                    'mplsTunnelCHopIpv6Addr' => $value['vRtrMplsTunnelCHopIpv6Addr'],
                    'mplsTunnelCHopAsNumber' => $value['vRtrMplsTunnelCHopAsNumber'],
                    'mplsTunnelCHopStrictOrLoose' => $value['vRtrMplsTunnelCHopStrictOrLoose'],
                    'mplsTunnelCHopRouterId' => $value['vRtrMplsTunnelCHopRtrID'],
                ]);
            });
    }

    /**
     * @return Collection MplsLsp objects
     */
    public function pollMplsLsps(): Collection
    {
        return SnmpQuery::hideMib()->abortOnFailure()->walk([
            'TIMETRA-MPLS-MIB::vRtrMplsLspTable',
            'TIMETRA-MPLS-MIB::vRtrMplsLspLastChange',
            'TIMETRA-MPLS-MIB::vRtrMplsLspStatTable',
        ])->mapTable(function ($value, $vrf_oid, $lsp_oid) {
            return new MplsLsp([
                'vrf_oid' => $vrf_oid,
                'lsp_oid' => $lsp_oid,
                'device_id' => $this->getDeviceId(),
                'mplsLspRowStatus' => $value['vRtrMplsLspRowStatus'],
                'mplsLspLastChange' => round(($value['vRtrMplsLspLastChange'] ?? 0) / 100),
                'mplsLspName' => $value['vRtrMplsLspName'] ?? null,
                'mplsLspAdminState' => $value['vRtrMplsLspAdminState'] ?? null,
                'mplsLspOperState' => $value['vRtrMplsLspOperState'] ?? null,
                'mplsLspFromAddr' => $this->parseIpField($value, 'vRtrMplsLspNgFromAddr'),
                'mplsLspToAddr' => $this->parseIpField($value, 'vRtrMplsLspNgToAddr'),
                'mplsLspType' => $value['vRtrMplsLspType'] ?? null,
                'mplsLspFastReroute' => $value['vRtrMplsLspFastReroute'] ?? null,
                'mplsLspAge' => abs($value['vRtrMplsLspAge'] ?? 0),
                'mplsLspTimeUp' => abs($value['vRtrMplsLspTimeUp'] ?? 0),
                'mplsLspTimeDown' => abs($value['vRtrMplsLspTimeDown'] ?? 0),
                'mplsLspPrimaryTimeUp' => abs($value['vRtrMplsLspPrimaryTimeUp'] ?? 0),
                'mplsLspTransitions' => $value['vRtrMplsLspTransitions'] ?? null,
                'mplsLspLastTransition' => abs(round(($value['vRtrMplsLspLastTransition'] ?? 0) / 100)),
                'mplsLspConfiguredPaths' => $value['vRtrMplsLspConfiguredPaths'] ?? null,
                'mplsLspStandbyPaths' => $value['vRtrMplsLspStandbyPaths'] ?? null,
                'mplsLspOperationalPaths' => $value['vRtrMplsLspOperationalPaths'] ?? null,
            ]);
        });
    }

    /**
     * @param  Collection  $lsps  collecton of synchronized lsp objects from pollMplsLsps()
     * @return Collection MplsLspPath objects
     */
    public function pollMplsPaths($lsps): Collection
    {
        return SnmpQuery::hideMib()->enumStrings()->abortOnFailure()->walk([
            'TIMETRA-MPLS-MIB::vRtrMplsLspPathTable',
            'TIMETRA-MPLS-MIB::vRtrMplsLspPathLastChange',
            'TIMETRA-MPLS-MIB::vRtrMplsLspPathStatTable',
        ])->mapTable(function ($value, $vrf_oid, $lsp_oid, $path_oid) use ($lsps) {
            $lsp_id = $lsps->where('lsp_oid', $lsp_oid)->firstWhere('vrf_oid', $vrf_oid)->lsp_id;

            return new MplsLspPath([
                'lsp_id' => $lsp_id,
                'path_oid' => $path_oid,
                'device_id' => $this->getDeviceId(),
                'mplsLspPathRowStatus' => $value['vRtrMplsLspPathRowStatus'] ?? null,
                'mplsLspPathLastChange' => round(($value['vRtrMplsLspPathLastChange'] ?? 0) / 100),
                'mplsLspPathType' => $value['vRtrMplsLspPathType'] ?? null,
                'mplsLspPathBandwidth' => $value['vRtrMplsLspPathBandwidth'] ?? null,
                'mplsLspPathOperBandwidth' => $value['vRtrMplsLspPathOperBandwidth'] ?? null,
                'mplsLspPathAdminState' => $value['vRtrMplsLspPathAdminState'] ?? null,
                'mplsLspPathOperState' => $value['vRtrMplsLspPathOperState'] ?? null,
                'mplsLspPathState' => $value['vRtrMplsLspPathState'] ?? null,
                'mplsLspPathFailCode' => $value['vRtrMplsLspPathFailCode'] ?? null,
                'mplsLspPathFailNodeAddr' => $value['vRtrMplsLspPathFailNodeAddr'] ?? null,
                'mplsLspPathMetric' => $value['vRtrMplsLspPathMetric'] ?? null,
                'mplsLspPathOperMetric' => $value['vRtrMplsLspPathOperMetric'] ?? null,
                'mplsLspPathTimeUp' => abs($value['vRtrMplsLspPathTimeUp'] ?? 0),
                'mplsLspPathTimeDown' => abs($value['vRtrMplsLspPathTimeDown'] ?? 0),
                'mplsLspPathTransitionCount' => $value['vRtrMplsLspPathTransitionCount'] ?? null,
                'mplsLspPathTunnelARHopListIndex' => $value['vRtrMplsLspPathTunnelARHopListIndex'] ?? null,
                'mplsLspPathTunnelCHopListIndex' => $value['vRtrMplsLspPathTunnelCRHopListIndex'] ?? null,
            ]);
        });
    }

    /**
     * @return Collection MplsSdp objects
     */
    public function pollMplsSdps(): Collection
    {
        return SnmpQuery::hideMib()->enumStrings()->walk('TIMETRA-SDP-MIB::sdpInfoTable')->mapTable(function ($value) {
            return new MplsSdp([
                'sdp_oid' => $value['sdpId'],
                'device_id' => $this->getDeviceId(),
                'sdpRowStatus' => $value['sdpRowStatus'],
                'sdpDelivery' => $value['sdpDelivery'],
                'sdpDescription' => $value['sdpDescription'],
                'sdpAdminStatus' => $value['sdpAdminStatus'],
                'sdpOperStatus' => $value['sdpOperStatus'],
                'sdpAdminPathMtu' => $value['sdpAdminPathMtu'],
                'sdpOperPathMtu' => $value['sdpOperPathMtu'],
                'sdpLastMgmtChange' => round($value['sdpLastMgmtChange'] / 100),
                'sdpLastStatusChange' => round($value['sdpLastStatusChange'] / 100),
                'sdpActiveLspType' => $value['sdpActiveLspType'] ?? null,
                'sdpFarEndInetAddressType' => $value['sdpFarEndInetAddressType'] ?? null,
                'sdpFarEndInetAddress' => IP::fromHexString($value['sdpFarEndInetAddress'], true),
            ]);
        });
    }

    /**
     * @return Collection MplsService objects
     */
    public function pollMplsServices(): Collection
    {
        return SnmpQuery::hideMib()->enumStrings()->abortOnFailure()->walk([
            'TIMETRA-SERV-MIB::svcBaseInfoTable',
            'TIMETRA-SERV-MIB::svcTlsInfoTable',
        ])->mapTable(function ($value) {
            // Workaround, remove some default entries we do not want to see
            if (preg_match('/^\w* Service for internal purposes only/', $value['svcDescription'])) {
                return null;
            }

            return new MplsService([
                'svc_oid' => $value['svcId'],
                'device_id' => $this->getDeviceId(),
                'svcRowStatus' => $value['svcRowStatus'] ?? null,
                'svcType' => $value['svcType'] ?? null,
                'svcCustId' => $value['svcCustId'] ?? null,
                'svcAdminStatus' => $value['svcAdminStatus'] ?? null,
                'svcOperStatus' => $value['svcOperStatus'] ?? null,
                'svcDescription' => $value['svcDescription'] ?? null,
                'svcMtu' => $value['svcMtu'] ?? null,
                'svcNumSaps' => $value['svcNumSaps'] ?? null,
                'svcNumSdps' => $value['svcNumSdps'] ?? null,
                'svcLastMgmtChange' => round(($value['svcLastMgmtChange'] ?? 0) / 100),
                'svcLastStatusChange' => round(($value['svcLastStatusChange'] ?? 0) / 100),
                'svcVRouterId' => $value['svcVRouterId'] ?? null,
                'svcTlsMacLearning' => $value['svcTlsMacLearning'] ?? null,
                'svcTlsStpAdminStatus' => $value['svcTlsStpAdminStatus'] ?? null,
                'svcTlsStpOperStatus' => $value['svcTlsStpOperStatus'] ?? null,
                'svcTlsFdbTableSize' => $value['svcTlsFdbTableSize'] ?? null,
                'svcTlsFdbNumEntries' => $value['svcTlsFdbNumEntries'] ?? null,
            ]);
        })->filter();
    }

    /**
     * @return Collection MplsSap objects
     */
    public function pollMplsSaps($svcs): Collection
    {
        // cache a ifIndex -> ifName
        $ifIndexNames = $this->getDevice()->ports()->pluck('ifName', 'ifIndex');

        return SnmpQuery::hideMib()->enumStrings()->abortOnFailure()->walk([
            'TIMETRA-SAP-MIB::sapBaseInfoTable',
            'TIMETRA-SAP-MIB::sapBaseStatsTable',
        ])->mapTable(function ($value, $svcId, $sapPortId, $sapEncapValue) use ($svcs, $ifIndexNames) {
            // Workaround, there are some oids not covered by actual MIB, try to filter them
            // i.e. sapBaseInfoEntry.300.118208001.1342177283.10
            if (! isset($value['sapDescription'])) {
                return null;
            }

            // remove some default entries we do not want to see
            if (str_starts_with($value['sapDescription'], 'Internal SAP')) {
                return null;
            }

            $svc_id = $svcs->firstWhere('svc_oid', $svcId)->svc_id;

            // Any unused vlan on a port returns * in sapEncapValue but had OID .4095
            $specialQinQIdentifier = $this->nokiaEncap($sapEncapValue);
            if ($specialQinQIdentifier == '*') {
                $specialQinQIdentifier = '4095';
            }
            $traffic_id = $svcId . '.' . $sapPortId . '.' . $specialQinQIdentifier;

            //create SAP graphs
            $rrd_name = \LibreNMS\Data\Store\Rrd::safeName('sap-' . $traffic_id);
            $rrd_def = RrdDefinition::make()
                ->addDataset('sapIngressBits', 'COUNTER', 0)
                ->addDataset('sapEgressBits', 'COUNTER', 0)
                ->addDataset('sapIngressDroppedBits', 'COUNTER', 0)
                ->addDataset('sapEgressDroppedBits', 'COUNTER', 0);

            $fields = [
                'sapIngressBits' => (($value['sapBaseStatsIngressPchipOfferedLoPrioOctets'] ?? 0) + ($value['sapBaseStatsIngressPchipOfferedHiPrioOctets'] ?? 0)) * 8,
                'sapEgressBits' => (($value['sapBaseStatsEgressQchipForwardedOutProfOctets'] ?? 0) + ($value['sapBaseStatsEgressQchipForwardedInProfOctets'] ?? 0)) * 8,
                'sapIngressDroppedBits' => (($value['sapBaseStatsIngressQchipDroppedLoPrioOctets'] ?? 0) + ($value['sapBaseStatsIngressQchipDroppedHiPrioOctets'] ?? 0)) * 8,
                'sapEgressDroppedBits' => (($value['sapBaseStatsEgressQchipDroppedOutProfOctets'] ?? 0) + ($value['sapBaseStatsEgressQchipDroppedInProfOctets'] ?? 0)) * 8,
            ];

            $tags = [
                'traffic_id' => $traffic_id,
                'rrd_name' => $rrd_name,
                'rrd_def' => $rrd_def,
            ];

            app('Datastore')->put($this->getDeviceArray(), 'sap', $tags, $fields);
            $this->enableGraph('sap');

            return new MplsSap([
                'svc_id' => $svc_id,
                'svc_oid' => $svcId,
                'sapPortId' => $sapPortId,
                'ifName' => $ifIndexNames->get($sapPortId),
                'device_id' => $this->getDeviceId(),
                'sapEncapValue' => $this->nokiaEncap($sapEncapValue),
                'sapRowStatus' => $value['sapRowStatus'],
                'sapType' => $value['sapType'],
                'sapDescription' => $value['sapDescription'],
                'sapAdminStatus' => $value['sapAdminStatus'],
                'sapOperStatus' => $value['sapOperStatus'],
                'sapLastMgmtChange' => round($value['sapLastMgmtChange'] / 100),
                'sapLastStatusChange' => round($value['sapLastStatusChange'] / 100),
            ]);
        })->filter();
    }

    /**
     * @return Collection MplsSDpBind objects
     */
    public function pollMplsSdpBinds($sdps, $svcs): Collection
    {
        return SnmpQuery::hideMib()->numericIndex()->enumStrings()->abortOnFailure()->walk([
            'TIMETRA-SDP-MIB::sdpBindTable',
            'TIMETRA-SDP-MIB::sdpBindBaseStatsTable',
        ])->mapTable(function ($value, $svcId) use ($sdps, $svcs) {
            $bind_id = str_replace(' ', '', $value['sdpBindId'] ?? '');
            $sdp_oid = hexdec(substr($bind_id, 0, 8));
            $svc_oid = hexdec(substr($bind_id, 9, 16));
            $sdp_id = $sdps->firstWhere('sdp_oid', $sdp_oid)->sdp_id;
            $svc_id = $svcs->firstWhere('svc_oid', $svcId)->svc_id;

            if ($sdp_id && $svc_id && $sdp_oid && $svc_oid) {
                return new MplsSdpBind([
                    'sdp_id' => $sdp_id,
                    'svc_id' => $svc_id,
                    'sdp_oid' => $sdp_oid,
                    'svc_oid' => $svc_oid,
                    'device_id' => $this->getDeviceId(),
                    'sdpBindRowStatus' => $value['sdpBindRowStatus'] ?? null,
                    'sdpBindAdminStatus' => $value['sdpBindAdminStatus'] ?? null,
                    'sdpBindOperStatus' => $value['sdpBindOperStatus'] ?? null,
                    'sdpBindLastMgmtChange' => round(($value['sdpBindLastMgmtChange'] ?? 0) / 100),
                    'sdpBindLastStatusChange' => round(($value['sdpBindLastStatusChange'] ?? 0) / 100),
                    'sdpBindType' => $value['sdpBindType'] ?? null,
                    'sdpBindVcType' => $value['sdpBindVcType'] ?? null,
                    'sdpBindBaseStatsIngFwdPackets' => $value['sdpBindBaseStatsIngressForwardedPackets'] ?? null,
                    'sdpBindBaseStatsIngFwdOctets' => $value['sdpBindBaseStatsIngFwdOctets'] ?? null,
                    'sdpBindBaseStatsEgrFwdPackets' => $value['sdpBindBaseStatsEgressForwardedPackets'] ?? null,
                    'sdpBindBaseStatsEgrFwdOctets' => $value['sdpBindBaseStatsEgressForwardedOctets'] ?? null,
                ]);
            }

            return null;
        })->filter();
    }

    /**
     * @return Collection MplsTunnelArHop objects
     */
    public function pollMplsTunnelArHops($paths): Collection
    {
        return SnmpQuery::hideMib()->abortOnFailure()->walk([
            'MPLS-TE-MIB::mplsTunnelARHopTable',
            'TIMETRA-MPLS-MIB::vRtrMplsTunnelARHopTable',
        ])->mapTable(function ($value, $mplsTunnelARHopListIndex, $mplsTunnelARHopIndex) use ($paths) {
            $firstPath = $paths->firstWhere('mplsLspPathTunnelARHopListIndex', $mplsTunnelARHopListIndex);
            if (! isset($firstPath)) {
                return null;
            }
            $lsp_path_id = $firstPath->lsp_path_id;
            $protection = intval($value['vRtrMplsTunnelARHopProtection'] ?? 0, 16);

            // vRtrMplsTunnelARHopProtection Bits
            $localAvailable = 0b10000000;
            $localInUse = 0b01000000;
            $bandwidthProtected = 0b00100000;
            $nodeProtected = 0b00010000;
            $preemptionPending = 0b00001000;
            $nodeId = 0b00000100;

            $localLinkProtection = ($protection & $localAvailable) ? 'true' : 'false';
            $linkProtectionInUse = ($protection & $localInUse) ? 'true' : 'false';
            $bandwidthProtection = ($protection & $bandwidthProtected) ? 'true' : 'false';
            $nextNodeProtection = ($protection & $nodeProtected) ? 'true' : 'false';

            if (isset($mplsTunnelARHopListIndex, $mplsTunnelARHopIndex, $lsp_path_id)) {
                return new MplsTunnelArHop([
                    'mplsTunnelARHopListIndex' => $mplsTunnelARHopListIndex,
                    'mplsTunnelARHopIndex' => $mplsTunnelARHopIndex,
                    'lsp_path_id' => $lsp_path_id,
                    'device_id' => $this->getDeviceId(),
                    'mplsTunnelARHopAddrType' => $value['mplsTunnelARHopAddrType'] ?? null,
                    'mplsTunnelARHopIpv4Addr' => $value['mplsTunnelARHopIpv4Addr'] ?? null,
                    'mplsTunnelARHopIpv6Addr' => $value['mplsTunnelARHopIpv6Addr'] ?? null,
                    'mplsTunnelARHopAsNumber' => $value['mplsTunnelARHopAsNumber'] ?? null,
                    'mplsTunnelARHopStrictOrLoose' => $value['mplsTunnelARHopStrictOrLoose'] ?? null,
                    'mplsTunnelARHopRouterId' => $this->parseIpField($value, 'vRtrMplsTunnelARHopNgRouterId'),
                    'localProtected' => $localLinkProtection,
                    'linkProtectionInUse' => $linkProtectionInUse,
                    'bandwidthProtected' => $bandwidthProtection,
                    'nextNodeProtected' => $nextNodeProtection,
                ]);
            }

            return null;
        })->filter();
    }

    /**
     * @return Collection MplsTunnelCHop objects
     */
    public function pollMplsTunnelCHops($paths): Collection
    {
        $path_ids = $paths->pluck('lsp_path_id', 'mplsLspPathTunnelCHopListIndex');

        return SnmpQuery::hideMib()->walk('TIMETRA-MPLS-MIB::vRtrMplsTunnelCHopTable')
            ->mapTable(function ($value, $mplsTunnelCHopListIndex, $mplsTunnelCHopIndex) use ($path_ids) {
                $lsp_path_id = $path_ids[$mplsTunnelCHopListIndex] ?? null;

                return new MplsTunnelCHop([
                    'mplsTunnelCHopListIndex' => $mplsTunnelCHopListIndex,
                    'mplsTunnelCHopIndex' => $mplsTunnelCHopIndex,
                    'lsp_path_id' => $lsp_path_id,
                    'device_id' => $this->getDeviceId(),
                    'mplsTunnelCHopAddrType' => $value['vRtrMplsTunnelCHopAddrType'] ?? null,
                    'mplsTunnelCHopIpv4Addr' => $value['vRtrMplsTunnelCHopIpv4Addr'] ?? null,
                    'mplsTunnelCHopIpv6Addr' => $value['vRtrMplsTunnelCHopIpv6Addr'] ?? null,
                    'mplsTunnelCHopAsNumber' => $value['vRtrMplsTunnelCHopAsNumber'] ?? null,
                    'mplsTunnelCHopStrictOrLoose' => $value['vRtrMplsTunnelCHopStrictOrLoose'] ?? null,
                    'mplsTunnelCHopRouterId' => $value['vRtrMplsTunnelCHopRtrID'] ?? null,
                ]);
            });
    }

    public function discoverWirelessSnr(): array
    {
        $sensors = [];

        $carrier = SnmpQuery::cache()->walk('IF-MIB::ifName')->valuesByIndex();
        $data = SnmpQuery::walk('TIMETRA-CELLULAR-MIB::tmnxCellPortSinr')->valuesByIndex($carrier);

        foreach ($data as $index => $entry) {
            if (isset($entry['TIMETRA-CELLULAR-MIB::tmnxCellPortSinr'])) {
                $sensors[] = new WirelessSensor(
                    'snr',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.6527.3.1.2.109.3.1.1.1.12.' . $index,
                    'timos',
                    $index,
                    'SNR: ' . $entry['IF-MIB::ifName'],
                    $entry['TIMETRA-CELLULAR-MIB::tmnxCellPortSinr'] / 10,
                    1,
                    10
                );
            }
        }

        return $sensors;
    }

    public function discoverWirelessRsrq(): array
    {
        $sensors = [];

        $carrier = SnmpQuery::cache()->walk('IF-MIB::ifName')->valuesByIndex();
        $data = SnmpQuery::walk('TIMETRA-CELLULAR-MIB::tmnxCellPortRsrq')->valuesByIndex($carrier);

        foreach ($data as $index => $entry) {
            if (isset($entry['TIMETRA-CELLULAR-MIB::tmnxCellPortRsrq'])) {
                $sensors[] = new WirelessSensor(
                    'rsrq',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.6527.3.1.2.109.3.1.1.1.11.' . $index,
                    'timos',
                    $index,
                    'RSRQ: ' . $entry['IF-MIB::ifName'],
                    $entry['TIMETRA-CELLULAR-MIB::tmnxCellPortRsrq']
                );
            }
        }

        return $sensors;
    }

    public function discoverWirelessRssi(): array
    {
        $sensors = [];

        $carrier = SnmpQuery::cache()->walk('IF-MIB::ifName')->valuesByIndex();
        $data = SnmpQuery::walk('TIMETRA-CELLULAR-MIB::tmnxCellPortRssi')->valuesByIndex($carrier);

        foreach ($data as $index => $entry) {
            if (isset($entry['TIMETRA-CELLULAR-MIB::tmnxCellPortRssi'])) {
                $sensors[] = new WirelessSensor(
                    'rssi',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.6527.3.1.2.109.3.1.1.1.8.' . $index,
                    'timos',
                    $index,
                    'RSSI: ' . $entry['IF-MIB::ifName'],
                    $entry['TIMETRA-CELLULAR-MIB::tmnxCellPortRssi'],
                );
            }
        }

        return $sensors;
    }

    public function discoverWirelessRsrp(): array
    {
        $sensors = [];

        $carrier = SnmpQuery::cache()->walk('IF-MIB::ifName')->valuesByIndex();
        $data = SnmpQuery::walk('TIMETRA-CELLULAR-MIB::tmnxCellPortRsrp')->valuesByIndex($carrier);

        foreach ($data as $index => $entry) {
            if (isset($entry['TIMETRA-CELLULAR-MIB::tmnxCellPortRsrp'])) {
                $sensors[] = new WirelessSensor(
                    'rsrp',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.6527.3.1.2.109.3.1.1.1.9.' . $index,
                    'timos',
                    $index,
                    'RSRP: ' . $entry['IF-MIB::ifName'],
                    $entry['TIMETRA-CELLULAR-MIB::tmnxCellPortRsrp'],
                );
            }
        }

        return $sensors;
    }

    public function discoverWirelessChannel(): array
    {
        $sensors = [];

        $carrier = SnmpQuery::cache()->walk('IF-MIB::ifName')->valuesByIndex();
        $data = SnmpQuery::walk('TIMETRA-CELLULAR-MIB::tmnxCellPortChannelNumber')->valuesByIndex($carrier);

        foreach ($data as $index => $entry) {
            if (isset($entry['TIMETRA-CELLULAR-MIB::tmnxCellPortChannelNumber'])) {
                $sensors[] = new WirelessSensor(
                    'channel',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.6527.3.1.2.109.3.1.1.1.5.' . $index,
                    'timos',
                    $index,
                    'CHANNEL: ' . $entry['IF-MIB::ifName'],
                    $entry['TIMETRA-CELLULAR-MIB::tmnxCellPortChannelNumber']
                );
            }
        }

        return $sensors;
    }

    public function discoverEntityPhysical(): Collection
    {
        $inventory = new Collection;

        $chassis = SnmpQuery::walk('TIMETRA-CHASSIS-MIB::tmnxChassisType')->pluck();
        $chassisTypes = SnmpQuery::walk('TIMETRA-CHASSIS-MIB::tmnxChassisTypeTable')->table(1);
        $hardware = SnmpQuery::enumStrings()->walk('TIMETRA-CHASSIS-MIB::tmnxHwTable');

        foreach ($hardware->table(2) as $tmnxChassisIndex => $chassisContents) {
            $type = $chassis[$tmnxChassisIndex];

            if (isset($chassisTypes[$type])) {
                $inventory->push(new EntPhysical([
                    'entPhysicalIndex' => $tmnxChassisIndex,
                    'entPhysicalDescr' => $chassisTypes[$type]['TIMETRA-CHASSIS-MIB::tmnxChassisTypeDescription'] ?? null,
                    'entPhysicalClass' => 'chassis',
                    'entPhysicalContainedIn' => 0,
                    'entPhysicalName' => $chassisTypes[$type]['TIMETRA-CHASSIS-MIB::tmnxChassisTypeName'] ?? null,
                ]));
            }

            foreach ($chassisContents as $tmnxHwIndex => $entry) {
                $inventory->push(new EntPhysical([
                    'entPhysicalIndex' => $tmnxHwIndex,
                    'entPhysicalClass' => $entry['TIMETRA-CHASSIS-MIB::tmnxHwClass'],
                    //                    'entPhysicalDescr' => $entry['TIMETRA-CHASSIS-MIB::tmnxHwID'],
                    'entPhysicalName' => $entry['TIMETRA-CHASSIS-MIB::tmnxHwName'],
                    'entPhysicalModelName' => $entry['TIMETRA-CHASSIS-MIB::tmnxHwMfgBoardNumber'],
                    'entPhysicalSerialNum' => $entry['TIMETRA-CHASSIS-MIB::tmnxHwSerialNumber'],
                    'entPhysicalContainedIn' => $entry['TIMETRA-CHASSIS-MIB::tmnxHwContainedIn'],
                    'entPhysicalMfgName' => $entry['TIMETRA-CHASSIS-MIB::tmnxHwMfgBoardNumber'],
                    'entPhysicalParentRelPos' => $entry['TIMETRA-CHASSIS-MIB::tmnxHwParentRelPos'],
                    'entPhysicalHardwareRev' => '1.0',
                    'entPhysicalFirmwareRev' => $entry['TIMETRA-CHASSIS-MIB::tmnxHwBootCodeVersion'],
                    'entPhysicalSoftwareRev' => $entry['TIMETRA-CHASSIS-MIB::tmnxHwBootCodeVersion'],
                    'entPhysicalIsFRU' => $entry['TIMETRA-CHASSIS-MIB::tmnxHwIsFRU'],
                    'entPhysicalAlias' => $entry['TIMETRA-CHASSIS-MIB::tmnxHwAlias'],
                    'entPhysicalAssetID' => $entry['TIMETRA-CHASSIS-MIB::tmnxHwAssetID'],
                ]));
            }
        }

        return $inventory;
    }

    private function parseIpField(array $data, string $ngField): string|null
    {
        if (isset($data[$ngField])) {
            try {
                return IP::parse($data[$ngField])->uncompressed();
            } catch (InvalidIpException $e) {
                return null;
            }
        }

        $nonNg = str_replace('Ng', '', $ngField);
        if (isset($data[$nonNg])) {
            return $data[$nonNg];
        }

        return null;
    }
}
