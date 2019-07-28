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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2019 Vitali Kari
 * @copyright  2019 Tony Murray
 * @author     Vitali Kari <vitali.kari@gmail.com>
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\MplsLsp;
use App\Models\MplsLspPath;
use App\Models\MplsSdp;
use App\Models\MplsService;
use App\Models\MplsSap;
use App\Models\MplsSdpBind;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\MplsDiscovery;
use LibreNMS\Interfaces\Polling\MplsPolling;
use LibreNMS\OS;

class Timos extends OS implements MplsDiscovery, MplsPolling
{
    /**
     * @param tmnxEnacpVal
     * @return encapsulation string
     * see TIMETRA-TC-MIB::TmnxEncapVal
     */
    private function nokiaEncap($tmnxEncapVal)
    {
        // implement other encapsulation values
        $map = sprintf("%032b", $tmnxEncapVal);
       
        if (substr($map, -32, 20) == '00000000000000000000') { // 12-bit IEEE 802.1Q VLAN ID
            if ($tmnxEncapVal == 4095) {
                return '*';
            }
        }

        return $tmnxEncapVal;
    }

    /**
     * @param tmnxPortID a 32bit encoded value
     * @param scheme
     * @return converted ifName
     * see TIMETRA-TC-MIB::TmnxPortID
    */
    private function nokiaIfName($tmnxPortId, $scheme)
    {
        // Fixme implement other schemes and channels
        if ($scheme == 'schemeA') {
            $map = sprintf("%032b", $tmnxPortId);
            
            if (substr($map, -32, 4) == '0101') { // LAG Port
                if (substr($map, -28, 4) == '1011') { // Pseudowire Port
                    return "pw-" . bindec(substr($map, -10, 10));
                }
                return "lag-" . bindec(substr($map, -10, 10));
            }
            $slot = bindec(substr($map, -29, 4));
            $mda = bindec(substr($map, -25, 4));
            $port = bindec(substr($map, -21, 6));
            return $slot . "/" . $mda . "/" . $port;
        }
    }

    /**
     * @return Collection MplsLsp objects
     */
    public function discoverMplsLsps()
    {
        $mplsLspCache = snmpwalk_cache_multi_oid($this->getDevice(), 'vRtrMplsLspTable', [], 'TIMETRA-MPLS-MIB', 'nokia');
        if (!empty($mplsLspCache)) {
            $mplsLspCache = snmpwalk_cache_multi_oid($this->getDevice(), 'vRtrMplsLspLastChange', $mplsLspCache, 'TIMETRA-MPLS-MIB', 'nokia', '-OQUst');
        }

        $lsps = collect();
        foreach ($mplsLspCache as $key => $value) {
            list($vrf_oid, $lsp_oid) = explode('.', $key);
            $lsps->push(new MplsLsp([
                'vrf_oid' => $vrf_oid,
                'lsp_oid' => $lsp_oid,
                'device_id' => $this->getDeviceId(),
                'mplsLspRowStatus' => $value['vRtrMplsLspRowStatus'],
                'mplsLspLastChange' => round($value['vRtrMplsLspLastChange'] / 100),
                'mplsLspName' => $value['vRtrMplsLspName'],
                'mplsLspAdminState' => $value['vRtrMplsLspAdminState'],
                'mplsLspOperState' => $value['vRtrMplsLspOperState'],
                'mplsLspFromAddr' => $value['vRtrMplsLspFromAddr'],
                'mplsLspToAddr' => $value['vRtrMplsLspToAddr'],
                'mplsLspType' => $value['vRtrMplsLspType'],
                'mplsLspFastReroute' => $value['vRtrMplsLspFastReroute'],
            ]));
        }

        return $lsps;
    }

    /**
     * @param Collection $lsps collecton of synchronized lsp objects from discoverMplsLsps()
     * @return Collection MplsLspPath objects
     */
    public function discoverMplsPaths($lsps)
    {
        $mplsPathCache = snmpwalk_cache_multi_oid($this->getDevice(), 'vRtrMplsLspPathTable', [], 'TIMETRA-MPLS-MIB', 'nokia');
        if (!empty($mplsPathCache)) {
            $mplsPathCache = snmpwalk_cache_multi_oid($this->getDevice(), 'vRtrMplsLspPathLastChange', $mplsPathCache, 'TIMETRA-MPLS-MIB', 'nokia', '-OQUst');
        }

        $paths = collect();
        foreach ($mplsPathCache as $key => $value) {
            list($vrf_oid, $lsp_oid, $path_oid) = explode('.', $key);
            $lsp_id = $lsps->where('lsp_oid', $lsp_oid)->firstWhere('vrf_oid', $vrf_oid)->lsp_id;
            $paths->push(new MplsLspPath([
                'lsp_id' => $lsp_id,
                'path_oid' => $path_oid,
                'device_id' => $this->getDeviceId(),
                'mplsLspPathRowStatus' => $value['vRtrMplsLspPathRowStatus'],
                'mplsLspPathLastChange' => round($value['vRtrMplsLspPathLastChange'] / 100),
                'mplsLspPathType' => $value['vRtrMplsLspPathType'],
                'mplsLspPathBandwidth' => $value['vRtrMplsLspPathBandwidth'],
                'mplsLspPathOperBandwidth' => $value['vRtrMplsLspPathOperBandwidth'],
                'mplsLspPathAdminState' => $value['vRtrMplsLspPathAdminState'],
                'mplsLspPathOperState' => $value['vRtrMplsLspPathOperState'],
                'mplsLspPathState' => $value['vRtrMplsLspPathState'],
                'mplsLspPathFailCode' => $value['vRtrMplsLspPathFailCode'],
                'mplsLspPathFailNodeAddr' => $value['vRtrMplsLspPathFailNodeAddr'],
                'mplsLspPathMetric' => $value['vRtrMplsLspPathMetric'],
                'mplsLspPathOperMetric' => $value['vRtrMplsLspPathOperMetric'],
            ]));
        }

        return $paths;
    }

    /**
     * @return Collection MplsSdp objects
     */
    public function discoverMplsSdps()
    {
        $mplsSdpCache = snmpwalk_cache_multi_oid($this->getDevice(), 'sdpInfoTable', [], 'TIMETRA-SDP-MIB', 'nokia', '-OQUst');

        $sdps = collect();
        foreach ($mplsSdpCache as $value) {
            if ((!empty($value['sdpFarEndInetAddress'])) && ($value['sdpFarEndInetAddressType'] == 'ipv4')) {
                $ip = long2ip(hexdec(str_replace(' ', '', $value['sdpFarEndInetAddress'])));
            } else {
                #Fixme implement ipv6 conversion
                $ip = $value['sdpFarEndInetAddress'];
            }
            $sdps->push(new MplsSdp([
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
                'sdpActiveLspType' => $value['sdpActiveLspType'],
                'sdpFarEndInetAddressType' => $value['sdpFarEndInetAddressType'],
                'sdpFarEndInetAddress' => $ip,
            ]));
        }

        return $sdps;
    }

    /**
     * @return Collection MplsService objects
     */
    public function discoverMplsServices()
    {
        $mplsSvcCache = snmpwalk_cache_multi_oid($this->getDevice(), 'svcBaseInfoTable', [], 'TIMETRA-SERV-MIB', 'nokia', '-OQUst');
        $mplsSvcCache = snmpwalk_cache_multi_oid($this->getDevice(), 'svcTlsInfoTable', $mplsSvcCache, 'TIMETRA-SERV-MIB', 'nokia', '-OQUst');

        $svcs = collect();

        // Workaround, remove some defalt entries we do not want to see
        $filter = '/^\w* Service for internal purposes only/';

        foreach ($mplsSvcCache as $key => $value) {
            $garbage = preg_match($filter, $value['svcDescription']);
            if ($garbage) {
                unset($key);
                continue;
            }

            $svcs->push(new MplsService([
                'svc_oid' => $value['svcId'],
                'device_id' => $this->getDeviceId(),
                'svcRowStatus' => $value['svcRowStatus'],
                'svcType' => $value['svcType'],
                'svcCustId' => $value['svcCustId'],
                'svcAdminStatus' => $value['svcAdminStatus'],
                'svcOperStatus' => $value['svcOperStatus'],
                'svcDescription' => $value['svcDescription'],
                'svcMtu' => $value['svcMtu'],
                'svcNumSaps' => $value['svcNumSaps'],
                'svcNumSdps' => $value['svcNumSdps'],
                'svcLastMgmtChange' => round($value['svcLastMgmtChange'] / 100),
                'svcLastStatusChange' => round($value['svcLastStatusChange'] / 100),
                'svcVRouterId' => $value['svcVRouterId'],
                'svcTlsMacLearning' => $value['svcTlsMacLearning'],
                'svcTlsStpAdminStatus' => $value['svcTlsStpAdminStatus'],
                'svcTlsStpOperStatus' => $value['svcTlsStpOperStatus'],
                'svcTlsFdbTableSize' => $value['svcTlsFdbTableSize'],
                'svcTlsFdbNumEntries' => $value['svcTlsFdbNumEntries'],
            ]));
        }
        return $svcs;
    }
    
    /**
     * @return Collection MplsSap objects
     */
    public function discoverMplsSaps($svcs)
    {
        $mplsSapCache = snmpwalk_cache_multi_oid($this->getDevice(), 'sapBaseInfoTable', [], 'TIMETRA-SAP-MIB', 'nokia', '-OQUst');
        $portScheme = snmp_get($this->getDevice(), 'tmnxChassisPortIdScheme.1', '-Oqv', 'TIMETRA-CHASSIS-MIB', 'nokia');
        
        $saps = collect();

        // Workaround, there are some oids not covered by actual MIB, try to filter them
        // i.e. sapBaseInfoEntry.300.118208001.1342177283.10
        $filter_key = '/300\.[0-9]+\.[0-9]+\.[0-9]+/';
        // remove some defalt entries we do not want to see
        $filter_value = '/^Internal SAP/';

        foreach ($mplsSapCache as $key => $value) {
            if (preg_match($filter_key, $key) || preg_match($filter_value, $value['sapDescription'])) {
                unset($key);
                continue;
            }
            list($svcId, $sapPortId, $sapEncapValue) = explode('.', $key);
            $svc_id = $svcs->firstWhere('svc_oid', $svcId)->svc_id;
            $saps->push(new MplsSap([
                'svc_id' => $svc_id,
                'svc_oid' => $svcId,
                'sapPortId' => $sapPortId,
                'ifName' => $this->nokiaIfName($sapPortId, $portScheme),
                'device_id' => $this->getDeviceId(),
                'sapEncapValue' => $this->nokiaEncap($sapEncapValue),
                'sapRowStatus' => $value['sapRowStatus'],
                'sapType' => $value['sapType'],
                'sapDescription' => $value['sapDescription'],
                'sapAdminStatus' => $value['sapAdminStatus'],
                'sapOperStatus' => $value['sapOperStatus'],
                'sapLastMgmtChange' => round($value['sapLastMgmtChange'] / 100),
                'sapLastStatusChange' => round($value['sapLastStatusChange'] /100),
            ]));
        }
        return $saps;
    }


    /**
     * @return Collection MplsSdpBind objects
     */
    public function discoverMplsSdpBinds($sdps, $svcs)
    {
        $mplsBindCache = snmpwalk_cache_multi_oid($this->getDevice(), 'sdpBindTable', [], 'TIMETRA-SDP-MIB', 'nokia', '-OQUsbt');
        $mplsBindCache = snmpwalk_cache_multi_oid($this->getDevice(), 'sdpBindBaseStatsTable', $mplsBindCache, 'TIMETRA-SDP-MIB', 'nokia', '-OQUsb');

        $binds = collect();
        foreach ($mplsBindCache as $value) {
            $bind_id = str_replace(' ', '', $value['sdpBindId']);
            $sdp_oid = hexdec(substr($bind_id, 0, 8));
            $svc_oid = hexdec(substr($bind_id, 9, 16));
            $sdp_id = $sdps->firstWhere('sdp_oid', $sdp_oid)->sdp_id;
            $svc_id = $svcs->firstWhere('svc_oid', $svc_oid)->svc_id;
            $binds->push(new MplsSdpBind([
                'sdp_id' => $sdp_id,
                'svc_id' => $svc_id,
                'sdp_oid' => $sdp_oid,
                'svc_oid' => $svc_oid,
                'device_id' => $this->getDeviceId(),
                'sdpBindRowStatus' => $value['sdpBindRowStatus'],
                'sdpBindAdminStatus' => $value['sdpBindAdminStatus'],
                'sdpBindOperStatus' => $value['sdpBindOperStatus'],
                'sdpBindLastMgmtChange' => round($value['sdpBindLastMgmtChange'] / 100),
                'sdpBindLastStatusChange' => round($value['sdpBindLastStatusChange'] / 100),
                'sdpBindType' => $value['sdpBindType'],
                'sdpBindVcType' => $value['sdpBindVcType'],
                'sdpBindBaseStatsIngFwdPackets' => $value['sdpBindBaseStatsIngressForwardedPackets'],
                'sdpBindBaseStatsIngFwdOctets' => $value['sdpBindBaseStatsIngFwdOctets'],
                'sdpBindBaseStatsEgrFwdPackets' => $value['sdpBindBaseStatsEgressForwardedPackets'],
                'sdpBindBaseStatsEgrFwdOctets' => $value['sdpBindBaseStatsEgressForwardedOctets'],
            ]));
        }
        return $binds;
    }

    /**
     * @return Collection MplsLsp objects
     */
    public function pollMplsLsps()
    {
        $mplsLspCache = snmpwalk_cache_multi_oid($this->getDevice(), 'vRtrMplsLspTable', [], 'TIMETRA-MPLS-MIB', 'nokia');
        if (!empty($mplsLspCache)) {
            $mplsLspCache = snmpwalk_cache_multi_oid($this->getDevice(), 'vRtrMplsLspLastChange', $mplsLspCache, 'TIMETRA-MPLS-MIB', 'nokia', '-OQUst');
            $mplsLspCache = snmpwalk_cache_multi_oid($this->getDevice(), 'vRtrMplsLspStatTable', $mplsLspCache, 'TIMETRA-MPLS-MIB', 'nokia');
        }

        $lsps = collect();
        foreach ($mplsLspCache as $key => $value) {
            list($vrf_oid, $lsp_oid) = explode('.', $key);
            $lsps->push(new MplsLsp([
                'vrf_oid' => $vrf_oid,
                'lsp_oid' => $lsp_oid,
                'device_id' => $this->getDeviceId(),
                'mplsLspRowStatus' => $value['vRtrMplsLspRowStatus'],
                'mplsLspLastChange' => round($value['vRtrMplsLspLastChange'] / 100),
                'mplsLspName' => $value['vRtrMplsLspName'],
                'mplsLspAdminState' => $value['vRtrMplsLspAdminState'],
                'mplsLspOperState' => $value['vRtrMplsLspOperState'],
                'mplsLspFromAddr' => $value['vRtrMplsLspFromAddr'],
                'mplsLspToAddr' => $value['vRtrMplsLspToAddr'],
                'mplsLspType' => $value['vRtrMplsLspType'],
                'mplsLspFastReroute' => $value['vRtrMplsLspFastReroute'],
                'mplsLspAge' => abs($value['vRtrMplsLspAge']),
                'mplsLspTimeUp' => abs($value['vRtrMplsLspTimeUp']),
                'mplsLspTimeDown' => abs($value['vRtrMplsLspTimeDown']),
                'mplsLspPrimaryTimeUp' => abs($value['vRtrMplsLspPrimaryTimeUp']),
                'mplsLspTransitions' => $value['vRtrMplsLspTransitions'],
                'mplsLspLastTransition' => abs(round($value['vRtrMplsLspLastTransition'] / 100)),
                'mplsLspConfiguredPaths' => $value['vRtrMplsLspConfiguredPaths'],
                'mplsLspStandbyPaths' => $value['vRtrMplsLspStandbyPaths'],
                'mplsLspOperationalPaths' => $value['vRtrMplsLspOperationalPaths'],
            ]));
        }

        return $lsps;
    }

    /**
     * @param Collection $lsps collecton of synchronized lsp objects from pollMplsLsps()
     * @return Collection MplsLspPath objects
     */
    public function pollMplsPaths($lsps)
    {
        $mplsPathCache = snmpwalk_cache_multi_oid($this->getDevice(), 'vRtrMplsLspPathTable', [], 'TIMETRA-MPLS-MIB', 'nokia');
        if (!empty($mplsPathCache)) {
            $mplsPathCache = snmpwalk_cache_multi_oid($this->getDevice(), 'vRtrMplsLspPathLastChange', $mplsPathCache, 'TIMETRA-MPLS-MIB', 'nokia', '-OQUst');
            $mplsPathCache = snmpwalk_cache_multi_oid($this->getDevice(), 'vRtrMplsLspPathStatTable', $mplsPathCache, 'TIMETRA-MPLS-MIB', 'nokia');
        }

        $paths = collect();
        foreach ($mplsPathCache as $key => $value) {
            list($vrf_oid, $lsp_oid, $path_oid) = explode('.', $key);
            $lsp_id = $lsps->where('lsp_oid', $lsp_oid)->firstWhere('vrf_oid', $vrf_oid)->lsp_id;
            $paths->push(new MplsLspPath([
                'lsp_id' => $lsp_id,
                'path_oid' => $path_oid,
                'device_id' => $this->getDeviceId(),
                'mplsLspPathRowStatus' => $value['vRtrMplsLspPathRowStatus'],
                'mplsLspPathLastChange' => round($value['vRtrMplsLspPathLastChange'] / 100),
                'mplsLspPathType' => $value['vRtrMplsLspPathType'],
                'mplsLspPathBandwidth' => $value['vRtrMplsLspPathBandwidth'],
                'mplsLspPathOperBandwidth' => $value['vRtrMplsLspPathOperBandwidth'],
                'mplsLspPathAdminState' => $value['vRtrMplsLspPathAdminState'],
                'mplsLspPathOperState' => $value['vRtrMplsLspPathOperState'],
                'mplsLspPathState' => $value['vRtrMplsLspPathState'],
                'mplsLspPathFailCode' => $value['vRtrMplsLspPathFailCode'],
                'mplsLspPathFailNodeAddr' => $value['vRtrMplsLspPathFailNodeAddr'],
                'mplsLspPathMetric' => $value['vRtrMplsLspPathMetric'],
                'mplsLspPathOperMetric' => $value['vRtrMplsLspPathOperMetric'],
                'mplsLspPathTimeUp' => abs($value['vRtrMplsLspPathTimeUp']),
                'mplsLspPathTimeDown' => abs($value['vRtrMplsLspPathTimeDown']),
                'mplsLspPathTransitionCount' => $value['vRtrMplsLspPathTransitionCount'],
            ]));
        }

        return $paths;
    }

    /**
     * @return Collection MplsSdp objects
     */
    public function pollMplsSdps()
    {
        $mplsSdpCache = snmpwalk_cache_multi_oid($this->getDevice(), 'sdpInfoTable', [], 'TIMETRA-SDP-MIB', 'nokia', '-OQUst');

        $sdps = collect();
        foreach ($mplsSdpCache as $value) {
            if ((!empty($value['sdpFarEndInetAddress'])) && ($value['sdpFarEndInetAddressType'] == 'ipv4')) {
                $ip = long2ip(hexdec(str_replace(' ', '', $value['sdpFarEndInetAddress'])));
            } else {
                #Fixme implement ipv6 conversion
                $ip = $value['sdpFarEndInetAddress'];
            }
            $sdps->push(new MplsSdp([
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
                'sdpActiveLspType' => $value['sdpActiveLspType'],
                'sdpFarEndInetAddressType' => $value['sdpFarEndInetAddressType'],
                'sdpFarEndInetAddress' => $ip,
            ]));
        }

        return $sdps;
    }

    /**
     * @return Collection MplsService objects
     */
    public function pollMplsServices()
    {
        $mplsSvcCache = snmpwalk_cache_multi_oid($this->getDevice(), 'svcBaseInfoTable', [], 'TIMETRA-SERV-MIB', 'nokia', '-OQUst');
        $mplsSvcCache = snmpwalk_cache_multi_oid($this->getDevice(), 'svcTlsInfoTable', $mplsSvcCache, 'TIMETRA-SERV-MIB', 'nokia', '-OQUst');

        $svcs = collect();

        // Workaround, remove some default entries we do not want to see
        $filter = '/^\w* Service for internal purposes only/';

        foreach ($mplsSvcCache as $key => $value) {
            $garbage = preg_match($filter, $value['svcDescription']);
            if ($garbage) {
                unset($key);
                continue;
            }
            $svcs->push(new MplsService([
                'svc_oid' => $value['svcId'],
                'device_id' => $this->getDeviceId(),
                'svcRowStatus' => $value['svcRowStatus'],
                'svcType' => $value['svcType'],
                'svcCustId' => $value['svcCustId'],
                'svcAdminStatus' => $value['svcAdminStatus'],
                'svcOperStatus' => $value['svcOperStatus'],
                'svcDescription' => $value['svcDescription'],
                'svcMtu' => $value['svcMtu'],
                'svcNumSaps' => $value['svcNumSaps'],
                'svcNumSdps' => $value['svcNumSdps'],
                'svcLastMgmtChange' => round($value['svcLastMgmtChange'] / 100),
                'svcLastStatusChange' => round($value['svcLastStatusChange'] / 100),
                'svcVRouterId' => $value['svcVRouterId'],
                'svcTlsMacLearning' => $value['svcTlsMacLearning'],
                'svcTlsStpAdminStatus' => $value['svcTlsStpAdminStatus'],
                'svcTlsStpOperStatus' => $value['svcTlsStpOperStatus'],
                'svcTlsFdbTableSize' => $value['svcTlsFdbTableSize'],
                'svcTlsFdbNumEntries' => $value['svcTlsFdbNumEntries'],
            ]));
        }

        return $svcs;
    }

    /**
     * @return Collection MplsSap objects
     */
    public function pollMplsSaps($svcs)
    {
        $mplsSapCache = snmpwalk_cache_multi_oid($this->getDevice(), 'sapBaseInfoTable', [], 'TIMETRA-SAP-MIB', 'nokia', '-OQUst');
        $portScheme = snmp_get($this->getDevice(), 'tmnxChassisPortIdScheme.1', '-Oqv', 'TIMETRA-CHASSIS-MIB', 'nokia');

        $saps = collect();

        // Workaround, there are some oids not covered by actual MIB, try to filter them
        // i.e. sapBaseInfoEntry.300.118208001.1342177283.10
        $filter_key = '/300\.[0-9]+\.[0-9]+\.[0-9]+/';
        // remove some default entries we do not want to see
        $filter_value = '/^Internal SAP/';

        foreach ($mplsSapCache as $key => $value) {
            if (preg_match($filter_key, $key) || preg_match($filter_value, $value['sapDescription'])) {
                unset($key);
                continue;
            }
            list($svcId, $sapPortId, $sapEncapValue) = explode('.', $key);
            $svc_id = $svcs->firstWhere('svc_oid', $svcId)->svc_id;
            $saps->push(new MplsSap([
                'svc_id' => $svc_id,
                'svc_oid' => $svcId,
                'sapPortId' => $sapPortId,
                'ifName' => $this->nokiaIfName($sapPortId, $portScheme),
                'device_id' => $this->getDeviceId(),
                'sapEncapValue' => $this->nokiaEncap($sapEncapValue),
                'sapRowStatus' => $value['sapRowStatus'],
                'sapType' => $value['sapType'],
                'sapDescription' => $value['sapDescription'],
                'sapAdminStatus' => $value['sapAdminStatus'],
                'sapOperStatus' => $value['sapOperStatus'],
                'sapLastMgmtChange' => round($value['sapLastMgmtChange'] / 100),
                'sapLastStatusChange' => round($value['sapLastStatusChange'] /100),
            ]));
        }
        return $saps;
    }

    /**
     * @return Collection MplsSDpBind objects
     */
    public function pollMplsSdpBinds($sdps, $svcs)
    {
        $mplsBindCache = snmpwalk_cache_multi_oid($this->getDevice(), 'sdpBindTable', [], 'TIMETRA-SDP-MIB', 'nokia', '-OQUsbt');
        $mplsBindCache = snmpwalk_cache_multi_oid($this->getDevice(), 'sdpBindBaseStatsTable', $mplsBindCache, 'TIMETRA-SDP-MIB', 'nokia', '-OQUsb');

        $binds = collect();
        foreach ($mplsBindCache as $value) {
            $bind_id = str_replace(' ', '', $value['sdpBindId']);
            $sdp_oid = hexdec(substr($bind_id, 0, 8));
            $svc_oid = hexdec(substr($bind_id, 9, 16));
            $sdp_id = $sdps->firstWhere('sdp_oid', $sdp_oid)->sdp_id;
            $svc_id = $svcs->firstWhere('svc_oid', $svc_oid)->svc_id;
            $binds->push(new MplsSdpBind([
                'sdp_id' => $sdp_id,
                'svc_id' => $svc_id,
                'sdp_oid' => $sdp_oid,
                'svc_oid' => $svc_oid,
                'device_id' => $this->getDeviceId(),
                'sdpBindRowStatus' => $value['sdpBindRowStatus'],
                'sdpBindAdminStatus' => $value['sdpBindAdminStatus'],
                'sdpBindOperStatus' => $value['sdpBindOperStatus'],
                'sdpBindLastMgmtChange' => round($value['sdpBindLastMgmtChange'] / 100),
                'sdpBindLastStatusChange' => round($value['sdpBindLastStatusChange'] / 100),
                'sdpBindType' => $value['sdpBindType'],
                'sdpBindVcType' => $value['sdpBindVcType'],
                'sdpBindBaseStatsIngFwdPackets' => $value['sdpBindBaseStatsIngressForwardedPackets'],
                'sdpBindBaseStatsIngFwdOctets' => $value['sdpBindBaseStatsIngFwdOctets'],
                'sdpBindBaseStatsEgrFwdPackets' => $value['sdpBindBaseStatsEgressForwardedPackets'],
                'sdpBindBaseStatsEgrFwdOctets' => $value['sdpBindBaseStatsEgressForwardedOctets'],
            ]));
        }

        return $binds;
    }
}
