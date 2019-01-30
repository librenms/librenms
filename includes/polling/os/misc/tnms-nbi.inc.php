<?php

use App\Models\TnmsNeInfo;
use App\Models\TnmsAlarm;
use LibreNMS\Util\ModuleModelObserver;

echo 'TNMS-NBI-MIB: ';

/*
 * Coriant have done some SQL over SNMP, since we have to populate and update all the tables
 * before using it, we have to do ugly stuff
 */

echo "NE: ";
$c_oids = snmpwalk_cache_multi_oid($device, 'enmsNETable', [], 'TNMS-NBI-MIB');
$existing_ne = TnmsNeInfo::where('device_id', $device['device_id'])->get();
$remove_ne = $existing_ne->keyBy('tnmsne_info_id'); // put existing ne here and remove them when we update them
$existing_ne = $existing_ne->keyBy('neID');

if (!TnmsNeInfo::getEventDispatcher()->hasListeners('eloquent.created: App\Models\TnmsNeInfo')) {
    TnmsNeInfo::observe(new ModuleModelObserver());
}

foreach ($c_oids as $index => $entry) {
    $fields = [
        'device_id' => $device['device_id'],
        'neID' => $index,
        'neType' => $entry['enmsNeType'],
        'neName' => $entry['enmsNeName'],
        'neLocation' => $entry['enmsNeLocation'],
        'neAlarm' => $entry['enmsNeAlarmSeverity'],
        'neOpMode' => $entry['enmsNeOperatingMode'],
        'neOpState' => $entry['enmsNeOpState'],
    ];
    d_echo($fields);

    if ($existing_ne->has($index)) {
        $ne = $existing_ne->get($index)->fill($fields);
        $remove_ne->forget($ne->tnmsne_info_id);
    } else {
        $ne = new TnmsNeInfo($fields);
        $existing_ne->put($ne->neID, $ne);
    }

    $ne->save();
}

// delete old NE left in the DB
$remove_ne->each(function ($ne) use ($device) {
    log_event("Coriant NE Hardware " . $ne->neName . ' at ' . $ne->neLocation . ' Removed', $device, 'system', $ne->neID);
})->each->delete();

echo PHP_EOL;

echo 'NE Alarms: ';

if (!TnmsAlarm::getEventDispatcher()->hasListeners('eloquent.created: App\Models\TnmsAlarm')) {
    TnmsAlarm::observe(new ModuleModelObserver());
}

// unfortunately, cannot map snmp alarms to existing alarms reliably, they may shuffle

$ne_alarm_oids = snmpwalk_cache_multi_oid($device, 'enmsAlarmtable', [], 'TNMS-NBI-MIB', null, '-OQUsb');
$existing_alarms = \App\Models\TnmsAlarm::where('device_id', $device['device_id'])->get();
$remove_alarms = $existing_alarms->keyBy('id');
$existing_alarms = $existing_alarms->keyBy('alarm_num');

foreach ($ne_alarm_oids as $alarm) {
    // for some reason this walk returns extra data... only handle this table's entries
    if (!empty($alarm['enmsAlAlarmNumber'])) {
        $fields = [
            'tnmsne_info_id' => $existing_ne->get($alarm['enmsAlNEId'])->tnmsne_info_id,
            'device_id' => $device['device_id'],
            'alarm_num' => $alarm['enmsAlAlarmNumber'],
            'alarm_cause' => $alarm['enmsAlProbableCauseString'],
            'alarm_location' => $alarm['enmsAlAffectedLocation'],
            'neAlarmtimestamp' => $alarm['enmsAlTimeStamp'],
        ];
        d_echo($fields);
        if ($fields['tnmsne_info_id']) {
            if ($existing_alarm = $existing_alarms->get($alarm['enmsAlAlarmNumber'])) {
                $existing_alarm->fill($fields)->save();
                $remove_alarms->forget($existing_alarm->id);
            } else {
                $tnms_alarm = new TnmsAlarm($fields);
                $tnms_alarm->save();
            }
        }
    }
}
// delete old ones
$remove_alarms->each->delete();

echo PHP_EOL;
