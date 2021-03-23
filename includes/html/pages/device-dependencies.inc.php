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

$pagetitle[] = 'Device Dependencies';

require_once 'includes/html/modal/delete_host_dependency.inc.php';
require_once 'includes/html/modal/edit_host_dependency.inc.php';
require_once 'includes/html/modal/manage_host_dependencies.inc.php';
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
                <th data-column-id="id" data-type="int" data-sortable="true" data-visible="true">Id</th>
                <th data-column-id="hostname" data-type="string" data-css-class="childhost" data-formatter="hostname">Hostname</th>
                <th data-column-id="sysname" data-type="string" data-visible="false">Sysname</th>
                <th data-column-id="parent" data-type="string" data-css-class="parenthost" data-formatter="parent">Parent Device(s)</th>
                <th data-column-id="parentid" data-visible="false">Parent ID</th>
                <th data-column-id="actions" data-sortable="false" data-searchable="false" data-formatter="actions">Actions</th>
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
<?php if (Auth::user()->hasGlobalAdmin()) { ?>
                        <div class="col-sm-8 actionBar"> \
                            <span class="pull-left"> \
                            <button type="button" class="btn btn-primary btn-sm command-manage" data-toggle="modal" data-target="#manage-dependencies" data-template_id="">Manage Device Dependencies</button> \
                            </span> \
                        </div> \
                <div class="col-sm-4 actionBar"><p class="{{css.search}}"></p><p class="{{css.actions}}"></p></div></div></div>'
<?php } else { ?>
                <div class="actionBar"><p class="{{css.search}}"></p><p class="{{css.actions}}"></p></div></div></div>'

<?php } ?>
    },
    formatters: {
        "actions": function(column, row) {
            var buttonDisabled = '';
            var response =  "<button type='button' class='btn btn-primary btn-sm command-edit' aria-label='Edit' data-toggle='modal' data-target='#edit-dependency' data-device_id='"+row.deviceid+"' data-host_name='"+row.hostname+"' data-parent_id='"+row.parentid+"' name='edit-host-dependency'><i class='fa fa-pencil' aria-hidden='true'></i></button> ";
            if (row.parent == 'None') {
                buttonDisabled = ' disabled';
            }
            response += "<button type='button' class='command-delete btn btn-danger btn-sm"+buttonDisabled+"' aria-label='Delete' data-toggle='modal' data-target='#confirm-delete' data-device_id='"+row.deviceid+"' data-device_parent ='"+row.parentid+"' data-host_name='"+row.hostname+"' name='delete-host-dependency'"+buttonDisabled+"><i class='fa fa-trash' aria-hidden='true'></i></button>";
            return response;
        },
        "hostname": function(column, row) {
            return '<a href="device/device='+row.deviceid+'/" class="list-device">'+row.hostname+'</a><br />'+row.sysname;
        },
        "parent": function(column, row) {
            if (row.parent == 'None') {
                return 'None';
            } else {
                var temp = Array();
                var tempids = Array();
                var counter = 0;
                temp = row.parent.split(',');
                tempids = row.parentid.split(',');
                var retstr = '';
                for (i=0; i < temp.length; i++) {
                    retstr = retstr + '<a href="device/device='+tempids[i]+'/" class="list-device">'+temp[i]+'</a>, ';
                }
                return retstr.slice(0, -2);
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

$(document).ready(function() {
    var editSelect = $('#availableparents').select2({
        dropdownParent: $('#edit-dependency'),
        width: 'resolve'

    });

    var manParentDevstoClr = $('#manclearchildren').select2({
        dropdownParent: $('#manage-dependencies'),
        width: 'resolve'
    });

    var manParentDevs = $('#manavailableparents').select2({
        dropdownParent: $('#manage-dependencies'),
        width: 'resolve'
    });

    var manAllDevs = $('#manalldevices').select2({
        dropdownParent: $('#manage-dependencies'),
        width: 'resolve'
    });

    $.ajax({
        type: "POST",
        url: 'ajax_form.php',
        data: {type: 'get-host-dependencies', "viewtype": 'fulllist' },
        dataType: "json",
        success: function(output) {
            if (output.status == 0) {
                manParentDevs.append($('<option>', { value: 0, text: 'None'}));
                editSelect.append($('<option>', { value: 0, text: 'None'}));
                manParentDevstoClr.append($('<option>', { value: 0, text: 'None'}));
                $.each(output.deps, function (i,elem) {
                    var devtext = elem.hostname + ' (' + elem.sysName + ')';
                    manParentDevs.append($('<option>',{value:elem.id, text:devtext}));
                    editSelect.append($('<option>',{value:elem.id, text:devtext}));
                    manAllDevs.append($('<option>',{value:elem.id, text:devtext}));
                    manParentDevstoClr.append($('<option>',{value:elem.id, text:devtext}));
                });
            } else {
                toastr.error('Device dependencies could not be retrieved from the database');
            }
        },
        error: function() {
            toastr.error('Device dependencies could not be retrieved from the database');
        }
    });
});
</script>
