<?php

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessErrorsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\OS;

class HorizonQuantum extends OS implements
    WirelessSnrDiscovery,
    WirelessPowerDiscovery,
    WirelessRssiDiscovery,
    WirelessErrorsDiscovery,
    WirelessRateDiscovery
{
    public function discoverWirelessSnr()
    {
        $index = snmpwalk_group($this->getDeviceArray(), 'hzQtmModemIndex', 'DRAGONWAVE-HORIZON-QUANTUM-MIB');
        $data = snmpwalk_group($this->getDeviceArray(), 'hzQtmModemSNR', 'DRAGONWAVE-HORIZON-QUANTUM-MIB');
        $sensors = [];
        foreach ($data as $oid => $snr_value) {
            if ($snr_value['hzQtmModemSNR'] != '-99') {
                $sensors[] = new WirelessSensor(
                    'snr',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.7262.2.4.5.2.1.1.8.' . $index[$oid]['hzQtmModemIndex'],
                    'horizon-quantum',
                    $oid,
                    $oid . ' SNR',
                    null,
                    1,
                    10
                );
            }
        }

        return $sensors;
    }

    public function discoverWirelessPower()
    {
        $index = snmpwalk_group($this->getDeviceArray(), 'hzQtmRadioIndex', 'DRAGONWAVE-HORIZON-QUANTUM-MIB');
        $data = snmpwalk_group($this->getDeviceArray(), 'hzQtmRadioActualTransmitPowerdBm', 'DRAGONWAVE-HORIZON-QUANTUM-MIB');
        $sensors = [];
        foreach ($data as $oid => $power_value) {
            if ($power_value['hzQtmRadioActualTransmitPowerdBm'] != '-99') {
                $sensors[] = new WirelessSensor(
                    'power',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.7262.2.4.5.4.1.1.19.' . $index[$oid]['hzQtmRadioIndex'],
                    'horizon-quantum-tx',
                    $oid,
                    $oid . ' TX Power',
                    null,
                    1,
                    10
                );
            }
        }

        return $sensors;
    }

    public function discoverWirelessRssi()
    {
        $index = snmpwalk_group($this->getDeviceArray(), 'hzQtmModemIndex', 'DRAGONWAVE-HORIZON-QUANTUM-MIB');
        $data = snmpwalk_group($this->getDeviceArray(), 'hzQtmModemChannelizedRSL', 'DRAGONWAVE-HORIZON-QUANTUM-MIB');
        $sensors = [];
        foreach ($data as $oid => $rssi_value) {
            if ($rssi_value['hzQtmModemChannelizedRSL'] != '-99') {
                $sensors[] = new WirelessSensor(
                    'rssi',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.7262.2.4.5.2.1.1.3.' . $index[$oid]['hzQtmModemIndex'],
                    'horizon-quantum',
                    $oid,
                    $oid . ' RSSI',
                    null,
                    1,
                    10
                );
            }
        }

        return $sensors;
    }

    public function discoverWirelessErrors()
    {
        $index = snmpwalk_group($this->getDeviceArray(), 'hzQtmWirelessEnetPortIndex', 'DRAGONWAVE-HORIZON-QUANTUM-MIB');
        $data = snmpwalk_group($this->getDeviceArray(), 'hzQtmWirelessEnetPortRxFramesErrors', 'DRAGONWAVE-HORIZON-QUANTUM-MIB');
        $sensors = [];
        foreach ($data as $oid => $errors_value) {
            if ($errors_value['hzQtmWirelessEnetPortRxFramesErrors'] != '-99') {
                $sensors[] = new WirelessSensor(
                    'errors',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.7262.2.4.5.2.3.1.4.' . $index[$oid]['hzQtmWirelessEnetPortIndex'],
                    'horizon-quantum-rx',
                    $oid,
                    $oid . ' Rx Errors'
                );
            }
        }

        return $sensors;
    }

    public function discoverWirelessRate()
    {
        $sensors = [];
        $index = snmpwalk_group($this->getDeviceArray(), 'hzQtmModemIndex', 'DRAGONWAVE-HORIZON-QUANTUM-MIB');

        $data = snmpwalk_group($this->getDeviceArray(), 'hzQtmModemRxSpeed', 'DRAGONWAVE-HORIZON-QUANTUM-MIB');
        foreach ($data as $oid => $rate_value) {
            if ($rate_value['hzQtmModemRxSpeed'] != '-99') {
                $sensors[] = new WirelessSensor(
                    'rate',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.7262.2.4.5.2.1.1.6.' . $index[$oid]['hzQtmModemIndex'],
                    'horizon-quantum-rx',
                    $oid,
                    $oid . ' Rx Rate',
                    null,
                    1,
                    10
                );
            }
        }

        $data = snmpwalk_group($this->getDeviceArray(), 'hzQtmModemTxSpeed', 'DRAGONWAVE-HORIZON-QUANTUM-MIB');
        foreach ($data as $oid => $rate_value) {
            if ($rate_value['hzQtmModemTxSpeed'] != '-99') {
                $sensors[] = new WirelessSensor(
                    'rate',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.7262.2.4.5.2.1.1.7.' . $index[$oid]['hzQtmModemIndex'],
                    'horizon-quantum-tx',
                    $oid,
                    $oid . ' Tx Rate',
                    null,
                    1,
                    10
                );
            }
        }

        return $sensors;
    }
}
