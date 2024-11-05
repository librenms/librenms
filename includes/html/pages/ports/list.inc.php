<?php
/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage webui
 * @link       https://www.librenms.org
 * @copyright  2017 LibreNMS
 * @author     LibreNMS Contributors
*/

$details_visible = var_export($vars['format'] == 'list_detail', 1);
$errors_visible = var_export($vars['format'] == 'list_detail' || isset($vars['errors']), 1);
$no_refresh = true;
$device = DeviceCache::get((int) $vars['device_id']);
$device_selected = json_encode($device->exists ? ['id' => $device->device_id, 'text' => $device->displayName()] : '');
$location = \App\Models\Location::find((int) $vars['location']);
$location_selected = json_encode(! empty($location) ? ['id' => $location->id, 'text' => $location->location] : '');

if (isset($vars['errors'])) {
    $error_sort = ' data-order="desc"';
    $sort = '';
} else {
    $error_sort = '';
    $sort = ' data-order="asc"';
}
?>
<div class="panel panel-default panel-condensed">
    <div class="panel-heading">
        <?php echo $displayLists; ?>
    </div>
    <div class="table-responsive">
        <table id="ports" class="table table-condensed table-hover table-striped">
            <thead>
            <tr>
                <th data-column-id="hostname" data-formatter="device">Device</th>
                <th data-column-id="ifDescr"<?php echo $sort ?> data-formatter="port">Port</th>
                <th data-column-id="secondsIfLastChange" data-converter="duration">Status Changed</th>
                <th data-column-id="ifConnectorPresent" data-visible="false">Connected</th>
                <th data-column-id="ifSpeed" data-converter="human-bps">Speed</th>
                <th data-column-id="ifMtu" data-visible="false">MTU</th>
                <th data-column-id="ifInOctets_rate" data-searchable="false" data-css-class="green"
                    data-converter="human-bps">In
                </th>
                <th data-column-id="ifOutOctets_rate" data-searchable="false" data-css-class="blue"
                    data-converter="human-bps">Out
                </th>
                <th data-column-id="ifInUcastPkts_rate" data-searchable="false"
                    data-visible="<?php echo $details_visible ?>" data-css-class="green" data-converter="human-pps">
                    Packets In
                </th>
                <th data-column-id="ifOutUcastPkts_rate" data-searchable="false"
                    data-visible="<?php echo $details_visible ?>" data-css-class="blue" data-converter="human-pps">
                    Packets Out
                </th>
                <th data-column-id="ifInErrors_delta" data-searchable="false" data-visible="<?php echo $errors_visible ?>"
                    data-css-class="red"<?php echo $error_sort ?>>Errors In Rate
                </th>
                <th data-column-id="ifOutErrors_delta" data-searchable="false" data-visible="<?php echo $errors_visible ?>"
                    data-css-class="red">Errors Out Rate
                </th>
                <th data-column-id="ifInErrors" data-searchable="false" data-visible="<?php echo $errors_visible ?>"
                    data-css-class="red"<?php echo $error_sort ?>>Errors In
                </th>
                <th data-column-id="ifOutErrors" data-searchable="false" data-visible="<?php echo $errors_visible ?>"
                    data-css-class="red">Errors Out
                </th>
                <th data-column-id="ifType">Media</th>
                <th data-column-id="ifAlias">Description</th>
                <th data-column-id="actions" data-sortable="false" data-searchable="false">Actions</th>
            </tr>
            </thead>
        </table>
    </div>
</div>
<script>

function formatUnits(units,decimals,display,base) {
    if(!units) return '';
    if(display === undefined) display = ['bps', 'Kbps', 'Mbps', 'Gbps', 'Tbps', 'Pbps', 'Ebps', 'Zbps', 'Ybps'];
    if(units == 0) return units + display[0];
    base = base || 1000; // or 1024 for binary
    var dm = decimals || 2;
    var i = Math.floor(Math.log(units) / Math.log(base));
    return parseFloat((units / Math.pow(base, i)).toFixed(dm)) + display[i];
}

var grid = $("#ports").bootgrid({
    ajax: true,
    rowCount: [50, 100, 250, -1],
    converters: {
        'duration': {
            to: function (value) { return moment.duration(value, 'seconds').humanize(); }
        },
        'human-bps': {
            to: function (value) { return formatUnits(value); }
        },
        'human-pps': {
            to: function (value) {
                return formatUnits(value, 2, ['pps', 'Kpps', 'Mpps', 'Gpps', 'Tpps', 'Ppps', 'Epps', 'Zpps', 'Ypps']);
            }
        }
    },
    formatters: {
      'device': function (column, row) {
          return "<span class='alert-status " + row.status + "' style='float:left;margin-right:10px;'></span>" + row.device + "";
      },
      'port': function (column, row) {
          return row.port
      }
    },
    templates: {
        search: "" // hide the generic search
    },
    post: function ()
    {
        return {
            device_id: '<?php echo htmlspecialchars($vars['device_id'] ?? ''); ?>',
            hostname: '<?php echo htmlspecialchars($vars['hostname'] ?? ''); ?>',
            state: '<?php echo htmlspecialchars($vars['state'] ?? ''); ?>',
            ifSpeed: '<?php echo htmlspecialchars($vars['ifSpeed'] ?? ''); ?>',
            ifType: '<?php echo htmlspecialchars($vars['ifType'] ?? ''); ?>',
            port_descr_type: '<?php echo htmlspecialchars($vars['port_descr_type'] ?? ''); ?>',
            ifAlias: '<?php echo htmlspecialchars($vars['ifAlias'] ?? ''); ?>',
            location: '<?php echo htmlspecialchars($vars['location'] ?? '') ?>',
            disabled: '<?php echo htmlspecialchars($vars['disabled'] ?? ''); ?>',
            ignore: '<?php echo htmlspecialchars($vars['ignore'] ?? ''); ?>',
            deleted: '<?php echo htmlspecialchars($vars['deleted'] ?? ''); ?>',
            errors: '<?php echo htmlspecialchars($vars['errors'] ?? ''); ?>',
            group: '<?php echo htmlspecialchars($vars['group'] ?? ''); ?>',
            devicegroup: '<?php echo htmlspecialchars($vars['devicegroup'] ?? ''); ?>',
        };
    },
    url: '<?php echo route('table.ports') ?>'
});

$(".actionBar").append("<div class=\"pull-left\"><?php echo $output; ?></div>");

init_select2('#device_id', 'device', {}, <?php echo $device_selected ?>, 'All Devices');
init_select2('#location', 'location', {}, <?php echo $location_selected ?>, 'All Locations');

</script>
