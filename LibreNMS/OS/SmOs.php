<?php

namespace LibreNMS\OS;

use App\Models\Transceiver;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessMseDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\OS;
use SnmpQuery;

class SmOs extends OS implements
    TransceiverDiscovery,
    WirelessRateDiscovery,
    WirelessRssiDiscovery,
    WirelessPowerDiscovery,
    WirelessFrequencyDiscovery,
    WirelessMseDiscovery,
    WirelessSnrDiscovery
{
    private $radioLabels;
    private $linkLabels;

    public function discoverWirelessRate()
    {
        $oids = snmpwalk_group($this->getDeviceArray(), 'linkTxETHCapacity', 'SIAE-RADIO-SYSTEM-MIB', 2);
        $oids = snmpwalk_group($this->getDeviceArray(), 'linkRxETHCapacity', 'SIAE-RADIO-SYSTEM-MIB', 2, $oids);
        $sensors = [];

        foreach ($oids as $link => $radioEntry) {
            $totalOids = ['rx' => [], 'tx' => []];

            foreach ($radioEntry as $radio => $entry) {
                $index = "$link.$radio";
                if (isset($entry['linkTxETHCapacity'])) {
                    $txOid = '.1.3.6.1.4.1.3373.1103.80.17.1.10.' . $index;
                    $totalOids['tx'][] = $txOid;
                    $sensors[] = new WirelessSensor(
                        'rate',
                        $this->getDeviceId(),
                        $txOid,
                        'tx',
                        $index,
                        $this->getLinkLabel($link) . ' Tx ' . $this->getRadioLabel($radio),
                        $entry['linkTxETHCapacity'],
                        1000
                    );
                }

                if (isset($entry['linkRxETHCapacity'])) {
                    $rxOid = '.1.3.6.1.4.1.3373.1103.80.17.1.11.' . $index;
                    $totalOids['rx'][] = $rxOid;
                    $sensors[] = new WirelessSensor(
                        'rate',
                        $this->getDeviceId(),
                        $rxOid,
                        'rx',
                        $index,
                        $this->getLinkLabel($link) . ' Rx ' . $this->getRadioLabel($radio),
                        $entry['linkRxETHCapacity'],
                        1000
                    );
                }
            }

            if (! empty($totalOids['rx'])) {
                $sensors[] = new WirelessSensor(
                    'rate',
                    $this->getDeviceId(),
                    $totalOids['rx'],
                    'total-rx',
                    $index,
                    $this->getLinkLabel($link) . ' Total Rx',
                    array_sum(array_column($radioEntry, 'linkRxETHCapacity')),
                    1000
                );
            }

            if (! empty($totalOids['tx'])) {
                $sensors[] = new WirelessSensor(
                    'rate',
                    $this->getDeviceId(),
                    $totalOids['tx'],
                    'total-tx',
                    $index,
                    $this->getLinkLabel($link) . ' Total Tx',
                    array_sum(array_column($radioEntry, 'linkTxETHCapacity')),
                    1000
                );
            }
        }

        $sensors[] = new WirelessSensor(
            'rate',
            $this->getDeviceId(),
            '.1.3.6.1.4.1.3373.1103.15.4.1.17.1',
            'alfo80hdx-tx-rate',
            1,
            'Tx Rate',
            null,
            1000,
            1
        );

        $sensors[] = new WirelessSensor(
            'rate',
            $this->getDeviceId(),
            '.1.3.6.1.4.1.3373.1103.15.4.1.18.1',
            'alfo80hdx-rx-rate',
            2,
            'Rx Rate',
            null,
            1000,
            1
        );

        return $sensors;
    }

    public function discoverWirelessRssi()
    {
        $sensors[] = new WirelessSensor(
            'rssi',
            $this->getDeviceId(),
            '.1.3.6.1.4.1.3373.1103.39.2.1.12.1',
            'alfo80hdx-rx',
            1,
            'RSSI'
        );

        return $sensors;
    }

    public function discoverWirelessPower()
    {
        $oids = snmpwalk_cache_oid($this->getDeviceArray(), 'radioPrx', [], 'SIAE-RADIO-SYSTEM-MIB');
        $oids = snmpwalk_cache_oid($this->getDeviceArray(), 'radioPtx', $oids, 'SIAE-RADIO-SYSTEM-MIB');
        $sensors = [];

        foreach ($oids as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'power',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.3373.1103.80.12.1.3.' . $index,
                'sm-os',
                "radioPrx.$index",
                'Received Power Level',
                $entry['radioPrx']
            );
            $sensors[] = new WirelessSensor(
                'power',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.3373.1103.80.12.1.4.' . $index,
                'sm-os',
                "radioPtx.$index",
                'Transmitted Power Level',
                $entry['radioPtx']
            );
        }

        $oid = '.1.3.6.1.4.1.3373.1103.39.2.1.13.1';

        $sensors[] = new WirelessSensor('power', $this->getDeviceId(), $oid, 'alfo80hd-tx', 1, 'Tx Power');

        return $sensors;
    }

    public function discoverWirelessFrequency()
    {
        $oids = snmpwalk_cache_oid($this->getDeviceArray(), 'radioTxFrequency', [], 'SIAE-RADIO-SYSTEM-MIB');
        $sensors = [];

        foreach ($oids as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.3373.1103.80.9.1.4.' . $index,
                'sm-os',
                $index,
                'Tx ' . $this->getRadioLabel($index),
                $entry['radioTxFrequency'],
                1,
                1000
            );
        }

        $sensors[] = new WirelessSensor(
            'frequency',
            $this->getDeviceId(),
            '.1.3.6.1.4.1.3373.1103.39.2.1.2.1',
            'alfo80hdx-tx-freq',
            1,
            'Tx Frequency',
            null,
            1,
            1000
        );

        return $sensors;
    }

    public function discoverWirelessMse()
    {
        $oids = snmpwalk_cache_oid($this->getDeviceArray(), 'radioNormalizedMse', [], 'SIAE-RADIO-SYSTEM-MIB');
        $sensors = [];

        foreach ($oids as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'mse',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.3373.1103.80.12.1.5.' . $index,
                'sm-os',
                $index,
                $this->getRadioLabel($index),
                $entry['radioNormalizedMse']
            );
        }

        return $sensors;
    }

    /**
     * Discover wireless SNR.  This is in dB. Type is snr.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessSnr()
    {
        $radioStatusTable = SnmpQuery::hideMib()->walk('SIAE-RADIO-SYSTEM-MIB::radioStatusTable')->table(1);
        $sensors = [];

        foreach ($radioStatusTable as $index => $entry) {
            $oid = '.1.3.6.1.4.1.3373.1103.80.12.1.28.';
            $sensors[] = new WirelessSensor(
                'snr',
                $this->getDeviceId(),
                $oid . $index,
                'sm-os',
                $index,
                $this->getRadioLabel($index),
                $entry['radioXpd'],
                1,
                10
            );
        }

        return $sensors;
    }

    public function discoverTransceivers(): Collection
    {
        $ifIndexToPortId = $this->getDevice()->ports()->pluck('port_id', 'ifIndex');

        return SnmpQuery::cache()->walk('SIAE-SFP-MIB::sfpSerialIdTable')->mapTable(function ($data, $ifIndex) use ($ifIndexToPortId) {
            $distance = null;
            if ($data['SIAE-SFP-MIB::sfpLinkLength9u'] > 0) {
                $distance = $data['SIAE-SFP-MIB::sfpLinkLength9u'];
            } elseif ($data['SIAE-SFP-MIB::sfpLinkLength50u'] > 0) {
                $distance = $data['SIAE-SFP-MIB::sfpLinkLength50u'];
            } elseif ($data['SIAE-SFP-MIB::sfpLinkLength62p5u'] > 0) {
                $distance = $data['SIAE-SFP-MIB::sfpLinkLength62p5u'];
            } elseif ($data['SIAE-SFP-MIB::sfpLinkLengthCopper'] > 0) {
                $distance = $data['SIAE-SFP-MIB::sfpLinkLengthCopper'];
            }

            return new Transceiver([
                'port_id' => $ifIndexToPortId->get($ifIndex, 0),
                'index' => $ifIndex,
                'entity_physical_index' => $ifIndex,
                'type' => null,
                'vendor' => $data['SIAE-SFP-MIB::sfpVendorName'] ?? null,
                'date' => empty($data['SIAE-SFP-MIB::sfpVendorDateCode']) ? null : Carbon::createFromFormat('ymd', $data['SIAE-SFP-MIB::sfpVendorDateCode'])->toDateString(),
                'model' => $data['SIAE-SFP-MIB::sfpVendorPartNumber'] ?? null,
                'serial' => $data['SIAE-SFP-MIB::sfpVendorSN'] ?? null,
                'ddm' => empty($data['SIAE-SFP-MIB::sfpDiagMonitorCode']) ? 0 : 1,
                'distance' => $distance,
                'wavelength' => $data['SIAE-SFP-MIB::sfpWavelength'] ?? null,
            ]);
        });
    }

    public function getRadioLabel($index)
    {
        if (is_null($this->radioLabels)) {
            $this->radioLabels = snmpwalk_group($this->getDeviceArray(), 'radioLabel', 'SIAE-RADIO-SYSTEM-MIB');
        }

        return $this->radioLabels[$index]['radioLabel'] ?? $index;
    }

    public function getLinkLabel($index)
    {
        if (is_null($this->linkLabels)) {
            $this->linkLabels = snmpwalk_group($this->getDeviceArray(), 'linkLabel', 'SIAE-RADIO-SYSTEM-MIB');
        }

        return $this->linkLabels[$index]['linkLabel'] ?? $index;
    }
}
