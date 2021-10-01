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

if (! Auth::user()->hasGlobalAdmin()) {
    exit('ERROR: You need to be admin');
}

?>

<div class="modal fade" id="manage-dependencies" role="dialog" aria-labelledby="mandeps" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h5 class="modal-title" id="mandeps">Device Dependency for Multiple Devices</h5>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs" role="tablist" id="manhostdepstabs">
                    <li role="presentation" class="active"><a href="#bulkadd" aria-controls="bulkadd" role="tab" data-toggle="tab">Bulk Add</a></li>
                    <li role="presentation"><a href="#clearall" aria-controls="clearall" role="tab" data-toggle="tab">Clear All</a></li>
                </ul>
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="bulkadd">
                        <p>Here you can modify multiple device dependencies. Setting the parent device to "None" will clear the dependency.</p>
                        <br />
                        <div class="form-group">
                            <label for="manavailableparents">Parent Host:</label>
                            <select multiple name="parent_id" class="form-control" id="manavailableparents" style='width: 100%'>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="manalldevices">Child Hosts:</label>
                            <select multiple name="device_ids" class="form-control" id="manalldevices" style='width: 100%'>
                            </select>
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="clearall">
                        <p>Select the parent device to delete its child devices</p>
                        <div class="form-group">
                            <label for="manclearchildren">Parent Host:</label>
                            <select multiple name="parent_id" class="form-control" id="manclearchildren" style='width: 100%'>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success" id="manhostdep-save" data-target="manhostdep-save">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
function changeParents(e, evttype)
{
    e.preventDefault();
    if (evttype == 'select' && e.params.data.id == 0) {
        $('#manavailableparents').val(0);
        $('#manavailableparents').trigger('change');
    }

    var cur_option = $('#manavailableparents').select2('data');
    // So that we'll see all devices.
    var device_id = 0;
    var parent_ids = [];
    // This is needed to remove the None option if it is with another parent id

    for (var i=0;i<cur_option.length;i++) {
        if (cur_option.length > 1 && cur_option[i].id == 0) {
            continue;
        }
        parent_ids.push(cur_option[i].id);
    }

    // Set parents to new value
    $('#manavailableparents').val(parent_ids).trigger('change');

    $.ajax({
        type: 'POST',
        url: 'ajax_form.php',
        data: { type: "get-host-dependencies", "parent_ids": parent_ids, "viewtype": "fromparent" },
        dataType: "json",
        success: function(output) {
            if (output.status == 0) {
                if (output.deps != null && output.deps != '') {
                    var temp_arr2 = [];
                    $.each(output.deps, function (i, elem) {
                        temp_arr2.push(elem.device_id);
                    });
                    $('#manalldevices').val(temp_arr2);
                } else {
                    $('#manalldevices').val(null);
                }
                $('#manalldevices').trigger('change');
            } else {
                toastr.error(output.message);
            }
        },
        error: function() {
            toastr.error('Device dependencies could not be retrieved from the database');
        }
    });
}

$('#manage-dependencies').on('hide.bs.modal', function() {
    $('#manavailableparents').val('0');
    $('#manavailableparents').trigger('change');

    $('#manclearchildren').val('0');
    $('#manclearchildren').trigger('change');

    $('#manalldevices').val(null);
    $('#manalldevices').trigger('change');
});

$('#manage-dependencies').on('show.bs.modal', function() {
    var device_id = 0;
    $.ajax({
        type: 'POST',
        url: 'ajax_form.php',
        data: { type: "get-host-dependencies", "viewtype": 'fromparent', "parent_ids": 0},
        dataType: "json",
        success: function(output) {
            if (output.status == 0) {
                var tempArr = [];
                $.each(output.deps, function (i, elem) {
                    tempArr.push(elem.device_id);
                });
                $('#manalldevices').val(tempArr);
                $('#manalldevices').trigger('change');

                $('#manavailableparents').val(device_id);
                $('#manavailableparents').trigger('change');

                $('#manclearchildren').val(device_id);
                $('#manclearchildren').trigger('change');
            } else {
                toastr.error(output.message);
            }
        },
        error: function() {
            toastr.error('Device dependencies could not be retrieved from the database');
        }
    })
});

$('#manhostdep-save').on("click", function(event) {
    event.preventDefault();
    var device_ids = [];
    var children = [];
    var parent_id = [];
    var parent_host = '';
    var btn_text = $('#manhostdep-save').text();

    if (btn_text == 'Save') {
        // Get selections
        var parents = $('#manavailableparents').select2('data');
        var devices = $('#manalldevices').select2('data');

        for (var i=0;i<parents.length;i++) {
            parent_id.push(parents[i].id);
            parent_host = parent_host + '<a href="device/device='+ parents[i].id +'/">' + parents[i].text + '</a>, ';
        }
        for (var i=0;i<devices.length;i++) {
            device_ids.push(devices[i].id);
            children.push(devices[i].text);
        }
        parent_host = parent_host.slice(0, -2);

        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: { type: "save-host-dependency", device_ids: device_ids, parent_ids: parent_id },
            dataType: "json",
            success: function(output) {
                $("#manage-dependencies").modal('hide');
                $('#hostdeps').bootgrid('reload');
                if (output.status == 0) {
                    toastr.success('Device dependencies saved successfully');
                } else {
                    toastr.error('The device dependency could not be saved.');
                }
            },
            error: function() {
                toastr.error('The device dependency could not be saved.');
                $("#manage-dependencies").modal('hide');
            }
        });
    } else if(btn_text == 'Clear') {
        var parents = $('#manclearchildren').select2('data');
        for (var i=0;i<parents.length;i++) {
            parent_id.push(parents[i].id);
        }
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: { type: "delete-host-dependency", parent_ids: parent_id },
            dataType: "json",
            success: function(output) {
                $("#manage-dependencies").modal('hide');
                $('#hostdeps').bootgrid('reload');
                if (output.status == 0) {
                    toastr.success(output.message);
                } else {
                    toastr.error(output.message);
                }
            },
            error: function() {
                toastr.error('The device dependency could not be deleted.');
                $("#manage-dependencies").modal('hide');
            }
        });
    }
});

$(document).ready(function() {
    $('#manavailableparents').on('select2:select', function(e) {changeParents(e, 'select')});
    $('#manavailableparents').on('select2:unselect', function(e) {changeParents(e, 'unselect')});

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        if ($(e.target).closest('li').index() == 0) {
            $('#manhostdep-save').text('Save');
        } else if ($(e.target).closest('li').index() == 1) {
            $('#manhostdep-save').text('Clear');
        }

    });
});
</script>
