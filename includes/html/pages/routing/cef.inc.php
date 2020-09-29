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

foreach (dbFetchRows('SELECT C.`device_id`, C.`entPhysicalIndex`, C.`afi`, C.`cef_path`, C.`drop`, C.`punt`, C.`punt2host`, E.`entPhysicalName`, E.`entPhysicalModelName`, E.`entPhysicalContainedIn` FROM `cef_switching` AS `C`, `entPhysical` AS E WHERE C.`device_id` = E.`device_id` AND C.`entPhysicalIndex` = E.`entPhysicalIndex` ORDER BY C.`device_id`, C.`entPhysicalIndex`, C.`afi`, C.`cef_index`') as $cef) {
    $device = device_by_id_cache($cef['device_id']);

    if (! $cef['entPhysicalModelName'] && $cef['entPhysicalContainedIn']) {
        $parent_entity = dbFetchRow('SELECT `entPhysicalName`, `entPhysicalModelName` FROM `entPhysical` WHERE `device_id` = ? AND `entPhysicalIndex` = ?', [$device['device_id'], $cef['entPhysicalContainedIn']]);
        $entity_descr = $cef['entPhysicalName'] . ' (' . $parent_entity['entPhysicalModelName'] . ')';
    } else {
        $entity_descr = $cef['entPhysicalName'] . ' (' . $cef['entPhysicalModelName'] . ')';
    }

    echo '
        <tbody>
          <tr>
            <td></td>
            <td>' . generate_device_link($device, 0, ['tab' => 'routing', 'proto' => 'cef']) . '</td>
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
