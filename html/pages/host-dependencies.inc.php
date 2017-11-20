<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2017 Aldemir Akpinar <https://github.com/aldemira>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */


$no_refresh = true;

$pagetitle[] = 'Host Dependencies';

require_once 'includes/modal/delete_host_dependency.inc.php';
require_once 'includes/modal/edit_host_dependency.inc.php';
require_once 'includes/modal/manage_host_dependencies.inc.php';
?>
<div class="row">
    <div class="col-sm-12">
        <span id="message"></span>
    </div>
</div>

<div class="table-responsive">
    <table id="hostdeps" class="table table-hover table-condensed table-striped">
        <thead>
            <tr>
                <th data-column-id="deviceid" data-visible="false" data-css-class="deviceid">No</th>
                <th data-column-id="hostname" data-type="string" data-css-class="childhost" data-formatter="hostname">Hostname</th>
                <th data-column-id="parent" data-type="string" data-css-class="parenthost" data-formatter="parent">Parent Host</th>
                <th data-column-id="parentid" data-visible="false">Parent ID</th>
                <th data-column-id="actions" data-searchable="false" data-formatter="actions">Actions</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
<script>
var grid = $("#hostdeps").bootgrid({
    rowCount: [50, 100, 250, -1],
    ajax: true,
    post: function() {
        return {
            type: "get-host-dependencies",
            viewtype: "fulllist",
            format: "mainpage"
        };
    },
    url: "ajax_form.php",
    templates: {
        header: '<div id="{{ctx.id}}" class="{{css.header}}"> \
                    <div class="row"> \
<?php if (is_admin()) { ?>
                        <div class="col-sm-8 actionBar"> \
                            <span class="pull-left"> \
                            <button type="button" class="btn btn-primary btn-sm command-manage" data-toggle="modal" data-target="#manage-dependencies" data-template_id="">Manage Host Dependencies</button> \
                            </span> \
                        </div> \
                <div class="col-sm-4 actionBar"><p class="{{css.search}}"></p><p class="{{css.actions}}"></p></div></div></div>'
<?php } else { ?>
                <div class="actionBar"><p class="{{css.search}}"></p><p class="{{css.actions}}"></p></div></div></div>'

<?php } ?>
    },
    formatters: {
        "actions": function(column, row) {
            var buttonClass = '';
            var response =  "<button type='button' class='btn btn-primary btn-sm command-edit' aria-label='Edit' data-toggle='modal' data-target='#edit-dependency' data-device_id='"+row.deviceid+"' data-host_name='"+row.hostname+"' data-parent_id='"+row.parentid+"' name='edit-host-dependency'><i class='fa fa-pencil' aria-hidden='true'></i></button> ";
            if (row.parent == 'None') {
                buttonClass = 'command-delete btn btn-danger btn-sm disabled';
            } else {
                buttonClass = 'command-delete btn btn-danger btn-sm';
            }
            response += "<button type='button' class='"+buttonClass+"' aria-label='Delete' data-toggle='modal' data-target='#confirm-delete' data-device_id='"+row.deviceid+"' data-device_parent ='"+row.parentid+"' data-host_name='"+row.hostname+"' name='delete-host-dependency'><i class='fa fa-trash' aria-hidden='true'></i></button>";
            return response;
        },
        "hostname": function(column, row) {
            return '<a href="device/device='+row.deviceid+'/">'+row.hostname+'</a>';
        },
        "parent": function(column, row) {
            if (row.parent == 'None') {
                return 'None';
            } else {
                return '<a href="device/device='+row.parentid+'/">'+row.parent+'</a>';
            }
        }
    },
}).on("loaded.rs.jquery.bootgrid", function(e) {
    e.preventDefault();
        /* Executes after data is loaded and rendered */
    grid.find(".command-edit").on("click", function(e) {
        $('#edit-row_id').val($(this).parent().parent().data('row-id'));
        $("#edit-device_id").val($(this).data("device_id"));
        $("#edit-parent_id").val($(this).data("parent_id"));
        $('#edit-dependency').modal('show');
        $('.modalhostname').text($(this).data("host_name"));
    }).end().find(".command-delete").on("click", function(e) {
        $('#delete-row_id').val($(this).parent().parent().data('row-id'));
        $("#delete-device_id").val($(this).data("device_id"));
        $("#delete-parent_id").val($(this).data("device_parent"));
        $('#confirm-delete').modal('show');
        $('.modalhostname').text($(this).data("host_name"));
    }).end().find(".command-manage").on("click", function(e) {
        $('#manage-dependencies').modal('show');
    });
});
</script>
