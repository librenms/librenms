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
                        <p>Please choose a parent device for <strong class="modalhostname"></strong> and click Save</p>
                    </div>
                </div>
                <br />
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <select multiple class="form-control" name="parent_id" id="availableparents" style="width: 100%">
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <form role="form" class="remove_token_form">
                    <?php echo csrf_field() ?>
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
                var tempArr = [];
                $.each(output.deps, function (i, elem) {
                    tempArr.push(elem.device_id);
                });
                $('#availableparents').val(tempArr);
                $('#availableparents').trigger('change');
            } else {
                toastr.error(output.message);
            }
        },
        error: function() {
            toastr.error('The device dependency could not be fetched.');
            $("#manage-dependencies").modal('hide');
        }
    })
});

$('#hostdep-save').on("click", function(event) {
    event.preventDefault();
    var row_id = $("#edit-row_id").val();
    var device_id = $("#edit-device_id").val();
    var device_ids = [];
    var parent_ids = [];
    var parent_hosts = [];
    device_ids.push(device_id);
    $("#availableparents option:selected").each( function() {
        if ($(this).length) {
            parent_ids.push($(this).val());
            parent_hosts.push($(this).text());
        }
    });

    $("#modal_hostname").text();
    $.ajax({
        type: 'POST',
        url: 'ajax_form.php',
        data: { type: "save-host-dependency", device_ids: device_ids, parent_ids: parent_ids },
        dataType: "json",
        success: function(output) {
            if (output.status == 0) {
                toastr.success(output.message);
                $("#edit-dependency").modal('hide');
                $('#hostdeps').bootgrid('reload');
            } else {
                toastr.error(output.message);
            }
        },
        error: function() {
            toastr.error('The device dependency could not be saved.');
            $("#edit-dependency").modal('hide');
            $('#availableparents').val(null);
            $('#availableparents').trigger('change');
        }
    });
});

$('#edit-dependency').on('hide.bs.modal', function() {
    $('#availableparents').val(null);
    $('#availableparents').trigger('change');

});

$('#availableparents').on('select2:select', function(e) {
    if (e.params.data.id == 0) {
        $('#availableparents').val(0);
        $('#availableparents').trigger('change');
    }
});
</script>
