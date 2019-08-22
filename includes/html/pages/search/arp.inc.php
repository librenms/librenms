<div class="panel panel-default panel-condensed">
    <div class="panel-heading">
        <strong>ARP Entries</strong>
    </div>
    <table id="arp-search" class="table table-hover table-condensed table-striped">
        <thead>
            <tr>
                <th data-column-id="mac_address">MAC Address</th>
                <th data-column-id="ipv4_address">IP Address</th>
                <th data-column-id="hostname" data-order="asc">Device</th>
                <th data-column-id="interface">Interface</th>
                <th data-column-id="remote_device" data-sortable="false">Remote device</th>
                <th data-column-id="remote_interface" data-sortable="false">Remote interface</th>
            </tr>
        </thead>
    </table>
</div>

<script>

var grid = $("#arp-search").bootgrid({
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

            // Select the devices only with ARP tables
$sql = 'SELECT D.device_id AS device_id, `hostname`, `D`.`sysName` AS `sysName` FROM `ipv4_mac` AS M, `ports` AS P, `devices` AS D';

if (!Auth::user()->hasGlobalRead()) {
    $sql    .= ' LEFT JOIN `devices_perms` AS `DP` ON `D`.`device_id` = `DP`.`device_id`';
    $where  .= ' AND `DP`.`user_id`=?';
    $param[] = Auth::id();
}

$sql .= " WHERE M.port_id = P.port_id AND P.device_id = D.device_id $where GROUP BY `D`.`device_id`, `D`.`hostname`, `D`.`sysName` ORDER BY `hostname`";
foreach (dbFetchRows($sql, $param) as $data) {
    echo '"<option value=\"'.$data['device_id'].'\""+';
    if ($data['device_id'] == $_POST['device_id']) {
        echo '" selected "+';
    }

    echo '">'.format_hostname($data).'</option>"+';
}
?>
                "</select>"+
                "</div>"+
                "<div class=\"form-group\">"+
                "<select name=\"searchby\" id=\"searchby\" class=\"form-control input-sm\">"+
                "<option value=\"mac\" "+
<?php
if ($_POST['searchby'] != 'ip') {
    echo '" selected "+';
}
?>

                ">MAC Address</option>"+
                "<option value=\"ip\" "+
<?php
if ($_POST['searchby'] == 'ip') {
    echo '" selected "+';
}
?>

                ">IP Address</option>"+
                "</select>"+
                "</div>"+
                "<div class=\"form-group\">"+
                "<input type=\"text\" name=\"searchPhrase\" id=\"address\" value=\""+
<?php
echo '"'.$_POST['searchPhrase'].'"+';
?>

                "\" class=\"form-control input-sm\" placeholder=\"Address\" />"+
                "</div>"+
                "<button type=\"submit\" class=\"btn btn-default input-sm\">Search</button>"+
                "</form></span></div>"+
               "<div class=\"col-sm-3 actionBar\"><p class=\"{{css.actions}}\"></p></div></div></div>"
    },
    post: function ()
    {
        return {
            id: "arp-search",
            device_id: '<?php echo htmlspecialchars($_POST['device_id']); ?>',
            searchby: '<?php echo mres($_POST['searchby']); ?>',
            searchPhrase: '<?php echo mres($_POST['searchPhrase']); ?>'
        };
    },
    url: "ajax_table.php"
});

</script>
