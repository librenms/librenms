<div class="panel panel-default panel-condensed">
    <div class="panel-heading">
        <strong>MAC Addresses</strong>
    </div>
    <table id="mac-search" class="table table-hover table-condensed table-striped">
        <thead>
            <tr>
                <th data-column-id="hostname" data-order="asc">Device</th>
                <th data-column-id="interface">Interface</th>
                <th data-column-id="address" data-sortable="false" data-formatter="tooltip">MAC Address</th>
<?php
if (\LibreNMS\Config::get('mac_oui.enabled') === true) {
    echo '                <th data-column-id="mac_oui" data-sortable="false" data-width="150px" data-visible="false" data-formatter="tooltip">Vendor</th>';
}
?>                <th data-column-id="description" data-sortable="false" data-formatter="tooltip">Description</th></tr>
            </tr>
        </thead>
    </table>
</div>

<script>

var grid = $("#mac-search").bootgrid({
    ajax: true,
    rowCount: [50, 100, 250, -1],
    templates: {
        header: "<div id=\"{{ctx.id}}\" class=\"{{css.header}}\"><div class=\"row\">"+
                "<div class=\"col-sm-9 actionBar\"><span class=\"pull-left\">"+
                "<form method=\"post\" action=\"\" class=\"form-inline\" role=\"form\">"+
                "<?php echo addslashes(csrf_field()) ?>"+
                "<div class=\"form-group\">"+
                "<select name=\"device_id\" id=\"device_id\" class=\"form-control input-sm\">"+
                "<option value=\"\">All Devices</option>"+
<?php

$sql = 'SELECT `devices`.`device_id`,`hostname`, `sysName` FROM `devices`';
$param = [];

if (! Auth::user()->hasGlobalRead()) {
    $device_ids = Permissions::devicesForUser()->toArray() ?: [0];
    $where .= ' WHERE `devices`.`device_id` IN ' . dbGenPlaceholders(count($device_ids));
    $param = array_merge($param, $device_ids);
}

$sql .= " $where ORDER BY `hostname`";
foreach (dbFetchRows($sql, $param) as $data) {
    echo '"<option value=\"' . $data['device_id'] . '\""+';
    if ($data['device_id'] == $_POST['device_id']) {
        echo '" selected "+';
    }

    echo '">' . format_hostname($data) . '</option>"+';
}
?>
               "</select>"+
               "</div>"+
               "<div class=\"form-group\">"+
               "<select name=\"interface\" id=\"interface\" class=\"form-control input-sm\">"+
               "<option value=\"\">All Interfaces</option>"+
               "<option value=\"Loopback%\" "+
<?php
if ($_POST['interface'] == 'Loopback%') {
    echo '" selected "+';
}
?>
               ">Loopbacks</option>"+
               "<option value=\"Vlan%\""+
<?php
if ($_POST['interface'] == 'Vlan%') {
    echo '" selected "+';
}
?>

               ">VLANs</option>"+
               "</select>"+
               "</div>"+
               "<div class=\"form-group\">"+
               "<input type=\"text\" name=\"address\" id=\"address\" value=\""+
<?php
echo '"' . $_POST['address'] . '"+';
?>

               "\" class=\"form-control input-sm\" placeholder=\"Mac Address\"/>"+
               "</div>"+
               "<button type=\"submit\" class=\"btn btn-default input-sm\">Search</button>"+
               "</form></span></div>"+
               "<div class=\"col-sm-3 actionBar\"><p class=\"{{css.actions}}\"></p></div></div></div>"
    },
    post: function ()
    {
        return {
            id: "address-search",
            search_type: "mac",
            device_id: '<?php echo htmlspecialchars($_POST['device_id']); ?>',
            interface: '<?php echo $_POST['interface']; ?>',
            address: '<?php echo $_POST['address']; ?>'
        };
    },
    url: "ajax_table.php",
    formatters: {
        "tooltip": function (column, row) {
                var value = row[column.id];
                var vendor = '';
                if (column.id == 'address' && ((vendor = row['mac_oui']) != '' )) {
                    return "<span title=\'" + value + " (" + vendor + ")\' data-toggle=\'tooltip\'>" + value + "</span>";
                }
                return "<span title=\'" + value + "\' data-toggle=\'tooltip\'>" + value + "</span>";
            },
    },
});

</script>
