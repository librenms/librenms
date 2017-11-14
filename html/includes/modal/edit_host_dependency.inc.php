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

<div class="modal fade" id="edit-dependency" role="dialog" aria-labelledby="Delete" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h5 class="modal-title" id="Delete">Edit Dependency</h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <p>Please choose a parent host for <strong class="modalhostname"></strong> and click Save</p>
                    </div>
                </div>
                <br />
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <select class="form-control" name="parent_id" id="availableparents">
                                <option value="0">None</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <form role="form" class="remove_token_form">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="hostdep-save" data-target="hostdep-save">Save</button>
                    <input type="hidden" name="row_id" id="edit-row_id" value="">
                    <input type="hidden" name="device_id" id="edit-device_id" value="">
                    <input type="hidden" name="orig_parent_id" id="edit-parent_id" value="">
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$('#edit-dependency').on('show.bs.modal', function() {
    var device_id = $("#edit-device_id").val();

    $.ajax({
        type: 'POST',
        url: 'ajax_form.php',
        data: { type: "get-host-dependencies", "device_id": device_id },
        dataType: "json",
        success: function(output) {
            if (output.status == 0) {
                $.each(output.deps, function (i, elem) {
                    if (elem.device_id == $('#edit-parent_id').val()) {
                        var select_line = "<option value=" + elem.device_id + " selected='selected'>" + elem.hostname + "</option>";
                    } else {
                        var select_line = "<option value=" + elem.device_id + ">" + elem.hostname + "</option>";
                    }
                    $('#availableparents').append(select_line);
                });
            } else {
                toastr.error(output.message);
            }
        },
        error: function() {
            toastr.error('The host dependency could not be fetched.');
            $("#manage-dependencies").modal('hide');
        }
    })
});

$('#hostdep-save').click('', function(event) {
    event.preventDefault();
    var row_id = $("#edit-row_id").val();
    var parent_id = $("#availableparents").find(":selected").val();
    var parent_host = $("#availableparents").find(":selected").text();
    var device_id = $("#edit-device_id").val();
    var device_ids = [];
    device_ids.push(device_id);
    $("#modal_hostname").text();
    $.ajax({
        type: 'POST',
        url: 'ajax_form.php',
        data: { type: "save-host-dependency", device_ids: device_ids, parent_id: parent_id },
        dataType: "json",
        success: function(output) {
            if (output.status == 0) {
                toastr.success(output.message);
                $("#edit-dependency").modal('hide');
                $('#availableparents')
                    .find('option')
                    .remove()
                    .end()
                    .append('<option value="0">None</option>')
                    .val('0');
                $('[data-row-id='+row_id+']').find('.parenthost').text(parent_host);
            } else {
                toastr.error(output.message);
            }
        },
        error: function() {
            toastr.error('The host dependency could not be saved.');
            $("#edit-dependency").modal('hide');
            $('#availableparents')
                .find('option')
                .remove()
                .end()
                .append('<option value="0">None</option>')
                .val('0');
        }
    });
});
</script>
