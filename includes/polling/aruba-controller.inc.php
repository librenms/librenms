<?php

use App\Models\AccessPoint;
use Illuminate\Support\Collection;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\Mac;

if ($device['type'] == 'wireless' && $device['os'] == 'arubaos') {
    // get data about the controller
    $aruba_stats = SnmpQuery::get([
        'WLSX-SWITCH-MIB::wlsxSwitchTotalNumAccessPoints.0',
        'WLSX-SWITCH-MIB::wlsxSwitchTotalNumStationsAssociated.0',
    ])->values();

    $rrd_name = 'aruba-controller';
    $rrd_def = RrdDefinition::make()
        ->addDataset('NUMAPS', 'GAUGE', 0, 12500000000)
        ->addDataset('NUMCLIENTS', 'GAUGE', 0, 12500000000);

    $fields = [
        'NUMAPS' => $aruba_stats['WLSX-SWITCH-MIB::wlsxSwitchTotalNumAccessPoints.0'],
        'NUMCLIENTS' => $aruba_stats['WLSX-SWITCH-MIB::wlsxSwitchTotalNumStationsAssociated.0'],
    ];

    $tags = compact('rrd_name', 'rrd_def');
    data_update($device, 'aruba-controller', $tags, $fields);

    // get AP data
    $aruba_apstats = SnmpQuery::enumStrings()->walk([
        'WLSX-WLAN-MIB::wlsxWlanRadioTable',
        'WLSX-WLAN-MIB::wlanAPChInterferenceIndex',
    ])->table(2);

    $aps = new Collection;
    $db_aps = DeviceCache::getPrimary()->accessPoints->keyBy->getCompositeKey();

    foreach ($aruba_apstats as $mac => $radio) {
        foreach ($radio as $radionum => $data) {
            $ap = new AccessPoint([
                'name' => $data['WLSX-WLAN-MIB::wlanAPRadioAPName'] ?? null,
                'radio_number' => $radionum,
                'type' => $data['WLSX-WLAN-MIB::wlanAPRadioType'] ?? null,
                'mac_addr' => Mac::parse($mac)->readable(),
                'channel' => $data['WLSX-WLAN-MIB::wlanAPRadioChannel'] ?? null,
                'txpow' => isset($data['WLSX-WLAN-MIB::wlanAPRadioTransmitPower10x']) ? ($data['WLSX-WLAN-MIB::wlanAPRadioTransmitPower10x'] / 10) : ($data['WLSX-WLAN-MIB::wlanAPRadioTransmitPower'] ?? 0) / 2,
                'radioutil' => $data['WLSX-WLAN-MIB::wlanAPRadioUtilization'] ?? null,
                'numasoclients' => $data['WLSX-WLAN-MIB::wlanAPRadioNumAssociatedClients'] ?? null,
                'nummonclients' => $data['WLSX-WLAN-MIB::wlanAPRadioNumMonitoredClients'] ?? null,
                'numactbssid' => $data['WLSX-WLAN-MIB::wlanAPRadioNumActiveBSSIDs'] ?? null,
                'nummonbssid' => $data['WLSX-WLAN-MIB::wlanAPRadioNumMonitoredBSSIDs'] ?? null,
                'interference' => isset($data['WLSX-WLAN-MIB::wlanAPChInterferenceIndex']) ? ($data['WLSX-WLAN-MIB::wlanAPChInterferenceIndex'] / 600) : null,
            ]);

            Log::debug(<<<DEBUG
> mac:            $ap->mac_addr
  radionum:       $ap->radio_number
  name:           $ap->name
  type:           $ap->type
  channel:        $ap->channel
  txpow:          $ap->txpow
  radioutil:      $ap->radioutil
  numasoclients:  $ap->numasoclients
  interference:   $ap->interference
DEBUG);

            // if there is a numeric channel, assume the rest of the data is valid, I guess
            if (is_numeric($ap->channel)) {
                $rrd_def = RrdDefinition::make()
                    ->addDataset('channel', 'GAUGE', 0, 200)
                    ->addDataset('txpow', 'GAUGE', 0, 200)
                    ->addDataset('radioutil', 'GAUGE', 0, 100)
                    ->addDataset('nummonclients', 'GAUGE', 0, 500)
                    ->addDataset('nummonbssid', 'GAUGE', 0, 200)
                    ->addDataset('numasoclients', 'GAUGE', 0, 500)
                    ->addDataset('interference', 'GAUGE', 0, 2000);

                $fields = $ap->only([
                    'channel',
                    'txpow',
                    'radioutil',
                    'nummonclients',
                    'nummonbssid',
                    'numasoclients',
                    'interference',
                ]);

                $tags = [
                    'name' => $ap->name,
                    'radionum' => $ap->radio_number,
                    'rrd_name' => ['arubaap', $ap->name . $ap->radio_number],
                    'rrd_def' => $rrd_def,
                ];

                data_update($device, 'aruba', $tags, $fields);

                // sync to DB
                $ap_key = $ap->getCompositeKey();
                if ($db_aps->has($ap_key)) {
                    // ap exists in DB, update it
                    $db_ap = $db_aps->get($ap_key);
                    $db_ap->fill($ap->getAttributes());
                    $db_ap->deleted = 0;
                    $db_ap->save();
                    $db_aps->forget($ap_key); // remove valid APs from collection
                } else {
                    // save new to DB
                    DeviceCache::getPrimary()->accessPoints()->save($ap);
                }
            }
        }
    }

    // mark APs which are not on this controller anymore as deleted
    $db_aps->each->update(['deleted' => 1]);
}//end if
