<?php

if ($_SESSION['userlevel'] >= '10') {
    $sql = "SELECT D.device_id,D.hostname, D.last_polled, D.last_polled_timetaken FROM devices AS D WHERE D.status ='1' AND D.ignore='0' AND D.disabled='0' ORDER BY hostname";
    $result = mysql_query($sql);
}
else {
    $sql = "SELECT D.device_id,D.hostname, D.last_polled, D.last_polled_timetaken FROM devices AS D, devices_perms AS P WHERE D.status ='1' AND D.ignore='0' AND D.disabled='0' AND D.device_id = P.device_id AND P.user_id = '" . $_SESSION['user_id'] . "' AND D.ignore = '0' ORDER BY hostname";
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
    if ($device['last_polled_timetaken'] < 180 ) {
            $tr_class = NULL;
    }
    elseif ($device['last_polled_timetaken'] < 300 ) {
            $tr_class = ' class="warning"';
    }
    elseif ($device['last_polled_timetaken'] >= 300 ) {
            $tr_class = ' class="danger"';
    }
    echo "<tr" .$tr_class. "><td><a class='list-device' href='" .generate_device_url($device, array('tab' => 'graphs', 'group' => 'poller')). "'>" .$device['hostname']. "</a><td>" .$device['last_polled']. "<td>" .$device['last_polled_timetaken']. "</tr>";
}
echo "</tbody></table>";

?>
