<?php

$device_groups="";
foreach (dbFetchRows("select dg.id, dg.name from devices d, device_group_device g, device_groups dg where dg.id=g.device_group_id and g.device_id=d.device_id and d.hostname=? order by dg.name", array($device['hostname'])) as $groups) {
    $device_groups .= (empty($device_groups) ? "" : str_repeat("&nbsp; ", 4)) . '<a class="list-device" href="/devices/group=' . $groups['id'] . '" target="_blank">' . $groups['name'] . '</a>';
}

if (!empty($device_groups)) {
    echo "<div class='row'>
        <div class='col-md-12'>
          <div class='panel panel-default panel-condensed device-overview'>
            <div class='panel-heading'>";

    echo '<i class="fa fa-th fa-fw fa-lg" aria-hidden="true"></i> <a class="list-device" href="/device-groups">Device Group Membership</a>';
    echo '</div><div class="panel-body">';

    echo '<div class="row">
        <div class="col-sm-12">' . $device_groups . '</div>
      </div>';

    echo "</div>
        </div>
      </div>
    </div>";
}
