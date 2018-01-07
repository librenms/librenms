<?php

$no_refresh = true;

require_once 'includes/modal/alert_template.inc.php';
require_once 'includes/modal/delete_alert_template.inc.php';
require_once 'includes/modal/attach_alert_template.inc.php';

echo '<div class="panel panel-default panel-condensed">';
echo '<div class="panel-heading">';
echo '<strong>Alert templates</strong>';

if ($_SESSION['userlevel'] >= '10') {
    echo '<div class="pull-right">';
    echo '<span style="font-weight:bold;">Actions &#187;&nbsp;</span>';
    echo '<a href="#" data-toggle="modal" data-target="#alert-template" data-template_id="">Create new alert template</a>';
    echo '</div>';
}

echo '</div>';
echo '<div class="panel-body">';
echo '<div style="margin:10px 10px 0px 10px;" id="message"></div>';

echo '<div class="table-responsive">';
echo '<table id="templatetable" class="table table-hover table-condensed table-striped">';
echo '<thead>';
echo '<th data-column-id="id" data-searchable="false" data-identifier="true" data-type="numeric">#</th>';
echo '<th data-column-id="templatename">Name</th>';
echo '<th data-column-id="actions" data-searchable="false" data-formatter="commands">Action</th>';
echo '</thead>';
echo '<tbody>';
echo '<tr data-row-id="0">';
echo '<td>0</td>';
echo '<td>Default Alert Template</td>';
echo '<td></td>';
echo '</tr>';

$full_query = "SELECT id, name from alert_templates";
foreach (dbFetchRows($full_query, $param) as $template) {
    if ($template['name'] == 'Default Alert Template') {
        $default_tplid = $template['id'];
        continue;
    }
    echo '<tr data-row-id="'.$template['id'].'">';
    echo '<td>'.$template['id'].'</td>';
    echo '<td>'.$template['name'].'</td>';
    echo '<td></td>';
    echo '</tr>';
}

echo '</tbody>';
echo '</table>';
echo '</div>';

?>
<script>
$(document).ready(function() {
    var grid = $('#templatetable').bootgrid({
        rowCount: [50, 100, 250, -1],
        formatters: {
            "commands": function(column, row) {
                var response = '';
                if(row.id == 0) {
                    response = "<button type=\"button\" class=\"btn btn-xs btn-primary command-edit\" data-toggle='modal' data-target='#alert-template' data-template_id=\"" + row.id + "\" data-template_action='edit' name='edit-alert-template'><i class=\"fa fa-pencil\" aria-hidden=\"true\"></i></button> " + "<button type=\"button\" class=\"btn btn-xs btn-danger command-delete\" disabled=\"disabled\"><i class=\"fa fa-trash-o\" aria-hidden=\"true\"></i></button> " + "<button type='button' class='btn btn-warning btn-xs command-attach' disabled=\"disabled\"><i class='fa fa-th-list' aria-hidden='true'></i></button>";
                } else {
                    response = "<button type=\"button\" class=\"btn btn-xs btn-primary command-edit\" data-toggle='modal' data-target='#alert-template' data-template_id=\"" + row.id + "\" data-template_action='edit' name='edit-alert-template'><i class=\"fa fa-pencil\" aria-hidden=\"true\"></i></button> " + "<button type=\"button\" class=\"btn btn-xs btn-danger command-delete\" data-toggle=\"modal\" data-target='#confirm-delete-alert-template' data-template_id=\"" + row.id + "\" name='delete-alert-template'><i class=\"fa fa-trash-o\" aria-hidden=\"true\"></i></button> " + "<button type='button' class='btn btn-warning btn-xs command-attach' data-toggle='modal' data-target='#attach-alert-template' data-template_id='" + row.id + "' name='attach-alert-template'><i class='fa fa-th-list' aria-hidden='true'></i></button>";
                }
                return response;
            }
        },
    }).on("loaded.rs.jquery.bootgrid", function() {
        /* Executes after data is loaded and rendered */
        grid.find(".command-edit").on("click", function(e) {
            var localtmpl_id = $(this).data("template_id");
            if(localtmpl_id == 0) {
                $('#default_template').val("1");
                $('#template_id').val(<?=$default_tplid?>);
            } else {
                $('#default_template').val("0");
                $('#template_id').val(localtmpl_id);
            }
            $("#alert-template").modal('show');
        }).end().find(".command-delete").on("click", function(e) {
            $('#template_id').val($(this).data("template_id"));
            $('#confirm-delete-alert-template').modal('show');
        }).end().find(".command-attach").on("click", function(e) {
            $('#template_id').val($(this).data("template_id"));
            $('#attach-alert-template').modal('show');
        });
    });
});
</script>
