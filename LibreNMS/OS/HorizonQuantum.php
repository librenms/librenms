<?php

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessErrorsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\OS;

class HorizonQuantum extends OS implements WirelessSnrDiscovery, WirelessPowerDiscovery, WirelessRssiDiscovery, WirelessErrorsDiscovery
{
    public function discoverWirelessSnr()
    {
        $data = snmpwalk_group($this->getDeviceArray(), 'hzQtmModemSNR', 'DRAGONWAVE-HORIZON-QUANTUM-MIB');
        $sensors = [];
        foreach ($data as $index => $snr_value) {
            if ($snr_value['hzQtmModemSNR'] != '-99') {
                $sensors[] = new WirelessSensor('snr', $this->getDeviceId(), '.1.3.6.1.4.1.7262.2.4.5.2.1.1.8.' . $index, 'horizon-quantum' . $index, 'SNR radio ' . $index , $snr_value['hzQtmModemSNR'], 1, 10);
            }
        }

        return $sensors;
    }

    public function discoverWirelessPower()
    {
        $data = snmpwalk_group($this->getDeviceArray(), 'hzQtmRadioActualTransmitPowerdBm', 'DRAGONWAVE-HORIZON-QUANTUM-MIB');
        $sensors = [];
        foreach ($data as $index => $power_value) {
            if ($power_value['hzQtmRadioActualTransmitPowerdBm'] != '-99') {
                $sensors[] = new WirelessSensor('power', $this->getDeviceId(), '.1.3.6.1.4.1.7262.2.4.5.4.1.1.19.' . $index, 'horizon-quantum' . $index, 'TX power radio ' . $index , $power_value['hzQtmRadioActualTransmitPowerdBm'], 1, 10);
            }
        }

        return $sensors;
    }

    public function discoverWirelessRssi()
    {
        $data = snmpwalk_group($this->getDeviceArray(), 'hzQtmModemChannelizedRSL', 'DRAGONWAVE-HORIZON-QUANTUM-MIB');
        $sensors = [];
        foreach ($data as $index => $rssi_value) {
            if ($rssi_value['hzQtmModemChannelizedRSL'] != '-99') {
                $sensors[] = new WirelessSensor('rssi', $this->getDeviceId(), '.1.3.6.1.4.1.7262.2.4.5.2.1.1.3.' . $index, 'horizon-quantum' . $index, 'RSSI radio ' . $index , $power_value['hzQtmModemChannelizedRSL'], 1, 10);
            }
        }

        return $sensors;
    }

    public function discoverWirelessRssi()
    {
        $data = snmpwalk_group($this->getDeviceArray(), 'hzQtmModemRxBlocksErrors', 'DRAGONWAVE-HORIZON-QUANTUM-MIB');
        $sensors = [];
        foreach ($data as $index => $errors_value) {
            if ($errors_value['hzQtmModemRxBlocksErrors'] != '-99') {
                $sensors[] = new WirelessSensor('errors', $this->getDeviceId(), '.1.3.6.1.4.1.7262.2.4.5.4.1.1.19.' . $index, 'horizon-quantum' . $index, 'Rx Errors radio ' . $index , $power_value['hzQtmModemRxBlocksErrors'], 1, 10);
            }
        }

        return $sensors;
    }

    public function discoverWirelessRate()
    {
        $data = snmpwalk_group($this->getDeviceArray(), 'hzQtmModemRxSpeed', 'DRAGONWAVE-HORIZON-QUANTUM-MIB');
        $sensors = [];
        foreach ($data as $index => $errors_value) {
            if ($errors_value['hzQtmModemRxSpeed'] != '-99') {
                $sensors[] = new WirelessSensor('errors', $this->getDeviceId(), '.1.3.6.1.4.1.7262.2.4.5.2.1.1.6.' . $index, 'horizon-quantum' . $index, 'Rx rate radio ' . $index , $power_value['hzQtmModemRxSpeed'], 1, 10);
            }
        }
        $data = snmpwalk_group($this->getDeviceArray(), 'hzQtmModemTxSpeed', 'DRAGONWAVE-HORIZON-QUANTUM-MIB');
        foreach ($data as $index => $errors_value) {
            if ($errors_value['hzQtmModemTxSpeed'] != '-99') {
                $sensors[] = new WirelessSensor('errors', $this->getDeviceId(), '.1.3.6.1.4.1.7262.2.4.5.2.1.1.7.' . $index, 'horizon-quantum' . $index, 'Tx rate radio ' . $index , $power_value['hzQtmModemTxSpeed'], 1, 10);
            }
        }

        return $sensors;
    }
}
