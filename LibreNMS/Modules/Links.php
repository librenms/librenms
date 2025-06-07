<?php

namespace LibreNMS\Modules;

use App\Facades\PortCache;
use App\Models\Device;
use App\Models\Link;
use App\Models\Port;
use App\Observers\ModuleModelObserver;
use Illuminate\Support\Collection;
use LibreNMS\Config;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\LinksDiscovery;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;
use LibreNMS\Util\IP;
use LibreNMS\Util\Mac;
use LibreNMS\Util\StringHelpers;
use SnmpQuery;

class Links implements Module
{
    use SyncsModels;

    /**
     * @inheritDoc
     */
    public function dependencies(): array
    {
        return ['ports'];
    }

    /**
     * @inheritDoc
     */
    public function shouldDiscover(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice());
    }

    /**
     * @inheritDoc
     */
    public function shouldPoll(OS $os, ModuleStatus $status): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        $links = new Collection;

        if ($os instanceof LinksDiscovery) {
            $links = $os->discoverLinks();
        }

        if ($links->isEmpty()) {
            $links = $this->discoverLldp($os->getDevice());
        }

        $links->each(function (Link $link) use ($os) {
            $tmp = explode('#', $link->protocol);
            $link->protocol = $dp = $tmp[0];
            $ip = $tmp[1] ?? '';

            if (empty($link->remote_device_id) &&
                \LibreNMS\Util\Validate::hostname($link->remote_hostname) &&
                ! can_skip_discovery($link->remote_hostname, $link->remote_version) &&
                Config::get('autodiscovery.xdp') === true) {
                $link->remote_device_id = discover_new_device(
                    $link->remote_hostname, $os->getDeviceArray(), strtoupper($dp), Port::where('port_id', $link->local_port_id)->first()->toArray()
                ) ?: 0;
            }
            if (empty($link->remote_device_id) &&
                ! empty($ip) &&
                Config::get('discovery_by_ip', false) &&
                Config::get('autodiscovery.xdp') === true) { //name lookup failed, try with IP
                $link->remote_device_id = discover_new_device(
                    $ip, $os->getDeviceArray(), strtoupper($dp), Port::where('port_id', $link->local_port_id)->first()->toArray()
                ) ?: 0;
            }
        });

        ModuleModelObserver::observe(\App\Models\Link::class);
        $this->syncModels($os->getDevice(), 'links', $links);
    }

    /**
     * @inheritDoc
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        $this->discover($os);
    }

    /**
     * @inheritDoc
     */
    public function dataExists(Device $device): bool
    {
        return $device->links()->exists();
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): int
    {
        return $device->links()->delete();
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device, string $type): ?array
    {
        if ($type == 'polling') {
            return null;
        }

        return [
            'links' => $device->links()->get()->map->makeHidden([
                'id', 'local_device_id', 'laravel_through_key']),
        ];
    }

    private function discoverLldp(Device $device): Collection
    {
        $links = new Collection;

        $bridgeLocPortId = SnmpQuery::hideMib()->walk('BRIDGE-MIB::dot1dBasePortIfIndex')->table();
        $bridgeLocPortId = (! empty($bridgeLocPortId)) ? array_shift($bridgeLocPortId) : [];
        $lldpLocPortId = SnmpQuery::hideMib()->walk('LLDP-MIB::lldpLocPortId')->table();
        $lldpLocPortId = (! empty($lldpLocPortId)) ? array_shift($lldpLocPortId) : [];
        $lldpRows = SnmpQuery::hideMib()->enumStrings()->walk('LLDP-MIB::lldpRemTable')->table(3);
        $oidsv2 = SnmpQuery::hideMib()->enumStrings()->walk('LLDP-V2-MIB::lldpV2RemTable')->table(4);

        if (! empty($lldpRows)) {
            $oidsremadd = SnmpQuery::hideMib()->numeric()->walk('LLDP-MIB::lldpRemManAddrIfSubtype')->values();
            foreach ($oidsremadd as $key => $tmp1) {
                $res = preg_match("/1\.0\.8802\.1\.1\.2\.1\.4\.2\.1\.3\.([^\.]*)\.([^\.]*)\.([^\.]*)\.([^\.]*)\.([^\.]*).(([^\.]*)(\.([^\.]*))+)/", $key, $matches);
                if ($res) {
                    //collect the Management IP address from the OID
                    if ($matches[5] == 4) {
                        $lldpRows[$matches[1]][$matches[2]][$matches[3]]['lldpRemManAddr'] = $matches[6];
                    } else {
                        $ipv6 = implode(':', array_map(function ($v) {
                            return sprintf('%02x', $v);
                        },
                            explode('.', $matches[6])
                        ));
                        $ipv6 = preg_replace('/([^:]{2}):([^:]{2})/i', '$1$2', $ipv6);
                        $lldpRows[$matches[1]][$matches[2]][$matches[3]]['lldpRemManAddr'] = $ipv6;
                    }
                }
            }
        }

        if (! empty($oidsv2)) {
            foreach ($oidsv2 as $lldpV2RemTimeMark => $tmp1) {
                foreach ($tmp1 as $lldpV2RemLocalIfIndex => $tmp2) {
                    foreach ($tmp2 as $lldpV2RemLocalDestMACAddress => $tmp3) {
                        foreach ($tmp3 as $lldpV2RemIndex => $data) {
                            foreach ($data as $key => $value) {
                                $newKey = str_replace('lldpV2', 'lldp', $key);
                                $lldpRows[$lldpV2RemTimeMark][$lldpV2RemLocalIfIndex][$lldpV2RemIndex][$newKey] = $value;
                            }
                            $lldpRows[$lldpV2RemTimeMark][$lldpV2RemLocalIfIndex][$lldpV2RemIndex]['lldpRemLocalDestMACAddress'] = $lldpV2RemLocalDestMACAddress;
                        }
                    }
                }
            }
        }

        $lldpKeys = ['lldpRemChassisIdSubtype', 'lldpRemChassisId', 'lldpRemPortIdSubtype', 'lldpRemPortId', 'lldpRemSysName', 'lldpRemSysDesc'];
        foreach ($lldpRows as $lldpRemTimeMark => $tmp1) {
            foreach ($tmp1 as $lldpRemLocalPortNum => $tmp2) {
                foreach ($tmp2 as $lldpRemIndex => $data) {
                    foreach ($lldpKeys as $keyName) {
                        $data[$keyName] = $data[$keyName] ?? '';
                    }

                    // fill the $data structure, in case we need to do debugging
                    $data['lldpRemLocalPortNum'] = $lldpRemLocalPortNum;
                    $data['lldpRemIndex'] = $lldpRemIndex;
                    $data['lldpRemTimeMark'] = $lldpRemTimeMark;

                    // lldpRemLocalPortNum is a local index for LLDP, not an ifIndex
                    // There is no path to ifindex, only to ifName, stored in lldpLocPortId
                    $data['lldpLocPortId'] = $lldpLocPortId[$lldpRemLocalPortNum] ?? null;
                    $data['localPortId'] = null;

                    if (empty($data['localPortId']) && ! empty($data['lldpLocPortId'])) {
                        // This should be the standard LLDP behaviour
                        $data['localPortId'] = PortCache::getIdFromIfName($data['lldpLocPortId'], $device);
                    }
                    if (empty($data['localPortId'])) {
                        $idx = $lldpRemLocalPortNum; // This should not happen, not MIB compliant
                        $data['localPortId'] = PortCache::getIdFromIfIndex($idx, $device) ?? 0;
                    }

                    if (empty($data['localPortId']) && ! empty($data['lldpLocPortId'])) {
                        // $data['lldpLocPortId'] should not be an ifIndex according to MIB but let's try...
                        $data['localPortId'] = PortCache::getIdFromIfIndex($data['lldpLocPortId'], $device);
                    }
                    $data['lldpRemSysName'] = substr($data['lldpRemSysName'], 0, 64);
                    $remoteMac = $remotePortName = '';

                    if ($data['lldpRemPortIdSubtype'] == 'interfaceName' || $data['lldpRemPortIdSubtype'] == 'interfaceAlias') {
                        $tmpName = $data['lldpRemPortDescr'] ?? $data['lldpRemPortId'];
                        $remotePortName = StringHelpers::linksRemPortName($data['lldpRemSysDesc'] ?? '', $tmpName ?? '');
                    }

                    if ($data['lldpRemPortIdSubtype'] == 'local') {
                        $remoteMac = Mac::parse($data['lldpRemPortId'])->hex();
                        $remotePortName = $data['lldpRemPortId'];
                    }

                    if ($data['lldpRemChassisIdSubtype'] == 'macAddress') {
                        $remoteMac = (strlen($data['lldpRemChassisId']) < 18)
                            ? Mac::parse($data['lldpRemChassisId'])->hex()
                            : str_replace([' ', ':', '-'], '', strtolower($data['lldpRemChassisId']));
                    }

                    if ($data['lldpRemPortIdSubtype'] == 'macAddress') {
                        $remoteMac = (strlen($data['lldpRemPortId']) < 18)
                            ? Mac::parse($data['lldpRemPortId'])->hex()
                            : str_replace([' ', ':', '-'], '', strtolower($data['lldpRemPortId']));
                        $remotePortName = $data['lldpRemPortId'];
                    }

                    if ($data['lldpRemChassisIdSubtype'] == 'interfaceName') {
                        $remotePortName = $data['lldpRemChassisId'];
                    }

                    $remoteSysName = StringHelpers::linksRemSysName($data['lldpRemSysName']);
                    $remoteSysName = (empty($remoteSysName)) ? StringHelpers::linksRemSysName($data['lldpRemSysDesc']) : $remoteSysName;

                    $remoteDeviceIp = $data['lldpRemManAddr'] ?? '';
                    $remoteDeviceId = find_device_id($remoteSysName, $remoteDeviceIp, $remoteMac);

                    $remotePortId = find_port_id($data['lldpRemPortDescr'] ?? null, $data['lldpRemPortId'], $remoteDeviceId);
                    $remotePortName = (empty($remotePortId)) ? $remotePortName . ' (' . $remoteMac . ')' : $remotePortName;

                    if (! empty($data['localPortId']) && (! empty($remoteSysName))) {
                        $suffix = (! empty($data['lldpRemManAddr'])) ? '#' . $data['lldpRemManAddr'] : '';
                        $links->push(new Link([
                            'local_port_id' => $data['localPortId'],
                            'remote_hostname' => $remoteSysName,
                            'remote_device_id' => $remoteDeviceId,
                            'remote_port_id' => $remotePortId ?? 0,
                            'active' => 1,
                            'protocol' => 'lldp' . $suffix,
                            'remote_port' => $remotePortName,
                            'remote_platform' => null,
                            'remote_version' => $data['lldpRemSysDesc'] ?? '',
                        ]));
                    }
                }
            }
        }

        return $links->filter();
    }
}
