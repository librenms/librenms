<?php

$where = 1;
$param = array();

$sql = " FROM `devices` WHERE $where ";

if (!empty($_POST['hostname'])) { $sql .= " AND hostname LIKE ?"; $param[] = "%".$_POST['hostname']."%"; }
if (!empty($_POST['os']))       { $sql .= " AND os = ?";          $param[] = $_POST['os']; }
if (!empty($_POST['version']))  { $sql .= " AND version = ?";     $param[] = $_POST['version']; }
if (!empty($_POST['hardware'])) { $sql .= " AND hardware = ?";    $param[] = $_POST['hardware']; }
if (!empty($_POST['features'])) { $sql .= " AND features = ?";    $param[] = $_POST['features']; }
if (!empty($_POST['type']))     {
  if ($_POST['type'] == 'generic') {
    $sql .= " AND ( type = ? OR type = '')";        $param[] = $_POST['type'];
  } else {
    $sql .= " AND type = ?";        $param[] = $_POST['type'];
  }
}
if (!empty($_POST['state'])) {
    $sql .= " AND status= ?";       $param[] = $state;
    $sql .= " AND disabled='0' AND `ignore`='0'"; $param[] = '';
}
if (!empty($_POST['disabled'])) { $sql .= " AND disabled= ?";     $param[] = $_POST['disabled']; }
if (!empty($_POST['ignore']))   { $sql .= " AND `ignore`= ?";       $param[] = $_POST['ignore']; }
if (!empty($_POST['location']) && $_POST['location'] == "Unset") { $location_filter = ''; }
if (!empty($_POST['location'])) { $location_filter = $_POST['location']; }
if( !empty($_POST['group']) ) {
    require_once('../includes/device-groups.inc.php');
    $sql .= " AND ( ";
    foreach( GetDevicesFromGroup($_POST['group']) as $dev ) {
        $sql .= "device_id = ? OR ";
        $param[] = $dev['device_id'];
    }
    $sql = substr($sql, 0, strlen($sql)-3);
    $sql .= " )";
}

$count_sql = "SELECT COUNT(`device_id`) $sql";

$total = dbFetchCell($count_sql,$param);

if (!isset($sort) || empty($sort)) {
    $sort = '`hostname` DESC';
}

$sql .= " ORDER BY $sort";

if (isset($current)) {
    $limit_low = ($current * $rowCount) - ($rowCount);
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

$sql = "SELECT * $sql";

if (!isset($_POST['format'])) {
    $_POST['format'] = "list_detail";
}
list($format, $subformat) = explode("_", $_POST['format']);

foreach (dbFetchRows($sql, $param) as $device) {
    if (device_permitted($device['device_id'])) {
        if (!isset($location_filter) || ((get_dev_attrib($device,'override_sysLocation_bool') &&
            get_dev_attrib($device,'override_sysLocation_string') == $location_filter) || $device['location'] == $location_filter)) {

            if (isset($bg) && $bg == $list_colour_b) {
                $bg = $list_colour_a;
            } else {
                $bg = $list_colour_b;
            }

            if ($device['status'] == '0') {
                $extra = "danger";
                $msg = "down";
            } else {
                $extra = "success";
                $msg = "up";
            }
            if ($device['ignore'] == '1') {
                $extra = "default";
                $msg = "ignored";
                if ($device['status'] == '1') {
                    $extra = "warning";
                    $msg = "ignored";
                }
            }
            if ($device['disabled'] == '1') {
                $extra = "default";
                $msg = "disabled";
            }

            $type = strtolower($device['os']);
            $image = getImage($device);
            
            if ($device['os'] == "ios") {
                formatCiscoHardware($device, true);
            }

            $device['os_text'] = $config['os'][$device['os']]['text'];
            $port_count   = dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE `device_id` = ?", array($device['device_id']));
            $sensor_count = dbFetchCell("SELECT COUNT(*) FROM `sensors` WHERE `device_id` = ?", array($device['device_id']));

            if (get_dev_attrib($device,'override_sysLocation_bool')) {
                $device['location'] = get_dev_attrib($device,'override_sysLocation_string');
            }

            $actions = ('<div class="row">
                             <div class="col-xs-1">');
            $actions .= '<a href="'.generate_device_url($device).'"> <img src="images/16/server.png" border="0" align="absmiddle" alt="View device" title="View device" /></a> ';
            $actions .= ('</div>
                          <div class="col-xs-1">');
            $actions .= '<a href="'.generate_device_url($device, array('tab' => 'alerts')).'"> <img src="images/16/bell.png" border="0" align="absmiddle" alt="View alerts" title="View alerts"  /></a> ';
            $actions .= '</div>';
            if ($_SESSION['userlevel'] >= "7") {
                $actions .= ('<div class="col-xs-1">
                                  <a href="'.generate_device_url($device, array('tab' => 'edit')).'"> <img src="images/16/wrench.png" border="0" align="absmiddle" alt="Edit device" title="Edit device" /></a>
                             </div>');
            }
            $actions .= ('</div>
                          <div class="row">
                              <div class="col-xs-1">
                                  <a href="telnet://' . $device['hostname']  . '"><img src="images/16/telnet.png" alt="telnet" title="Telnet to ' . $device['hostname']  . '" border="0" width="16" height="16"></a>
                              </div>
                              <div class="col-xs-1">
                                  <a href="ssh://' . $device['hostname']  . '"><img src="images/16/ssh.png" alt="ssh" title="SSH to ' . $device['hostname']  . '" border="0" width="16" height="16"></a>
                              </div>
                              <div class="col-xs-1">
                                  <a href="https://' . $device['hostname']  . '"><img src="images/16/http.png" alt="https" title="Launch browser https://' . $device['hostname']  . '" border="0" width="16" height="16" target="_blank"></a>
                              </div>
                          </div>');

            $hostname = generate_device_link($device);
            $platform = $device['hardware'] . '<br />' . $device['features'];
            $os = $device['os_text'] . '<br />' . $device['version'];
            $uptime = formatUptime($device['uptime'], 'short') . '<br />' . truncate($device['location'],32, '');
            if ($subformat == "detail") {
                $hostname .= '<br />' . $device['sysName'];
                if ($port_count) {
                    $col_port = ' <img src="images/icons/port.png" align=absmiddle /> '.$port_count . '<br />';
                }
                if ($sensor_count) {
                    $col_port .= ' <img src="images/icons/sensors.png" align=absmiddle /> '.$sensor_count;
                }
            } else {

            }
            $response[] = array('extra'=>$extra,'msg'=>$msg,'icon'=>$image,'hostname'=>$hostname,'ports'=>$col_port,'hardware'=>$platform,'os'=>$os,'uptime'=>$uptime,'actions'=>$actions);
        }
    }
}

$output = array('current'=>$current,'rowCount'=>$rowCount,'rows'=>$response,'total'=>$total);
echo _json_encode($output);
