<?php

namespace LibreNMS\OS;

use App\Models\Device;
use Illuminate\Support\Arr;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessApCountDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\OS;
use LibreNMS\Util\Oid;
use SnmpQuery;

class RuckuswirelessSz extends OS implements
    WirelessClientsDiscovery,
    WirelessApCountDiscovery
{
    public function discoverOS(Device $device): void
    {
        $sz = SnmpQuery::get([
            'RUCKUS-SZ-SYSTEM-MIB::ruckusSZSystemStatsSerialNumber.0',
            'RUCKUS-SZ-SYSTEM-MIB::ruckusSZSystemStatsNumAP.0',
        ]);
        $device->serial = $sz->value('RUCKUS-SZ-SYSTEM-MIB::ruckusSZSystemStatsSerialNumber.0');

        $system = SnmpQuery::walk('RUCKUS-CTRL-MIB::ruckusCTRLSystemNodeTable')->table(1);
        $system = $system[$device->serial] ?? Arr::first($system, default: []);

        $device->hardware = $system['RUCKUS-CTRL-MIB::ruckusCtrlSystemNodeModel'] ?? null;
        $device->version = $system['RUCKUS-CTRL-MIB::ruckusCtrlSystemNodeVersion'] ?? null;

        $ap_count = $sz->value('RUCKUS-SZ-SYSTEM-MIB::ruckusSZSystemStatsNumAP.0');
        $ap_license = $system['RUCKUS-CTRL-MIB::ruckusCtrlSystemNodeNumApLicense'] ?? '?';
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
                'clients',
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
            $sensors[] = new WirelessSensor('clients', $this->getDeviceId(), $oid, 'ruckuswireless-sz', 'total', 'System Total', $total);
        }

        return $sensors;
    }

    // ap-count - Discover System Connected APs

    public function discoverWirelessApCount(): array
    {
        $apconnected = SnmpQuery::walk('RUCKUS-CTRL-MIB::ruckusCtrlSystemNodeNumApConnected')->table(1);

        $apstatus = [];
        foreach ($apconnected as $sn => $connect) {
            if (isset($connect['RUCKUS-CTRL-MIB::ruckusCtrlSystemNodeNumApConnected'])) {
                $oid = '.1.3.6.1.4.1.25053.1.8.1.1.1.1.1.1.19.' . Oid::encodeString($sn);
                $apstatus[] = new WirelessSensor(
                    'ap-count',
                    $this->getDeviceId(),
                    $oid,
                    'ruckuswireless-sz',
                    "$sn",
                    'Connected APs',
                    $connect['RUCKUS-CTRL-MIB::ruckusCtrlSystemNodeNumApConnected'],
                );
            }
        }

        // ap-count - Discover System Total APs
        $total = (int) SnmpQuery::get('RUCKUS-SZ-SYSTEM-MIB::ruckusSZSystemStatsNumAP.0')->value();
        if ($total > 1) {
            $oid = '.1.3.6.1.4.1.25053.1.4.1.1.1.15.1.0'; //RUCKUS-SZ-SYSTEM-MIB::ruckusSZSystemStatsNumAP.0
            $apstatus[] = new WirelessSensor('ap-count', $this->getDeviceId(), $oid, 'ruckuswireless-sz', 'total', 'Total APs', $total);
        }

        return $apstatus;
    }
}
