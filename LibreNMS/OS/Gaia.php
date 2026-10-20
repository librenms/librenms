<?php

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\Ipv4Address;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\RRD\RrdDefinition;
use SnmpQuery;

class Gaia extends \LibreNMS\OS implements OSPolling
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
        $ips = parent::discoverIpv4Addresses();

        $vsids = SnmpQuery::mibs(['CHECKPOINT-MIB'])->hideMib()
            ->walk('CHECKPOINT-MIB::svnNetIfVsid')->values();
        if (! self::isVirtualSystem($vsids)) {
            return $ips;
        }

        $clusterIps = $this->discoverClusterIps($ips->mapWithKeys(fn ($ip) => [$ip->port_id => $ip->context_name]));
        $clusterPortIds = $clusterIps->pluck('port_id')->all();

        return $ips->reject(fn ($ip) => in_array($ip->port_id, $clusterPortIds, true))
            ->merge($clusterIps);
    }

    /**
     * @param  array<int|string, mixed>  $vsids
     */
    public static function isVirtualSystem(array $vsids): bool
    {
        return collect($vsids)->map(fn ($vsid) => (int) $vsid)->min() >= 1;
    }

    /**
     * @param  Collection<int, string>  $contextByPortId
     * @return Collection<int, Ipv4Address>
     */
    private function discoverClusterIps(Collection $contextByPortId): Collection
    {
        return SnmpQuery::mibs(['CHECKPOINT-MIB'])->hideMib()->enumStrings()
            ->walk('CHECKPOINT-MIB::haClusterIpTable')
            ->mapTable(function ($data) use ($contextByPortId) {
                $ifName = $data['haClusterIpIfName'] ?? null;
                $addr = $data['haClusterIpAddr'] ?? null;
                if (! $ifName || ! $addr || $addr === '0.0.0.0') {
                    return null;
                }

                $port_id = PortCache::getIdFromIfName($ifName, $this->getDevice());
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
    }
}
