<?php

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\Ipv4Address;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\Ipv4AddressDiscovery;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\IPv4;
use SnmpQuery;

class Gaia extends \LibreNMS\OS implements Ipv4AddressDiscovery, OSPolling
{
    public function pollOS(DataStorageInterface $datastore): void
    {
        $oids = ['fwLoggingHandlingRate.0', 'mgLSLogReceiveRate.0', 'fwNumConn.0', 'fwAccepted.0', 'fwRejected.0', 'fwDropped.0', 'fwLogged.0'];

        $data = snmp_get_multi($this->getDeviceArray(), $oids, '-OQUs', 'CHECKPOINT-MIB');

        //#############
        // Create firewall lograte/handlingrate rrd
        //#############
        if (is_numeric($data[0]['fwLoggingHandlingRate'] ?? null)) {
            $rrd_def = RrdDefinition::make()->addDataset('fwlograte', 'GAUGE', 0);

            $fields = [
                'fwlograte' => $data[0]['fwLoggingHandlingRate'],
            ];

            $tags = ['rrd_def' => $rrd_def];
            $datastore->put($this->getDeviceArray(), 'gaia_firewall_lograte', $tags, $fields);
            $this->enableGraph('gaia_firewall_lograte');
        }

        //#############
        // Create MGMT logserver lograte rrd
        //#############
        if (is_numeric($data[0]['mgLSLogReceiveRate'] ?? null)) {
            $rrd_def = RrdDefinition::make()->addDataset('LogReceiveRate', 'GAUGE', 0);

            $fields = [
                'LogReceiveRate' => $data[0]['mgLSLogReceiveRate'],
            ];

            $tags = ['rrd_def' => $rrd_def];
            $datastore->put($this->getDeviceArray(), 'gaia_logserver_lograte', $tags, $fields);
            $this->enableGraph('gaia_logserver_lograte');
        }

        //#############
        // Create firewall active connections rrd
        //#############
        if (is_numeric($data[0]['fwNumConn'] ?? null)) {
            $rrd_def = RrdDefinition::make()->addDataset('NumConn', 'GAUGE', 0);

            $fields = [
                'NumConn' => $data[0]['fwNumConn'],
            ];

            $tags = ['rrd_def' => $rrd_def];
            $datastore->put($this->getDeviceArray(), 'gaia_connections', $tags, $fields);
            $this->enableGraph('gaia_connections');
        }

        //#############
        // Create firewall packets rrd
        //#############
        if (is_numeric($data[0]['fwAccepted'] ?? null) && is_numeric($data[0]['fwRejected'] ?? null) && is_numeric($data[0]['fwDropped'] ?? null) && is_numeric($data[0]['fwLogged'] ?? null)) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('accepted', 'DERIVE', 0)
                ->addDataset('rejected', 'DERIVE', 0)
                ->addDataset('dropped', 'DERIVE', 0)
                ->addDataset('logged', 'DERIVE', 0);

            $fields = [
                'accepted' => $data[0]['fwAccepted'],
                'rejected' => $data[0]['fwRejected'],
                'dropped' => $data[0]['fwDropped'],
                'logged' => $data[0]['fwLogged'],
            ];

            $tags = ['rrd_def' => $rrd_def];
            $datastore->put($this->getDeviceArray(), 'gaia_firewall_packets', $tags, $fields);
            $this->enableGraph('gaia_firewall_packets');
        }
    }

    public function discoverIpv4Addresses(): Collection
    {
        $device = $this->getDevice();

        $ips = new Collection;
        foreach ($device->getVrfContexts() as $context_name) {
            $ips = $ips->merge(SnmpQuery::context($context_name)->allowUnordered()->hideMib()->enumStrings()->walk(
                ['IP-MIB::ipAdEntAddr', 'IP-MIB::ipAdEntIfIndex', 'IP-MIB::ipAdEntNetMask']
            )->mapTable(function ($data, $ipAddr = '') use ($context_name, $device) {
                $entAddr = $data['ipAdEntAddr'] ?? '';
                $addr = preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', (string) $entAddr) ? $entAddr : $ipAddr;

                if (! IPv4::isValid($addr)) {
                    return null;
                }

                return new Ipv4Address([
                    'port_id' => PortCache::getIdFromIfIndex($data['ipAdEntIfIndex'] ?? 0, $device),
                    'ipv4_address' => $addr,
                    'ipv4_prefixlen' => $data['ipAdEntNetMask'] ?? '',
                    'context_name' => $context_name,
                ]);
            }));
        }
        $ips = $ips->filter();

        $vsids = SnmpQuery::mibs(['CHECKPOINT-MIB'])->hideMib()
            ->walk('CHECKPOINT-MIB::svnNetIfVsid')->values();
        if (collect($vsids)->map(fn ($vsid) => (int) $vsid)->max() < 1) {
            return $ips;
        }

        $contextByPortId = $ips->mapWithKeys(fn ($ip) => [$ip->port_id => $ip->context_name]);

        $clusterIps = SnmpQuery::mibs(['CHECKPOINT-MIB'])->hideMib()->enumStrings()
            ->walk('CHECKPOINT-MIB::haClusterIpTable')
            ->mapTable(function ($data) use ($device, $contextByPortId) {
                $ifName = $data['haClusterIpIfName'] ?? null;
                $addr = $data['haClusterIpAddr'] ?? null;
                if (! $ifName || ! $addr || $addr == '0.0.0.0') {
                    return null;
                }

                $port_id = PortCache::getIdFromIfName($ifName, $device);
                if (! $port_id) {
                    return null;
                }

                return new Ipv4Address([
                    'port_id' => $port_id,
                    'ipv4_address' => $addr,
                    'ipv4_prefixlen' => $data['haClusterIpNetMask'] ?? '',
                    'context_name' => $contextByPortId->get($port_id, ''),
                ]);
            })->filter();

        if ($clusterIps->isEmpty()) {
            return $ips;
        }

        $clusterPortIds = $clusterIps->pluck('port_id')->all();

        return $ips->reject(fn ($ip) => in_array($ip->port_id, $clusterPortIds, true))
            ->merge($clusterIps);
    }
}
