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
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h5 class="modal-title" id="Delete">Edit Dependency</h5>
            </div>
            <div class="modal-body">
                <p>Please choose a parent host for <strong class="modalhostname"></strong> and click Save</p>
                <p>
                    <select name="parent_id" id="availableparents">
                        <option value="0">None</option>
                    </select>
                </p>
            </div>
            <div class="modal-footer">
                <form role="form" class="remove_token_form">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info" id="hostdep-save" data-target="hostdep-save">Save</button>
                    <input type="hidden" name="device_id" id="edit-device_id" value="">
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
            selected_items = [];
            $.each( output.rule_id, function (i, elem) {
                elem = pareInt(elem);
                selected_items.push(elem);
            });
            $('#availableparents').val(selected_items);
        }
    })
});

$('#hostdep-save').click('', function(event) {
    event.preventDefault();
    var parent_id = $("#parent_id").val();
    var device_id = $("#device_id").val();
    $("#modal_hostname").text();
    $.ajax({
        type: 'POST',
        url: 'ajax_form.php',
        data: { type: "save-host-dependency", device_id: device_id, parent_id: parent_id },
        dataType: "html",
        success: function(msg) {
            /* FIXME: need to enable refresh button
            if(msg.indexOf("ERROR:") <= -1) {
                $("#row_"+map_id).remove();
            }
            */
            $("#message").html('<div class="alert alert-info">'+msg+'</div>');
            $("#confirm-delete").modal('hide');
        },
        error: function() {
            $("#message").html('<div class="alert alert-info">The host dependency could not be saved.</div>');
            $("#edit-dependency").modal('hide');
        }
    });
});
</script>
