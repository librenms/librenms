<?php

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Enum\WirelessSensorType;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessCapacityDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\OS;

class Mni extends OS implements
    WirelessPowerDiscovery,
    WirelessRateDiscovery,
    WirelessFrequencyDiscovery,
    WirelessCapacityDiscovery
{
    /**
     * Discover wireless tx or rx power. This is in dBm. Type is power.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessPower(): array
    {
        $radio_index = \SnmpQuery::cache()->get('MNI-PROTEUS-AMT-MIB::mnPrLinkStatLocalRadioIndex.0')->value();
        $transmit_oid_raw = '.1.3.6.1.4.1.3323.13.1.4.1.1.2.'; //"MNI-PROTEUS-AMT-MIB::mnPrPerfBaseTxPower"
        $receive_oid_raw = '.1.3.6.1.4.1.3323.13.1.4.1.1.3.'; //"MNI-PROTEUS-AMT-MIB::mnPrPerfBaseRSL"
        $receive_oid = $receive_oid_raw . $radio_index;
        $transmit_oid = $transmit_oid_raw . $radio_index;

        return [
            new WirelessSensor(
                WirelessSensorType::Power,
                $this->getDeviceId(),
                $transmit_oid,
                'mni-tx',
                0,
                'MNI Transmit',
                null,
                1,
                1
            ),
            new WirelessSensor(
                WirelessSensorType::Power,
                $this->getDeviceId(),
                $receive_oid,
                'mni-rx',
                0,
                'MNI Receive',
                null,
                1,
                1
            ),
        ];
    }

    /**
     * Discover wireless rate. This is in bps. Type is rate.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessRate(): array
    {
        $radio_index = \SnmpQuery::cache()->get('MNI-PROTEUS-AMT-MIB::mnPrLinkStatLocalRadioIndex.0')->value();
        $receive_oid_raw = '.1.3.6.1.4.1.3323.13.1.4.1.1.17.'; //"MNI-PROTEUS-AMT-MIB::mnPrPerfBaseLinkCapMbps";
        $receive_oid = $receive_oid_raw . $radio_index;

        return [
            new WirelessSensor(
                WirelessSensorType::Rate,
                $this->getDeviceId(),
                $receive_oid,
                'mni-rx-rate',
                0,
                'MNI Receive Rate',
                null,
                1000000,
                1
            ),
        ];
    }

    public function discoverWirelessCapacity(): array
    {
        return \SnmpQuery::walk('MNI-PROTEUS-AMT-MIB::mnPrPerfBaseLinkCapMbps')
            ->mapTable(fn ($data, $mnInterfaceIndex) => new WirelessSensor(
                WirelessSensorType::Capacity,
                $this->getDeviceId(),
                ".1.3.6.1.4.1.3323.13.1.4.1.1.17.$mnInterfaceIndex",
                'mni-capacity',
                $mnInterfaceIndex,
                "Rx capacity $mnInterfaceIndex",
                $data['MNI-PROTEUS-AMT-MIB::mnPrPerfBaseLinkCapMbps'] ?? 0,
                100000
            ))->all();
    }

    public function discoverWirelessFrequency(): array
    {
        return \SnmpQuery::walk('MNI-PROTEUS-AMT-MIB::mnPrRadStatODUFreqBand')
            ->mapTable(fn ($data, $mnRadioIndex) => new WirelessSensor(
                WirelessSensorType::Frequency,
                $this->getDeviceId(),
                ".1.3.6.1.4.1.3323.13.1.1.2.1.17.$mnRadioIndex",
                'mni-frequency',
                $mnRadioIndex,
                "ODU Frequency Band $mnRadioIndex",
                $data['MNI-PROTEUS-AMT-MIB::mnPrRadStatODUFreqBand'] ?? 0,
                1000000000
            ))->all();
    }
}
