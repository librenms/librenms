<?php

namespace LibreNMS\OS;

use App\Models\EntPhysical;
use Illuminate\Support\Collection;
use LibreNMS\OS;
use SnmpQuery;

class MrvOd extends OS
{
    public function discoverEntityPhysical(): Collection
    {
        $inventory = new Collection;

        $chassis_array = SnmpQuery::hideMib()->enumStrings()->walk('NBS-CMMC-MIB::nbsCmmcChassisTable')->table(1);
        $slot_array = SnmpQuery::hideMib()->enumStrings()->walk('NBS-CMMC-MIB::nbsCmmcSlotTable')->table(2);
        $port_array = SnmpQuery::hideMib()->enumStrings()->walk('NBS-CMMC-MIB::nbsCmmcPortTable')->table(3);

        // We use the last digit in the OID to define an entPhysicalIndex for Power Supply state sensors
        $nbsCmmcChassisPSStatus_array = [
            7 => 'nbsCmmcChassisPS1Status',
            8 => 'nbsCmmcChassisPS2Status',
            9 => 'nbsCmmcChassisPS3Status',
            10 => 'nbsCmmcChassisPS4Status',
        ];

        // We use the last digit in the OID to define an entPhysicalIndex for Fan state sensors
        $nbsCmmcChassisFanStatus_array = [
            11 => 'nbsCmmcChassisFan1Status',
            12 => 'nbsCmmcChassisFan2Status',
            13 => 'nbsCmmcChassisFan3Status',
            14 => 'nbsCmmcChassisFan4Status',
            36 => 'nbsCmmcChassisFan5Status',
            37 => 'nbsCmmcChassisFan6Status',
            38 => 'nbsCmmcChassisFan7Status',
            39 => 'nbsCmmcChassisFan8Status',
        ];

        // Define all the types of pluggable port form factors recognized by nbsCmmcPortType in NBS-CMMC-MIB,
        // if nbsCmmcPortType returns a value that is not in this array, it should be a built-in port in the card.
        $nbsCmmcPortType_array = [
            125 => 'SFP',
            147 => 'GBIC',
            197 => 'XFP',
            219 => 'QSFP+',
            220 => 'CXP',
            221 => 'CFP',
            223 => 'QSFP28',
            224 => 'CFP2',
        ];

        $nbsCmmcPortSensor_array = [
            30 => [
                'objectType' => 'nbsCmmcPortTemperature',
                'skipValue' => '-2147483648',
                'entPhysicalName' => 'Port Temperature',
            ],
            31 => [
                'objectType' => 'nbsCmmcPortTxPower',
                'skipValue' => '-2147483648',
                'entPhysicalName' => 'Port Tx Power',
            ],
            32 => [
                'objectType' => 'nbsCmmcPortRxPower',
                'skipValue' => '-2147483648',
                'entPhysicalName' => 'Port Rx Power',
            ],
            33 => [
                'objectType' => 'nbsCmmcPortBiasAmps',
                'skipValue' => '-1',
                'entPhysicalName' => 'Port Tx Bias Current',
            ],
            34 => [
                'objectType' => 'nbsCmmcPortSupplyVolts',
                'skipValue' => '-1',
                'entPhysicalName' => 'Port Tx Supply Voltage',
            ],
            38 => [
                'objectType' => 'nbsCmmcPortDigitalDiags',
                'skipValue' => '1',
                'entPhysicalName' => 'Port Overall DigiDiags State',
            ],
        ];

        foreach ($chassis_array as $nbsCmmcChassis => $chassis_contents) {
            [$chassisHardwareRev, $chassisFirmwareRev] = explode(', ', $chassis_contents['nbsCmmcChassisHardwareRevision']);
            // Discover the chassis
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => $chassis_contents['nbsCmmcChassisIfIndex'] . '00',
                'entPhysicalDescr' => "MRV OptiDriver {$chassis_contents['nbsCmmcChassisModel']}",
                'entPhysicalClass' => 'chassis',
                'entPhysicalName' => "Chassis $nbsCmmcChassis",
                'entPhysicalModelName' => $chassis_contents['nbsCmmcChassisModel'],
                'entPhysicalSerialNum' => $chassis_contents['nbsCmmcChassisSerialNum'],
                'entPhysicalContainedIn' => '0',
                'entPhysicalMfgName' => 'MRV Communications',
                'entPhysicalParentRelPos' => $chassis_contents['nbsCmmcChassisIndex'],
                'entPhysicalVendorType' => $chassis_contents['nbsCmmcChassisType'],
                'entPhysicalHardwareRev' => $chassisHardwareRev,
                'entPhysicalFirmwareRev' => $chassisFirmwareRev,
                'entPhysicalIsFRU' => 'true',
                'entPhysicalAlias' => $chassis_contents['nbsCmmcChassisName'],
            ]));

            // Discover the chassis temperature sensor
            if (isset($chassis_contents['nbsCmmcChassisTemperature']) && $chassis_contents['nbsCmmcChassisTemperature'] != '-2147483648') {
                $inventory->push(new EntPhysical([
                    'entPhysicalIndex' => "{$chassis_contents['nbsCmmcChassisIfIndex']}15",
                    'entPhysicalDescr' => 'Chassis Temperature Sensor',
                    'entPhysicalClass' => 'sensor',
                    'entPhysicalName' => 'Chassis Temperature',
                    'entPhysicalContainedIn' => "{$chassis_contents['nbsCmmcChassisIfIndex']}00",
                    'entPhysicalMfgName' => 'MRV Communications',
                    'entPhysicalParentRelPos' => '-1',
                    'entPhysicalIsFRU' => 'false',
                ]));
            }

            // Discover the chassis power budget status sensor
            if (isset($chassis_contents['nbsCmmcChassisPowerStatus']) && $chassis_contents['nbsCmmcChassisPowerStatus'] != 'notSupported') {
                $inventory->push(new EntPhysical([
                    'entPhysicalIndex' => "{$chassis_contents['nbsCmmcChassisIfIndex']}51",
                    'entPhysicalDescr' => 'Chassis Power Budget Status Sensor',
                    'entPhysicalClass' => 'sensor',
                    'entPhysicalName' => 'Chassis Power Budget Status',
                    'entPhysicalContainedIn' => "{$chassis_contents['nbsCmmcChassisIfIndex']}00",
                    'entPhysicalMfgName' => 'MRV Communications',
                    'entPhysicalParentRelPos' => '-1',
                    'entPhysicalIsFRU' => 'false',
                ]));
            }

            // Discover the chassis power supplies and state sensors
            foreach ($nbsCmmcChassisPSStatus_array as $index => $item) {
                if (isset($chassis_contents[$item]) && $chassis_contents[$item] != 'notSupported') {
                    $position = substr($item, 16, 1);
                    $inventory->push(new EntPhysical([
                        'entPhysicalIndex' => $chassis_contents['nbsCmmcChassisIfIndex'] . $position,
                        'entPhysicalDescr' => 'Power Supply',
                        'entPhysicalClass' => 'powerSupply',
                        'entPhysicalName' => "Power Supply $position",
                        'entPhysicalContainedIn' => "{$chassis_contents['nbsCmmcChassisIfIndex']}00",
                        'entPhysicalMfgName' => 'MRV Communications',
                        'entPhysicalParentRelPos' => $position,
                        'entPhysicalIsFRU' => 'false',
                    ]));
                    $inventory->push(new EntPhysical([
                        'entPhysicalIndex' => $chassis_contents['nbsCmmcChassisIfIndex'] . $index,
                        'entPhysicalDescr' => 'Power Supply State',
                        'entPhysicalClass' => 'sensor',
                        'entPhysicalName' => "Power Supply $position",
                        'entPhysicalContainedIn' => $chassis_contents['nbsCmmcChassisIfIndex'] . $position,
                        'entPhysicalMfgName' => 'MRV Communications',
                        'entPhysicalParentRelPos' => '-1',
                        'entPhysicalIsFRU' => 'true',
                    ]));
                }
            }

            // Discover the chassis fan trays and state sensors
            foreach ($nbsCmmcChassisFanStatus_array as $index => $item) {
                if (isset($chassis_contents[$item]) && $chassis_contents[$item] != 'notSupported') {
                    $position = substr($item, 17, 1);
                    $inventory->push(new EntPhysical([
                        'entPhysicalIndex' => "{$chassis_contents['nbsCmmcChassisIfIndex']}0$position",
                        'entPhysicalDescr' => 'Fan Tray',
                        'entPhysicalClass' => 'fan',
                        'entPhysicalName' => "Fan Tray $position",
                        'entPhysicalContainedIn' => "{$chassis_contents['nbsCmmcChassisIfIndex']}00",
                        'entPhysicalMfgName' => 'MRV Communications',
                        'entPhysicalParentRelPos' => $position,
                        'entPhysicalIsFRU' => 'false',
                    ]));
                    $inventory->push(new EntPhysical([
                        'entPhysicalIndex' => $chassis_contents['nbsCmmcChassisIfIndex'] . $index,
                        'entPhysicalDescr' => 'Fan State',
                        'entPhysicalClass' => 'sensor',
                        'entPhysicalName' => "Fan $position",
                        'entPhysicalContainedIn' => "{$chassis_contents['nbsCmmcChassisIfIndex']}0$position",
                        'entPhysicalMfgName' => 'MRV Communications',
                        'entPhysicalParentRelPos' => '-1',
                        'entPhysicalIsFRU' => 'true',
                    ]));
                }
            }
        }

        foreach ($slot_array as $chassis_index => $chassis_contents) {
            foreach ($chassis_contents as $nbsCmmcSlot => $slot_contents) {
                // Obtain the nbsCmmcChassisIfIndex of the chassis which houses this slot
                $nbsCmmcChassisIfIndex = $chassis_array[$chassis_index]['nbsCmmcChassisIfIndex'];
                // Calculate the nbsCmmcSlotIfIndex since an empty slot has nbsCmmcSlotIfIndex == -1
                $nbsCmmcSlotIfIndex = $nbsCmmcChassisIfIndex + $slot_contents['nbsCmmcSlotIndex'] * 1000;
                // Discover the slot
                $inventory->push(new EntPhysical([
                    'entPhysicalIndex' => $nbsCmmcSlotIfIndex . '00',
                    'entPhysicalDescr' => 'MRV OptiDriver Slot',
                    'entPhysicalClass' => 'container',
                    'entPhysicalName' => "Card Slot $chassis_index.$nbsCmmcSlot",
                    'entPhysicalContainedIn' => $nbsCmmcChassisIfIndex . '00',
                    'entPhysicalMfgName' => 'MRV Communications',
                    'entPhysicalParentRelPos' => $slot_contents['nbsCmmcSlotIndex'] ?? -1,
                    'entPhysicalIsFRU' => 'false',
                ]));
                if (isset($slot_contents['nbsCmmcSlotIfIndex']) && $slot_contents['nbsCmmcSlotIfIndex'] != '-1') {
                    $cardHardwareRev = $slot_contents['nbsCmmcSlotHardwareRevision'];
                    $cardFirmwareRev = null;
                    $cardOtherRev = null;
                    $rev_explode = explode(', ', $cardHardwareRev);
                    if (isset($rev_explode[2])) {
                        [$cardHardwareRev, $cardFirmwareRev, $cardOtherRev] = $rev_explode;
                    }
                    // Discover the card
                    $inventory->push(new EntPhysical([
                        'entPhysicalIndex' => $slot_contents['nbsCmmcSlotIfIndex'] . '01',
                        'entPhysicalDescr' => 'MRV ' . ucfirst($slot_contents['nbsCmmcSlotOperationType']) . ' Card',
                        'entPhysicalClass' => 'module',
                        'entPhysicalName' => "Card $chassis_index.$nbsCmmcSlot",
                        'entPhysicalModelName' => $slot_contents['nbsCmmcSlotModel'],
                        'entPhysicalSerialNum' => $slot_contents['nbsCmmcSlotSerialNum'],
                        'entPhysicalContainedIn' => "{$slot_contents['nbsCmmcSlotIfIndex']}00",
                        'entPhysicalMfgName' => 'MRV Communications',
                        'entPhysicalParentRelPos' => '-1',
                        'entPhysicalVendorType' => $slot_contents['nbsCmmcSlotType'],
                        'entPhysicalHardwareRev' => "$cardHardwareRev, $cardOtherRev",
                        'entPhysicalFirmwareRev' => $cardFirmwareRev,
                        'entPhysicalIsFRU' => 'true',
                        'entPhysicalAlias' => $slot_contents['nbsCmmcSlotName'],
                    ]));

                    // Discover the module temperature sensor
                    if (isset($slot_contents['nbsCmmcSlotTemperature']) && $slot_contents['nbsCmmcSlotTemperature'] != '-2147483648') {
                        $inventory->push(new EntPhysical([
                            'entPhysicalIndex' => "{$slot_contents['nbsCmmcSlotIfIndex']}34",
                            'entPhysicalDescr' => 'Card Temperature Sensor',
                            'entPhysicalClass' => 'sensor',
                            'entPhysicalName' => 'Card Temperature',
                            'entPhysicalContainedIn' => "{$slot_contents['nbsCmmcSlotIfIndex']}01",
                            'entPhysicalMfgName' => 'MRV Communications',
                            'entPhysicalParentRelPos' => '-1',
                            'entPhysicalIsFRU' => 'false',
                        ]));
                    }
                }
            }
        }

        foreach ($port_array as $chassis_index => $chassis_contents) {
            foreach ($chassis_contents as $slot_index => $slot_contents) {
                foreach ($slot_contents as $nbsCmmcPort => $port_contents) {
                    // Obtain the nbsCmmcSlotIfIndex of the slot which houses this port
                    $nbsCmmcSlotIfIndex = $slot_array[$chassis_index][$slot_index]['nbsCmmcSlotIfIndex'];

                    // We only need to discover a transceiver container if the port type is pluggable
                    if (array_key_exists($port_contents['nbsCmmcPortType'], $nbsCmmcPortType_array)) {
                        $nbsCmmcPortType = $nbsCmmcPortType_array[$port_contents['nbsCmmcPortType']];

                        // Discover the transceiver container
                        $inventory->push(new EntPhysical([
                            'entPhysicalIndex' => $port_contents['nbsCmmcPortIfIndex'] . '00',
                            'entPhysicalDescr' => "$nbsCmmcPortType Transceiver Container",
                            'entPhysicalClass' => 'container',
                            'entPhysicalName' => "Transceiver Container $chassis_index.$slot_index.$nbsCmmcPort",
                            'entPhysicalContainedIn' => $nbsCmmcSlotIfIndex . '01',
                            'entPhysicalMfgName' => 'MRV Communications',
                            'entPhysicalParentRelPos' => $port_contents['nbsCmmcPortIndex'] ?? -1,
                            'entPhysicalIsFRU' => 'false',
                        ]));
                        // Set a few variables for the port discovery
                        $nbsCmmcPortContainedIn = $port_contents['nbsCmmcPortIfIndex'] . '00';
                        $nbsCmmcPortVendorInfo = $port_contents['nbsCmmcPortVendorInfo'];
                        $nbsCmmcPortIsFRU = 'true';
                        $nbsCmmcPortParentRelPos = '-1';
                        // If one runs a command like "show 1.1.1 | grep Part" on a port with a genuine pluggable transceiver,
                        // CLI output like "Part #/Rev: SFP-10GDWZR-22/0001" indicates / is considered to be the string delimiter.
                        // However, non-genuine pluggable transceivers may not adhere to this format.
                        [$nbsCmmcPortModelName, $nbsCmmcPortHardwareRev] = explode('/', $port_contents['nbsCmmcPortPartRev']);
                    } else {
                        $nbsCmmcPortType = 'Built-in';

                        // Set a few variables for the port discovery
                        $nbsCmmcPortContainedIn = $nbsCmmcSlotIfIndex . '01';
                        $nbsCmmcPortVendorInfo = 'MRV Communications';
                        $nbsCmmcPortIsFRU = 'false';
                        $nbsCmmcPortParentRelPos = $port_contents['nbsCmmcPortIndex'] ?? -1;
                        $nbsCmmcPortModelName = null;
                        $nbsCmmcPortHardwareRev = null;
                    }

                    if (isset($port_contents['nbsCmmcPortConnector']) && $port_contents['nbsCmmcPortConnector'] != 'removed') {
                        // Determine the correct entPhysicalDescr for the port
                        if (isset($port_contents['nbsCmmcPortWavelengthX']) && $port_contents['nbsCmmcPortWavelengthX'] != 'N/A') {
                            $portEntPhysicalDescr = "$nbsCmmcPortType Port, {$port_contents['nbsCmmcPortWavelengthX']}nm Tx Signal, {$port_contents['nbsCmmcPortConnector']} Connector";
                        } elseif (! empty($port_contents['nbsCmmcPortDescription'])) {
                            $portEntPhysicalDescr = "$nbsCmmcPortType Port, {$port_contents['nbsCmmcPortDescription']}, {$port_contents['nbsCmmcPortConnector']} Connector";
                        } else {
                            $portEntPhysicalDescr = "$nbsCmmcPortType Port, {$port_contents['nbsCmmcPortConnector']} Connector";
                        }

                        // Discover the port
                        $inventory->push(new EntPhysical([
                            'entPhysicalIndex' => "{$port_contents['nbsCmmcPortIfIndex']}01",
                            'entPhysicalDescr' => $portEntPhysicalDescr,
                            'entPhysicalClass' => 'port',
                            'entPhysicalName' => "Port $chassis_index.$slot_index.$nbsCmmcPort",
                            'entPhysicalModelName' => $nbsCmmcPortModelName,
                            'entPhysicalSerialNum' => $port_contents['nbsCmmcPortSerialNumber'],
                            'entPhysicalContainedIn' => $nbsCmmcPortContainedIn,
                            'entPhysicalMfgName' => $nbsCmmcPortVendorInfo,
                            'entPhysicalParentRelPos' => $nbsCmmcPortParentRelPos,
                            'entPhysicalVendorType' => $port_contents['nbsCmmcPortType'],
                            'entPhysicalHardwareRev' => $nbsCmmcPortHardwareRev,
                            'entPhysicalIsFRU' => $nbsCmmcPortIsFRU,
                            'entPhysicalAlias' => $port_contents['nbsCmmcPortName'],
                            'ifIndex' => $port_contents['nbsCmmcPortIfIndex'],
                        ]));

                        // Discover the port sensors
                        foreach ($nbsCmmcPortSensor_array as $index => $nbsCmmcPortSensor) {
                            if (isset($port_contents[$nbsCmmcPortSensor['objectType']]) && $port_contents[$nbsCmmcPortSensor['objectType']] != $nbsCmmcPortSensor['skipValue']) {
                                $inventory->push(new EntPhysical([
                                    'entPhysicalIndex' => $port_contents['nbsCmmcPortIfIndex'] . $index,
                                    'entPhysicalDescr' => "{$nbsCmmcPortSensor['entPhysicalName']} Sensor",
                                    'entPhysicalClass' => 'sensor',
                                    'entPhysicalName' => $nbsCmmcPortSensor['entPhysicalName'],
                                    'entPhysicalContainedIn' => "{$port_contents['nbsCmmcPortIfIndex']}01",
                                    'entPhysicalMfgName' => $nbsCmmcPortVendorInfo,
                                    'entPhysicalParentRelPos' => '-1',
                                    'entPhysicalIsFRU' => 'false',
                                ]));
                            }
                        }
                    }
                }
            }
        }

        return $inventory;
    }
}
