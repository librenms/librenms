<?php

namespace LibreNMS\OS;

use App\Models\Device;
use App\Models\EntPhysical;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use LibreNMS\Data\Source\Snmp\SnmpResponse;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Enum\WirelessSensorType;
use LibreNMS\Interfaces\Discovery\EntityPhysicalDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessApCountDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\OS;
use LibreNMS\Util\Oid;
use SnmpQuery;

class RuckuswirelessSz extends OS implements
    EntityPhysicalDiscovery,
    WirelessClientsDiscovery,
    WirelessApCountDiscovery
{
    public function discoverOS(Device $device): void
    {
        $sz = $this->getSystemStats();
        $device->serial = $sz->value('RUCKUS-SZ-SYSTEM-MIB::ruckusSZSystemStatsSerialNumber.0');

        $nodes = $this->getNodes();
        $system = $nodes[$device->serial] ?? Arr::first($nodes, default: []);

        $device->hardware = $system['RUCKUS-CTRL-MIB::ruckusCtrlSystemNodeModel'] ?? null;
        $device->version = $system['RUCKUS-CTRL-MIB::ruckusCtrlSystemNodeVersion'] ?? null;

        // licenses are pooled across all nodes in the cluster
        $ap_count = $sz->value('RUCKUS-SZ-SYSTEM-MIB::ruckusSZSystemStatsNumAP.0');
        $licenses = array_column($nodes, 'RUCKUS-CTRL-MIB::ruckusCtrlSystemNodeNumApLicense');
        $ap_license = empty($licenses) ? '?' : array_sum($licenses);
        $device->features = "Licenses: $ap_count/$ap_license";

        $ruckuscountry = SnmpQuery::next('RUCKUS-CTRL-MIB::ruckusCtrlZoneCountryCode')->value();
        if (! empty($ruckuscountry)) {
            $device->version .= " ($ruckuscountry)";
        }
    }

    public function discoverWirelessClients(): array
    {
        // clients - Discover Per SSID Client Count
        $sensors = [];
        $ssids = SnmpQuery::walk('RUCKUS-SZ-WLAN-MIB::ruckusSZWLANSSID')->pluck();
        $counts = SnmpQuery::walk('RUCKUS-SZ-WLAN-MIB::ruckusSZWLANNumSta')->pluck();

        $total = 0;
        foreach ($counts as $index => $count) {
            $oid = '.1.3.6.1.4.1.25053.1.4.2.1.1.1.2.1.12.' . $index;
            $total += $count;
            $ssid = $ssids[$index];

            $sensors[] = new WirelessSensor(
                WirelessSensorType::Clients,
                $this->getDeviceId(),
                $oid,
                'ruckuswireless-sz',
                $ssid,
                'SSID: ' . $ssid,
                $count
            );
        }

        // Do not get total client count if only 1 SSID
        if (count($counts) > 1) {
            // clients - Discover System Total Client Count
            $oid = '.1.3.6.1.4.1.25053.1.4.1.1.1.15.2.0'; //RUCKUS-SZ-SYSTEM-MIB::ruckusSZSystemStatsNumSta.0
            $sensors[] = new WirelessSensor(WirelessSensorType::Clients, $this->getDeviceId(), $oid, 'ruckuswireless-sz', 'total', 'System Total', $total);
        }

        return $sensors;
    }

    public function discoverWirelessApCount(): array
    {
        $apstatus = [];
        foreach ($this->getNodes() as $sn => $node) {
            if (isset($node['RUCKUS-CTRL-MIB::ruckusCtrlSystemNodeNumApConnected'])) {
                $oid = '.1.3.6.1.4.1.25053.1.8.1.1.1.1.1.1.19.' . Oid::encodeString($sn);
                $name = $node['RUCKUS-CTRL-MIB::ruckusCtrlSystemNodeName'] ?? $sn;
                $apstatus[] = new WirelessSensor(
                    WirelessSensorType::ApCount,
                    $this->getDeviceId(),
                    $oid,
                    'ruckuswireless-sz',
                    "$sn",
                    "Connected APs: $name",
                    $node['RUCKUS-CTRL-MIB::ruckusCtrlSystemNodeNumApConnected'],
                );
            }
        }

        // ap-count - Discover Connected APs per AP Zone, indexed by zone domain id + zone id (2x 16 byte UUID)
        $zones = SnmpQuery::numericIndex()->walk([
            'RUCKUS-CTRL-MIB::ruckusCtrlZoneName',
            'RUCKUS-CTRL-MIB::ruckusCtrlZoneNumApConnected',
        ])->valuesByIndex();
        foreach ($zones as $index => $zone) {
            if (isset($zone['RUCKUS-CTRL-MIB::ruckusCtrlZoneNumApConnected'])) {
                $zone_id = vsprintf(str_repeat('%02x', 16), array_slice(explode('.', $index), 16));
                $apstatus[] = new WirelessSensor(
                    WirelessSensorType::ApCount,
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.25053.1.8.1.1.1.1.3.1.9.' . $index,
                    'ruckuswireless-sz',
                    "zone.$zone_id",
                    'Connected APs zone: ' . ($zone['RUCKUS-CTRL-MIB::ruckusCtrlZoneName'] ?? $zone_id),
                    $zone['RUCKUS-CTRL-MIB::ruckusCtrlZoneNumApConnected'],
                );
            }
        }

        // ap-count - Discover System Total APs
        $total = (int) $this->getSystemStats()->value('RUCKUS-SZ-SYSTEM-MIB::ruckusSZSystemStatsNumAP.0');
        if ($total > 1) {
            $oid = '.1.3.6.1.4.1.25053.1.4.1.1.1.15.1.0'; //RUCKUS-SZ-SYSTEM-MIB::ruckusSZSystemStatsNumAP.0
            $apstatus[] = new WirelessSensor(WirelessSensorType::ApCount, $this->getDeviceId(), $oid, 'ruckuswireless-sz', 'total', 'Total APs', $total);
        }

        return $apstatus;
    }

    public function discoverEntityPhysical(): Collection
    {
        $inventory = new Collection;

        // sort by serial so the entPhysicalIndex stays stable regardless of walk order
        $nodes = $this->getNodes();
        ksort($nodes);

        $index = 1;
        foreach ($nodes as $sn => $node) {
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => $index++,
                'entPhysicalDescr' => $node['RUCKUS-CTRL-MIB::ruckusCtrlSystemNodeMgmtIp'] ?? null,
                'entPhysicalClass' => 'chassis',
                'entPhysicalName' => $node['RUCKUS-CTRL-MIB::ruckusCtrlSystemNodeName'] ?? $sn,
                'entPhysicalModelName' => $node['RUCKUS-CTRL-MIB::ruckusCtrlSystemNodeModel'] ?? null,
                'entPhysicalSerialNum' => $sn,
                'entPhysicalMfgName' => 'Ruckus Wireless',
                'entPhysicalSoftwareRev' => $node['RUCKUS-CTRL-MIB::ruckusCtrlSystemNodeVersion'] ?? null,
            ]));
        }

        return $inventory;
    }

    /**
     * Controller serial and total AP count, shared by os and wireless discovery
     */
    private function getSystemStats(): SnmpResponse
    {
        return SnmpQuery::cache()->get([
            'RUCKUS-SZ-SYSTEM-MIB::ruckusSZSystemStatsSerialNumber.0',
            'RUCKUS-SZ-SYSTEM-MIB::ruckusSZSystemStatsNumAP.0',
        ]);
    }

    /**
     * Controller nodes in this SmartZone cluster, keyed by node serial
     */
    private function getNodes(): array
    {
        return SnmpQuery::cache()->walk('RUCKUS-CTRL-MIB::ruckusCTRLSystemNodeTable')->table(1);
    }
}
