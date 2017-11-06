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

<div class="modal fade" id="confirm-delete" role="dialog" aria-labelledby="Delete" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h5 class="modal-title" id="Delete">Confirm Delete</h5>
            </div>
            <div class="modal-body">
                <p>Clicking Delete will remove host dependency from <strong class="modalhostname"></strong></p>
            </div>
            <div class="modal-footer">
                <form role="form" class="remove_token_form">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger danger" id="hostdep-removal" data-target="hostdep-removal">Delete</button>
                    <input type="hidden" name="row_id" id="delete-row_id" value="">
                    <input type="hidden" name="device_id" id="delete-device_id" value="">
                    <input type="hidden" name="parent_id" id="delete-parent_id" value="">
                    <input type="hidden" name="confirm" id="confirm" value="yes">
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$('#hostdep-removal').click('', function(event) {
    event.preventDefault();
    var parent_id = $("#delete-parent_id").val();
    var device_id = $("#delete-device_id").val();
    var row_id = $("#delete-row_id").val();
    $("#modal_hostname").text();
    $.ajax({
        type: 'POST',
        url: 'ajax_form.php',
        data: { type: "delete-host-dependency", device_id: device_id },
        dataType: "html",
        success: function(msg) {
            $("#message").html('<div class="alert alert-info"><button type="" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+msg+'</div>');
            $("#confirm-delete").modal('hide');
            // Clear the host association from html
            $('[data-row-id='+row_id+']').find('.parenthost').text('None');
        },
        error: function() {
            $("#message").html('<div class="alert alert-info">The host dependency could not be deleted.</div>');
            $("#confirm-delete").modal('hide');
        }
    });
});
</script>
