<?php

$pagetitle[] = 'Inventory';

?>

<div class="panel panel-default panel-condensed">
    <div class="panel-heading">
        <strong>Inventory</strong>
    </div>
    <table id="inventory" class="table table-hover table-condensed table-striped">
        <thead>
            <tr>
                <th data-column-id="hostname" data-order="asc">Hostname</th>
                <th data-column-id="description">Description</th>
                <th data-column-id="name">Name</th>
                <th data-column-id="model">Part No</th>
                <th data-column-id="serial">Serial No</th>
            </tr>
        </thead>
    </table>
</div>

<script>

var grid = $("#inventory").bootgrid({
    ajax: true,
    rowCount: [50, 100, 250, -1],
    templates: {
        header: "<div id=\"{{ctx.id}}\" class=\"{{css.header}}\"><div class=\"row\">"+
                "<div class=\"col-sm-9 actionBar\"><span class=\"pull-left\"><form method=\"post\" action=\"\" class=\"form-inline\" role=\"form\">"+
                "<?php echo addslashes(csrf_field()) ?>"+
                "<div class=\"form-group\">"+
                "<input type=\"text\" name=\"string\" id=\"string\" value=\"<?php echo $_POST['string']; ?>\" placeholder=\"Description\" class=\"form-control input-sm\" />"+
                "</div>"+
                "<div class=\"form-group\">"+
                "<strong>&nbsp;Part No&nbsp;</strong>"+
                "<select name=\"part\" id=\"part\" class=\"form-control input-sm\">"+
                "<option value=\"\">All Parts</option>"+
<?php
foreach (dbFetchRows('SELECT `entPhysicalModelName` FROM `entPhysical` GROUP BY `entPhysicalModelName` ORDER BY `entPhysicalModelName`') as $data) {
    echo '"<option value=\"' . $data['entPhysicalModelName'] . '\""+';
    if ($data['entPhysicalModelName'] == $_POST['part']) {
        echo '" selected"+';
    }

    echo '">' . $data['entPhysicalModelName'] . '</option>"+';
}
?>
                 "</select>"+
                 "</div>"+
                 "<div class=\"form-group\">"+
                 "<input type=\"text\" name=\"serial\" id=\"serial\" value=\"<?php echo $_POST['serial']; ?>\" placeholder=\"Serial\" class=\"form-control input-sm\"/>"+
                 "</div>"+
                 "<div class=\"form-group\">"+
                 "<strong>&nbsp;Device&nbsp;</strong>"+
                 "<select name=\"device\" id=\"device\" class=\"form-control input-sm\">"+
                 "<option value=\"\">All Devices</option>"+
<?php
foreach (dbFetchRows('SELECT * FROM `devices` ORDER BY `hostname`') as $data) {
    if (device_permitted($data['device_id'])) {
        echo '"<option value=\"' . $data['device_id'] . '\""+';
        if ($data['device_id'] == $_POST['device']) {
            echo '" selected"+';
        }

        echo '">' . format_hostname($data, $data['hostname']) . '</option>"+';
    }
}
?>
                 "</select>"+
                 "</div>"+
                 "<div class=\"form-group\">"+
                 "<input type=\"text\" size=24 name=\"device_string\" id=\"device_string\" value=\""+
                    <?php
                    if ($_POST['device_string']) {
                        echo $_POST['device_string'];
                    }
                    ?>
                 "\" placeholder=\"Description\" class=\"form-control input-sm\"/>"+
                 "</div>"+
                 "<button type=\"submit\" class=\"btn btn-default input-sm\">Search</button>"+
                 "</form></span></div>"+
                 "<div class=\"col-sm-3 actionBar\"><p class=\"{{css.actions}}\"></p></div></div></div>"
    },
    post: function ()
    {
        return {
            id: "inventory",
            device: '<?php echo htmlspecialchars($_POST['device']); ?>',
            string: '<?php echo $_POST['string']; ?>',
            device_string: '<?php echo $_POST['device_string']; ?>',
            part: '<?php echo $_POST['part']; ?>',
            serial: '<?php echo $_POST['serial']; ?>'
        };
    },
    url: "ajax_table.php"
});

</script>
