<?php

echo '
<div>
  <div class="panel panel-default">
    <div class="panel-body">
      <table class="table table-condensed table-hover" style="border-collapse:collapse;">
        <thead>
          <tr>
            <th>&nbsp;</th>
            <th>Device</th>
            <th>Entity</th>
            <th>AFI</th>
            <th>Path</th>
            <th>Drop</th>
            <th>Punt</th>
            <th>Punt2Host</th>
          </tr>
        </thead>';

foreach (dbFetchRows('SELECT * FROM `cef_switching` ORDER BY `device_id`, `entPhysicalIndex`, `afi`, `cef_index`') as $cef) {
    $device = device_by_id_cache($cef['device_id']);
    $entity = dbFetchRow('SELECT `entPhysicalName`, `entPhysicalModelName`, `entPhysicalContainedIn` FROM `entPhysical` WHERE `device_id` = ? AND `entPhysicalIndex` = ?', array($device['device_id'], $cef['entPhysicalIndex']));

    if (!$entity['entPhysicalModelName'] && $entity['entPhysicalContainedIn']) {
        $parent_entity = dbFetchRow('SELECT `entPhysicalName`, `entPhysicalModelName` FROM `entPhysical` WHERE `device_id` = ? AND `entPhysicalIndex` = ?', array($device['device_id'], $entity['entPhysicalContainedIn']));
        $entity_descr  = $entity['entPhysicalName'].' ('.$parent_entity['entPhysicalModelName'].')';
    } else {
        $entity_descr = $entity['entPhysicalName'].' ('.$entity['entPhysicalModelName'].')';
    }

   echo '
        <tbody>
          <tr>
            <td></td>
            <td>' . generate_device_link($device, 0, array('tab' => 'routing', 'proto' => 'cef')) . '</td>
            <td>' . $entity_descr . '</td>
            <td>' . $cef['afi'] . '</td>
            <td>' . $cef['cef_path'] . '</td>
            <td>' . $cef['drop'] . '</td>
            <td>' . $cef['punt'] . '</td>
            <td>' . $cef['punt2host'] . '</td>
          </tr>
        </tbody>';
}
echo '</table>
    </div>
  </div>
</div>';
