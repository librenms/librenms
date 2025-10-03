<div class="panel panel-default panel-condensed">
    <div class="panel-heading">
        <strong>Port Security</strong>
    </div>
    <table id="secureports-search" class="table table-hover table-condensed table-striped">
        <thead>
            <tr>
                <th data-column-id="device">Device</th>
                <th data-column-id="interface">Port</th>
                <th data-column-id="port_description">Description</th>
                <th data-column-id="enable">Enabled</th>
                <th data-column-id="status">Status</th>
                <th data-column-id="current_secure">Current MACs</th>
                <th data-column-id="max_secure">Max MACs</th>
                <th data-column-id="violation_action">Violation Action</th>
                <th data-column-id="violation_count">Violations</th>
                <th data-column-id="secure_last_mac">Last MAC</th>
                <th data-column-id="sticky_enable">Sticky</th>
            </tr>
        </thead>
    </table>
</div>

<script>

var grid = $("#secureports-search").bootgrid({
    ajax: true,
    rowCount: [50, 100, 250, -1],
    templates: {
        header: "<div id=\"@{{ctx.id}}\" class=\"@{{css.header}}\"><div class=\"row\">"+
                "<div class=\"col-sm-9 actionBar\"><span class=\"pull-left\">"+
                "<form method=\"post\" action=\"\" class=\"form-inline\" role=\"form\">"+
                "<?php echo addslashes(csrf_field()) ?>"+
                "<div class=\"form-group\">"+
                "<select name=\"device_id\" id=\"device_id\" class=\"form-control input-sm\">"+
                "<option value=\"\">All Devices</option>"+
<?php

$device_id = (int) ($vars['device_id'] ?? 0);
$searchby = $vars['searchby'] ?? 'status';
$searchPhrase = $vars['searchPhrase'] ?? '';

// Select the devices only with port security configured
$devices = \App\Models\Device::hasAccess(Auth::user())
    ->whereHas('portSecurity')
    ->orderBy('hostname')
    ->get(['device_id', 'hostname', 'sysName', 'display']);

foreach ($devices as $device) {
    $selected = ($device->device_id == $device_id) ? ' selected' : '';
    $device_array = $device->toArray();
    echo '"<option value=\"' . $device->device_id . '\"' . $selected . '>' .
         str_replace(['"', '\''], '', htmlentities(format_hostname($device_array))) . '</option>"+' . "\n";
}
?>
                "</select>"+
                "</div>"+
                "<div class=\"form-group\">"+
                "<select name=\"searchby\" id=\"searchby\" class=\"form-control input-sm\">"+
                "<option value=\"status\"<?php echo $searchby == 'status' ? ' selected' : ''; ?>>Status</option>"+
                "<option value=\"enable\"<?php echo $searchby == 'enable' ? ' selected' : ''; ?>>Enabled</option>"+
                "<option value=\"violation_action\"<?php echo $searchby == 'violation_action' ? ' selected' : ''; ?>>Violation Action</option>"+
                "<option value=\"secure_last_mac\"<?php echo $searchby == 'secure_last_mac' ? ' selected' : ''; ?>>Last MAC</option>"+
                "<option value=\"port\"<?php echo $searchby == 'port' ? ' selected' : ''; ?>>Port</option>"+
                "<option value=\"device\"<?php echo $searchby == 'device' ? ' selected' : ''; ?>>Device</option>"+
                "</select>"+
                "</div>"+
                "<div class=\"form-group\">"+
                "<input type=\"text\" name=\"searchPhrase\" id=\"searchPhrase\" value=\"<?php echo htmlspecialchars($searchPhrase); ?>\" class=\"form-control input-sm\" placeholder=\"Search...\" />"+
                "</div>"+
                "<button type=\"submit\" class=\"btn btn-default input-sm\">Search</button>"+
                "</form></span></div>"+
               "<div class=\"col-sm-3 actionBar\"><p class=\"@{{css.actions}}\"></p></div></div></div>"
    },
    post: function ()
    {
        return {
            device_id: '<?php echo $device_id ?: ''; ?>',
            searchby: '<?php echo htmlspecialchars($searchby); ?>',
            searchPhrase: '<?php echo htmlspecialchars($searchPhrase); ?>'
        };
    },
    url: "<?php echo url('/ajax/table/port-security'); ?>",
    formatters: {
        "status": function (column, row) {
            return row.status;
        }
    }
});

</script>