<?php

if ($_SESSION['userlevel'] >= '10') {
    $sql = "SELECT D.device_id,D.hostname, D.status, D.ignore, D.disabled, D.last_polled, D.last_polled_timetaken FROM devices AS D ORDER BY hostname";
    $result = mysql_query($sql);
}
else {
    $sql = "SELECT D.device_id,D.hostname, D.status, D.ignore, D.disabled, D.last_polled, D.last_polled_timetaken FROM devices AS D, devices_perms AS P WHERE D.device_id = P.device_id AND P.user_id = '" . $_SESSION['user_id'] . "' AND D.ignore = '0' ORDER BY hostname";
    $result = mysql_query($sql);
}
echo "
    <table class='table table-bordered table-condensed table-hover'>
        <thead>
        <tr>
            <th>Hostname</th>
            <th>Last Polled</th>
            <th>Polling Duration(Seconds)</th>
        </tr>
        </thead>
    <tbody>";

foreach(dbFetchRows($sql) as $device) {
    if ($device['status']==0 && $device['disabled']!=1) {
            $tr_class = ' class="danger"';
    }
    elseif ($device['status']==0 && $device['disabled']==1) {
            $tr_class = ' class="warning"';
    }
    else {
            $tr_class = NULL;
    }
    echo "<tr" .$tr_class. "><td><a class='list-device' href='" .generate_device_url($device, array('tab' => 'graphs', 'group' => 'poller')). "'>" .$device['hostname']. "</a><td>" .$device['last_polled']. "<td>" .$device['last_polled_timetaken']. "</tr>";
}
echo "</tbody></table>";

?>