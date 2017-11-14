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

if (is_admin() === false) {
    die('ERROR: You need to be admin');
}

?>

<div class="modal fade" id="manage-dependencies" role="dialog" aria-labelledby="mandeps" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h5 class="modal-title" id="mandeps">Host Dependency for multiple hosts</h5>
            </div>
            <div class="modal-body">
                <div id="mandeps-msg"></div>
                <p>Here you can modify multiple hosts dependencies. Setting the parent host to "None" will clear the dependency.</p>
                <br />
                <div class="form-group">
                    <label for="manavailableparents">Parent Host:</label>
                    <select name="parent_id" class="form-control" id="manavailableparents">
                        <option value="0">None</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="manalldevices">Child Hosts:</label>
                    <select multiple name="device_ids" class="form-control" id="manalldevices">
                    </select>
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
$('#manavailableparents').on('change', '', function(e) {
    e.preventDefault();
    var cur_option = $("option:selected", this);
    var parent_id = this.value;
    // So that we'll see all devices. 
    var device_id = 0;
    $("#manalldevices option").attr("selected", false);
    var dev_array = [];

    $.ajax({
        type: 'POST',
        url: 'ajax_form.php',
        data: { type: "get-host-dependencies", "parent_id": parent_id, "viewtype": "fromparent" },
        dataType: "json",
        success: function(output) {
            if (output.status == 0) {
                $.each(output, function (i, elem) {
                    $('#manalldevices option[value="'+ elem.device_id + '"').prop("selected", true);
                });
            } else {
                toastr.error(output.message);
            }
        },
        error: function() {
            toastr.error('Host dependencies could not be retrieved from the database');
        }
    });
});

$('#manage-dependencies').on('hide.bs.modal', function() {
    $('#manavailableparents')
        .find('option')
        .remove()
        .end()
        .append('<option value="0">None</option>')
        .val('0');

    $('#manalldevices').find('option').remove();
});

$('#manage-dependencies').on('show.bs.modal', function() {
    // So that we'll see all devices. 
    var device_id = 0;
    $('#message').empty();

    $.ajax({
        type: 'POST',
        url: 'ajax_form.php',
        data: { type: "get-host-dependencies", "device_id": device_id },
        dataType: "json",
        success: function(output) {
            if (output.status == 0) {
                $.each(output, function (i, elem) {
                    var select_line = "<option value=" + elem.device_id + ">" + elem.hostname + "</option>";
                    var select_line_selected = "<option value=" + elem.device_id + " selected='selected'>" + elem.hostname + "</option>";

                    $('#manavailableparents').append(select_line);
                    if (elem.parent_id == 0 || elem.parent_id == null) {
                        $('#manalldevices').append(select_line_selected);
                    } else {
                        $('#manalldevices').append(select_line);
                    }
                });
            } else {
                toastr.error(output.message);
            }
        },
        error: function() {
            toastr.error('Host dependencies could not be retrieved from the database');
        }
    })
});

$('#manhostdep-save').click('', function(event) {
    event.preventDefault();
    var device_ids = [];
    var children = [];
    var parent_id = $("#manavailableparents").find(":selected").val();
    var parent_host = $("#manavailableparents").find(":selected").text();
    $("#manalldevices option:selected").each( function() {
        if ($(this).length) {
            device_ids.push($(this).val());
            children.push($(this).text());
        }
    });

    $.ajax({
        type: 'POST',
        url: 'ajax_form.php',
        data: { type: "save-host-dependency", device_ids: device_ids, parent_id: parent_id },
        dataType: "json",
        success: function(msg) {
            $("#manage-dependencies").modal('hide');
            if (output.status == 0) {
                var arrayLength = device_ids.length;
                for (var i = 0; i < arrayLength; i++) {
                    $('#hostdeps').find('td.childhost').each(function() { 
                        if ($(this).text() == children[i]) {
                            $(this).next().text(parent_host);

                        }
                    });
                }
            } else {
                toastr.error('The host dependency could not be saved.');
            }
        },
        error: function() {
            toastr.error('The host dependency could not be saved.');
            $("#manage-dependencies").modal('hide');
        }
    });
});
</script>
