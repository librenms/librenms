<?php
$init_modules = [];
require realpath(__DIR__ . '/..') . '/includes/init.php';

$options = getopt('h:a:');
$hosts = str_replace('*', '%', mres($options['h']));
$alert_id = str_replace('*', '%', mres($options['a']));

if (empty($hosts)) {
    echo "-h <device hostname or wildcard>    Device(s) to match \n";
    echo "-a <Alert id> Alert id to display \n";
    echo "Use -h to display the device alert id's with timestamp, then use -h and -a to display the specific alert details.\n";
    echo "\n";
    exit;
}

if (!$alert_id){
foreach (dbFetchRows('SELECT device_id, hostname FROM devices WHERE hostname LIKE ?', "{$hosts}%") as $device) {
echo "\n";
foreach (dbFetchRows("SELECT device_id, id, time_logged FROM alert_log WHERE state != 2 && state != 0 && device_id = ?", $device['device_id'] ) as $alertlog) {

   echo "Device_name: {$device['hostname']} Alert_id: {$alertlog['id']} Timestamp: {$alertlog['time_logged']} ";
   echo "\n";
}}}

if ($alert_id){
foreach (dbFetchRows('SELECT device_id, hostname FROM devices WHERE hostname LIKE ?', "{$hosts}%") as $device) {
foreach (dbFetchRows("SELECT device_id, id, time_logged, details as detail FROM alert_log WHERE state != 2 && state != 0 && device_id = ? && id =  ?", [$device['device_id'], $alert_id])  as $alertlog) {
   $details =  gzuncompress($alertlog['detail']);
   $json_details =  json_decode($details, true);
   echo json_encode($json_details, JSON_PRETTY_PRINT);
}}}
?>
