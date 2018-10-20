<?php

use App\Models\DevicePerf;
use LibreNMS\Exceptions\InvalidIpException;
use LibreNMS\Util\IP;

echo '<div class="container-fluid">';
echo "<div class='row'>
      <div class='col-md-12'>
          <div class='panel panel-default panel-condensed'>
            <div class='panel-heading'>";

if ($config['overview_show_sysDescr']) {
    echo '<i class="fa fa-id-card fa-lg icon-theme" aria-hidden="true"></i> <strong>'.$device['sysDescr'].'</strong>';
}

echo '</div>
      <table class="table table-hover table-condensed table-striped">';

$uptime = formatUptime($device['uptime']);
$uptime_text = 'Uptime';
if ($device['status'] == 0) {
    // Rewrite $uptime to be downtime if device is down
    $uptime = formatUptime(time() - strtotime($device['last_polled']));
    $uptime_text = 'Downtime';
}

if ($device['os'] == 'ios') {
    formatCiscoHardware($device);
}

if ($device['features']) {
    $device['features'] = '('.$device['features'].')';
}

$device['os_text'] = $config['os'][$device['os']]['text'];

echo '<tr>
        <td>System Name</td>
        <td>'.$device['sysName'].' </td>
      </tr>';

if (!empty($device['ip'])) {
     echo "<tr><td>Resolved IP</td><td>{$device['ip']}</td></tr>";
} elseif ($config['force_ip_to_sysname'] === true) {
    try {
        $ip = IP::parse($device['hostname']);
        echo "<tr><td>IP Address</td><td>$ip</td></tr>";
    } catch (InvalidIpException $e) {
        // don't add an ip line
    }
}

if ($device['purpose']) {
    echo '<tr>
        <td>Description</td>
        <td>'.display($device['purpose']).'</td>
      </tr>';
}

if ($device['hardware']) {
    echo '<tr>
        <td>Hardware</td>
        <td>'.$device['hardware'].'</td>
      </tr>';
}

echo '<tr>
        <td>Operating System</td>
        <td>'.$device['os_text'].' '.$device['version'].' '.$device['features'].' </td>
      </tr>';

if ($device['serial']) {
    echo '<tr>
        <td>Serial</td>
        <td>'.$device['serial'].'</td>
      </tr>';
}

if ($device['sysObjectID']) {
    echo '<tr>
        <td>Object ID</td>
        <td>'.$device['sysObjectID'].'</td>
      </tr>';
}

if ($device['sysContact']) {
    echo '<tr>
        <td>Contact</td>';
    if (get_dev_attrib($device, 'override_sysContact_bool')) {
        echo '
        <td>'.htmlspecialchars(get_dev_attrib($device, 'override_sysContact_string')).'</td>
      </tr>
      <tr>
        <td>SNMP Contact</td>';
    }

    echo '
        <td>'.htmlspecialchars($device['sysContact']).'</td>
      </tr>';
}

if ($device['location_id']) {
    $location = dbFetchRow('SELECT * FROM locations where id=?', [$device['location_id']]);
    echo '<tr>
        <td>Location</td>
        <td>'.$location['location'].'</td>
      </tr>';

    echo '<tr>
        <td>Lat / Lng</td>
        <td>['.$location['lat'].','.$location['lng'].'] <div class="pull-right"><a href="https://maps.google.com/?q='.$location['lat'].'+'.$location['lng'].'" target="_blank" class="btn btn-success btn-xs" role="button"><i class="fa fa-map-marker" style="color:white" aria-hidden="true"></i> Map</button></div></a></td>
    </tr>';
}

if ($uptime) {
    echo "<tr>
        <td>$uptime_text</td>
        <td>$uptime</td>
      </tr>";
}

echo '</table>
      </div>
      </div>
      </div>';

$perf_info = DevicePerf::where('device_id', $device['device_id'])->latest('timestamp')->first();
$perf_debug = json_decode($perf_info['debug'], true);
if ($perf_debug['traceroute']) {
    echo "<div class='row'>
     <div class='col-md-12'>
         <div class='panel panel-default'>
             <div class='panel-heading'>
                 <h3 class='panel-title'>Traceroute ({$perf_info['timestamp']})</h3>
             </div>
             <div class='panel-body'>
                 <pre>{$perf_debug['traceroute']}</pre>
            </div>
         </div>
     </div>
 </div>";
}

echo '</div>';
