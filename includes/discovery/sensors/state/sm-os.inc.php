<?php

use LibreNMS\OS;

$modulation = snmpwalk_group($device, 'linkAcmRxModulation', 'SIAE-RADIO-SYSTEM-MIB', 2);
$modulation = snmpwalk_group($device, 'linkAcmTxModulation', 'SIAE-RADIO-SYSTEM-MIB', 2, $modulation);

$state_name = 'smosLinkAcmModulation';
$states = [
    ['value' => 1, 'generic' => 0, 'graph' => 1, 'descr' => 'BPSK'],
    ['value' => 2, 'generic' => 0, 'graph' => 1, 'descr' => '4QAM'],
    ['value' => 3, 'generic' => 0, 'graph' => 1, 'descr' => '8PSK'],
    ['value' => 4, 'generic' => 0, 'graph' => 1, 'descr' => '16QAM'],
    ['value' => 5, 'generic' => 0, 'graph' => 1, 'descr' => '32QAM'],
    ['value' => 6, 'generic' => 0, 'graph' => 1, 'descr' => '64QAM'],
    ['value' => 7, 'generic' => 0, 'graph' => 1, 'descr' => '128QAM'],
    ['value' => 8, 'generic' => 0, 'graph' => 1, 'descr' => '256QAM'],
    ['value' => 9, 'generic' => 0, 'graph' => 1, 'descr' => '512QAM'],
    ['value' => 10, 'generic' => 0, 'graph' => 1, 'descr' => '1024QAM'],
    ['value' => 11, 'generic' => 0, 'graph' => 1, 'descr' => '2048QAM'],
    ['value' => 12, 'generic' => 0, 'graph' => 1, 'descr' => '4096QAM'],
];

if (! empty($modulation)) {
    create_state_index($state_name, $states);
}

if (! $os instanceof OS) {
    $os = OS::make($device);
}

foreach ($modulation as $link => $linkEntry) {
    foreach ($linkEntry as $radio => $radioEntry) {
        $index = "$link.$radio";
        if (isset($radioEntry['linkAcmRxModulation'])) {
            discover_sensor(
                $valid['sensor'],
                'state',
                $device,
                ".1.3.6.1.4.1.3373.1103.80.17.1.6.$index",
                "rx-$index",
                $state_name,
                $os->getLinkLabel($link) . ' Rx ' . $os->getRadioLabel($radio)
            );
            create_sensor_to_state_index($device, $state_name, "rx-$index");
        }
        if (isset($radioEntry['linkAcmTxModulation'])) {
            discover_sensor(
                $valid['sensor'],
                'state',
                $device,
                ".1.3.6.1.4.1.3373.1103.80.17.1.7.$index",
                "tx-$index",
                $state_name,
                $os->getLinkLabel($link) . ' Tx ' . $os->getRadioLabel($radio)
            );
            create_sensor_to_state_index($device, $state_name, "tx-$index");
        }
    }
}
