<?php

// Set Defaults here

if(!isset($vars['format'])) {
    $vars['format'] = "list_detail";
}

$pagetitle[] = "Devices";

print_optionbar_start();

echo('<span style="font-weight: bold;">Lists</span> &#187; ');

$menu_options = array('basic' => 'Basic', 'detail' => 'Detail');

$sep = "";
foreach ($menu_options as $option => $text) {
    echo($sep);
    if ($vars['format'] == "list_".$option) {
        echo("<span class='pagemenu-selected'>");
    }
    echo('<a href="' . generate_url($vars, array('format' => "list_".$option)) . '">' . $text . '</a>');
    if ($vars['format'] == "list_".$option) {
        echo("</span>");
    }
    $sep = " | ";
}

?>

 |

<span style="font-weight: bold;">Graphs</span> &#187;

<?php

$menu_options = array('bits'        => 'Bits',
    'processor'   => 'CPU',
    'ucd_load'    => 'Load',
    'mempool'     => 'Memory',
    'uptime'      => 'Uptime',
    'storage'     => 'Storage',
    'diskio'      => 'Disk I/O',
    'poller_perf' => 'Poller',
    'ping_perf'   => 'Ping'
);
$sep = "";
foreach ($menu_options as $option => $text) {
    echo($sep);
    if ($vars['format'] == 'graph_'.$option) {
        echo("<span class='pagemenu-selected'>");
    }
    echo('<a href="' . generate_url($vars, array('format' => 'graph_'.$option, 'from' => '-24h', 'to' => 'now')) . '">' . $text . '</a>');
    if ($vars['format'] == 'graph_'.$option) {
        echo("</span>");
    }
    $sep = " | ";
}

?>

<div style="float: right;">

  <select name='type' id='type'
    onchange="window.open(this.options[this.selectedIndex].value,'_top')" >
<?php
    $type = 'device';
    foreach (get_graph_subtypes($type) as $avail_type) {
        echo("<option value='".generate_url($vars, array('format' => 'graph_'.$avail_type))."'");
        if ('graph_'.$avail_type == $vars['format']) {
            echo(" selected");
        }
        $display_type = is_mib_graph($type, $avail_type) ? $avail_type : nicecase($avail_type);
        echo(">$display_type</option>");
    }
?>
    </select>

<?php

if (isset($vars['searchbar']) && $vars['searchbar'] == "hide") {
    echo('<a href="'. generate_url($vars, array('searchbar' => '')).'">Restore Search</a>');
}
else {
    echo('<a href="'. generate_url($vars, array('searchbar' => 'hide')).'">Remove Search</a>');
}

echo("  | ");

if (isset($vars['bare']) && $vars['bare'] == "yes") {
    echo('<a href="'. generate_url($vars, array('bare' => '')).'">Restore Header</a>');
}
else {
    echo('<a href="'. generate_url($vars, array('bare' => 'yes')).'">Remove Header</a>');
}

print_optionbar_end();
?>

</div>

<?php

list($format, $subformat) = explode("_", $vars['format'], 2);

if($format == "graph") {

    if (empty($vars['from'])) {
        $graph_array['from'] = $config['time']['day'];
    }
    else {
        $graph_array['from'] = $vars['from'];
    }
    if (empty($vars['to'])) {
        $graph_array['to'] = $config['time']['now'];
    }
    else {
        $graph_array['to'] = $vars['to'];
    }

    echo "
    <div class='well well-sm'>
        <div class='container-fluid'>
            <div class='row'>
                <div class='col-md-12'>
    ";
    include_once 'includes/print-date-selector.inc.php';
    echo '
                </div>
            </div>
        </div>
    </div>
    ';

    $sql_param = array();

    if(isset($vars['state'])) {
        if($vars['state'] == 'up') {
            $state = '1';
        }
        elseif($vars['state'] == 'down') {
            $state = '0';
        }
    }

    if (!empty($vars['hostname'])) {
        $where .= " AND hostname LIKE ?";
        $sql_param[] = "%".$vars['hostname']."%";
    }
    if (!empty($vars['os'])) {
        $where .= " AND os = ?";
        $sql_param[] = $vars['os'];
    }
    if (!empty($vars['version'])) {
        $where .= " AND version = ?";
        $sql_param[] = $vars['version'];
    }
    if (!empty($vars['hardware'])) {
        $where .= " AND hardware = ?";
        $sql_param[] = $vars['hardware'];
    }
    if (!empty($vars['features'])) {
        $where .= " AND features = ?";
        $sql_param[] = $vars['features'];
    }

    if (!empty($vars['type'])) {
        if ($vars['type'] == 'generic') {
            $where .= " AND ( type = ? OR type = '')";
            $sql_param[] = $vars['type'];
        }
        else {
            $where .= " AND type = ?";
            $sql_param[] = $vars['type'];
        }
    }
    if (!empty($vars['state'])) {
        $where .= " AND status= ?";
        $sql_param[] = $state;
        $where .= " AND disabled='0' AND `ignore`='0'";
        $sql_param[] = '';
    }
    if (!empty($vars['disabled'])) {
        $where .= " AND disabled= ?";
        $sql_param[] = $vars['disabled'];
    }
    if (!empty($vars['ignore']))   {
        $where .= " AND `ignore`= ?";
        $sql_param[] = $vars['ignore'];
    }
    if (!empty($vars['location']) && $vars['location'] == "Unset") {
        $location_filter = '';
    }
    if (!empty($vars['location'])) {
        $location_filter = $vars['location'];
    }
    if( !empty($vars['group']) ) {
        require_once('../includes/device-groups.inc.php');
        $where .= " AND ( ";
        foreach( GetDevicesFromGroup($vars['group']) as $dev ) {
            $where .= "device_id = ? OR ";
            $sql_param[] = $dev['device_id'];
        }
        $where = substr($where, 0, strlen($where)-3);
        $where .= " )";
    }

    $query = "SELECT * FROM `devices` WHERE 1 ";

    if (isset($where)) {
        $query .= $where;
    }

    $query .= " ORDER BY hostname";

    $row = 1;
    foreach (dbFetchRows($query, $sql_param) as $device) {
        if (is_integer($row/2)) {
            $row_colour = $list_colour_a;
        }
        else {
            $row_colour = $list_colour_b;
        }

        if (device_permitted($device['device_id'])) {
            if (!$location_filter || $device['location'] == $location_filter) {
                $graph_type = "device_".$subformat;

                if ($_SESSION['widescreen']) {
                    $width=270;
                }
                else {
                    $width=315;
                }

                $graph_array_new                = array();
                $graph_array_new['type']        = $graph_type;
                $graph_array_new['device']      = $device['device_id'];
                $graph_array_new['height']      = '110';
                $graph_array_new['width']       = $width;
                $graph_array_new['legend']      = 'no';
                $graph_array_new['title']       = 'yes';
                $graph_array_new['from']        = $graph_array['from'];
                $graph_array_new['to']        = $graph_array['to'];

                $graph_array_zoom           = $graph_array_new;
                $graph_array_zoom['height'] = '150';
                $graph_array_zoom['width']  = '400';
                $graph_array_zoom['legend'] = 'yes';

                $overlib_link = "device/device=".$device['device_id']."/";
                echo "<div style='display: block; padding: 1px; margin: 2px; min-width: ".($width+90)."px; max-width:".($width+90)."px; min-height:170px; max-height:170px; text-align: center; float: left; background-color: #f5f5f5;'>";
                echo '<div class="panel panel-default">';
                echo overlib_link($overlib_link, generate_lazy_graph_tag($graph_array_new), generate_graph_tag($graph_array_zoom), NULL);
                echo "</div></div>\n\n";
            }
        }
    }
}
else {

?>

<div class="panel panel-default panel-condensed">
    <div class="table-responsive">
        <table id="devices" class="table table-condensed table-hover">
            <thead>
                <tr>
                    <th data-column-id="status" data-searchable="false" data-formatter="status">Status</th>
                    <th data-column-id="icon" data-sortable="false" data-searchable="false">Vendor</th>
                    <th data-column-id="hostname" data-order="asc">Device</th>
                    <th data-column-id="ports" data-sortable="false" data-searchable="false"></th>
                    <th data-column-id="hardware">Platform</th>
                    <th data-column-id="os">Operating System</th>
                    <th data-column-id="uptime">Uptime/Location</th>
                    <th data-column-id="actions" data-sortable="false" data-searchable="false">Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<script>

    searchbar = "<div id=\"{{ctx.id}}\" class=\"{{css.header}}\"><div class=\"row\">"+
                "<div class=\"col-sm-9 actionBar\"><span class=\"pull-left\"><form method=\"post\" action=\"\" class=\"form-inline\" role=\"form\">"+
                "<span class=\"pull-left\"><div class=\"form-group\">"+
                "<input type=\"text\" name=\"hostname\" id=\"hostname\" value=\"<?php echo($vars['hostname']); ?>\" class=\"form-control input-sm\" placeholder=\"Hostname\"/>"+
                "</div>"+
                "<div class=\"form-group\">"+
                "<select name='os' id='os' class=\"form-control input-sm\">"+
                "<option value=''>All OSes</option>"+
<?php

    if (is_admin() === TRUE || is_read() === TRUE) {
        $sql = "SELECT `os` FROM `devices` AS D WHERE 1 GROUP BY `os` ORDER BY `os`";
    }
    else {
        $sql = "SELECT `os` FROM `devices` AS `D`, `devices_perms` AS `P` WHERE `P`.`user_id` = ? AND `P`.`device_id` = `D`.`device_id` GROUP BY `os` ORDER BY `os`";
        $param[] = $_SESSION['user_id'];
    }
    foreach (dbFetch($sql,$param) as $data) {
        if ($data['os']) {
            $tmp_os = clean_bootgrid($data['os']);
            echo('"<option value=\"'.$tmp_os.'\""+');
            if ($tmp_os == $vars['os']) {
                echo('" selected "+');
            }
            echo('">'.$config['os'][$tmp_os]['text'].'</option>"+');
        }
    }
?>
                "</select>"+
                "</div>"+
                "<div class=\"form-group\">"+
                "<select name='version' id='version' class=\"form-control input-sm\">"+
                "<option value=''>All Versions</option>"+
<?php

    if (is_admin() === TRUE || is_read() === TRUE) {
        $sql = "SELECT `version` FROM `devices` AS D WHERE 1 GROUP BY `version` ORDER BY `version`";
    }
    else {
        $sql = "SELECT `version` FROM `devices` AS `D`, `devices_perms` AS `P` WHERE `P`.`user_id` = ? AND `P`.`device_id` = `D`.`device_id` GROUP BY `version` ORDER BY `version`";
        $param[] = $_SESSION['user_id'];
    }
    foreach (dbFetch($sql,$param) as $data) {
        if ($data['version']) {
            $tmp_version = clean_bootgrid($data['version']);
            echo('"<option value=\"'.$tmp_version.'\""+');
            if ($tmp_version == $vars['version']) {
                echo('" selected "+');
            }
            echo('">'.$tmp_version.'</option>"+');
        }
    }
?>
                "</select>"+
                "</div>"+
                "<div class=\"form-group\">"+
                "<select name=\"hardware\" id=\"hardware\" class=\"form-control input-sm\">"+
                 "<option value=\"\">All Platforms</option>"+
<?php

    if (is_admin() === TRUE || is_read() === TRUE) {
        $sql = "SELECT `hardware` FROM `devices` AS D WHERE 1 GROUP BY `hardware` ORDER BY `hardware`";
    }
    else {
        $sql = "SELECT `hardware` FROM `devices` AS `D`, `devices_perms` AS `P` WHERE `P`.`user_id` = ? AND `P`.`device_id` = `D`.`device_id` GROUP BY `hardware` ORDER BY `hardware`";
        $param[] = $_SESSION['user_id'];
    }
    foreach (dbFetch($sql,$param) as $data) {
        if ($data['hardware']) {
            $tmp_hardware = clean_bootgrid($data['hardware']);
            echo('"<option value=\"'.$tmp_hardware.'\""+');
            if ($tmp_hardware == $vars['hardware']) {
                echo('" selected"+');
            }
            echo('">'.$tmp_hardware.'</option>"+');
        }
    }

?>
                "</select>"+
                "</div>"+
                "<div class=\"form-group\">"+
                "<select name=\"features\" id=\"features\" class=\"form-control input-sm\">"+
                "<option value=\"\">All Featuresets</option>"+
<?php

    if (is_admin() === TRUE || is_read() === TRUE) {
        $sql = "SELECT `features` FROM `devices` AS D WHERE 1 GROUP BY `features` ORDER BY `features`";
    }
    else {
        $sql = "SELECT `features` FROM `devices` AS `D`, `devices_perms` AS `P` WHERE `P`.`user_id` = ? AND `P`.`device_id` = `D`.`device_id` GROUP BY `features` ORDER BY `features`";
        $param[] = $_SESSION['user_id'];
    }

    foreach (dbFetch($sql,$param) as $data) {
        if ($data['features']) {
            $tmp_features = clean_bootgrid($data['features']);
            echo('"<option value=\"'.$tmp_features.'\""+');
            if ($tmp_features == $vars['features']) {
                echo('" selected"+');
            }
            echo('">'.$tmp_features.'</option>"+');
        }
    }

?>
                   "</select>"+
                   "</div></span><span class=\"pull-left\">"+
                   "<div class=\"form-group\">"+
                   "<select name=\"location\" id=\"location\" class=\"form-control input-sm\">"+
                   "<option value=\"\">All Locations</option>"+

<?php
// fix me function?

    foreach (getlocations() as $location) {
        if ($location) {
            $location = clean_bootgrid($location);
            echo('"<option value=\"'.$location.'\""+');
            if ($location == $vars['location']) {
                echo('" selected"+');
            }
            echo('">'.$location.'</option>"+');
        }
    }
?>
                    "</select>"+
                    "</div>"+
                    "<div class=\"form-group\">"+
                    "<select name=\"type\" id=\"type\" class=\"form-control input-sm\">"+
                    "<option value=\"\">All Device Types</option>"+
<?php

    if (is_admin() === TRUE || is_read() === TRUE) {
        $sql = "SELECT `type` FROM `devices` AS D WHERE 1 GROUP BY `type` ORDER BY `type`";
    }
    else {
        $sql = "SELECT `type` FROM `devices` AS `D`, `devices_perms` AS `P` WHERE `P`.`user_id` = ? AND `P`.`device_id` = `D`.`device_id` GROUP BY `type` ORDER BY `type`";
        $param[] = $_SESSION['user_id'];
    }
    foreach (dbFetch($sql,$param) as $data) {
        if ($data['type']) {
            echo('"<option value=\"'.$data['type'].'\""+');
            if ($data['type'] == $vars['type']) {
                echo('" selected"+');
            }
            echo('">'.ucfirst($data['type']).'</option>"+');
        }
    }

?>
                      "</select>"+
                      "</div>"+
                      "<button type=\"submit\" class=\"btn btn-default input-sm\">Search</button>"+
                      "<div class=\"form-group\">"+
                      "<a href=\"<?php echo(generate_url($vars)); ?>\" title=\"Update the browser URL to reflect the search criteria.\" >&nbsp;Update URL</a> |"+
                      "<a href=\"<?php echo(generate_url(array('page' => 'devices', 'section' => $vars['section'], 'bare' => $vars['bare']))); ?>\" title=\"Reset critera to default.\" >&nbsp;Reset</a>"+
                      "</div>"+
                      "</form></span></div>"+
                      "<div class=\"col-sm-3 actionBar\"><p class=\"{{css.actions}}\"></p></div></div></div>"

<?php
    if (isset($vars['searchbar']) && $vars['searchbar'] == "hide") {
        echo "searchbar = ''";
    }
?>

var grid = $("#devices").bootgrid({
    ajax: true,
    rowCount: [50,100,250,-1],
    columnSelection: false,
    formatters: {
        "status": function(column,row) {
            return "<h4><span class='label label-"+row.extra+" threeqtr-width'>" + row.msg + "</span></h4>";
        }
    },
    templates: {
        header: searchbar
    },
    post: function ()
    {
        return {
            id: "devices",
            format: '<?php echo mres($vars['format']); ?>',
            hostname: '<?php echo htmlspecialchars($vars['hostname']); ?>',
            os: '<?php echo mres($vars['os']); ?>',
            version: '<?php echo mres($vars['version']); ?>',
            hardware: '<?php echo mres($vars['hardware']); ?>',
            features: '<?php echo mres($vars['features']); ?>',
            location: '<?php echo mres($vars['location']); ?>',
            type: '<?php echo mres($vars['type']); ?>',
            state: '<?php echo mres($vars['state']); ?>',
            disabled: '<?php echo mres($vars['disabled']); ?>',
            ignore: '<?php echo mres($vars['ignore']); ?>',
            group: '<?php echo mres($vars['group']); ?>',
        };
    },
    url: "ajax_table.php"
});

</script>

<?php

}
