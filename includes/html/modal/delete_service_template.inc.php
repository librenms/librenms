<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2016 Aaron Daniels <aaron@daniels.id.au>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if (Auth::user()->hasGlobalAdmin()) {
    ?>

    <div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="Delete" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h2 class="modal-title" id="Delete">Confirm Delete</h2>
                </div>
                <div class='alert alert-info'>Service Template will be deleted for the specified Device Group.</div>
                <div class='well well-lg'>
                    <div class="modal-body">
                        <p>Please confirm that you would like to delete this Service Template.</p>
                        <div class="form-group row">
                            <div class="col-sm-12 alert alert-info">
                                <label class='control-label text-left input-sm'>Please Tick this box to remove Services from Devices, that have been created with this Template.</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="service_delete" class="col-sm-3 col-md-3 control-label" title="Delete Services">Delete Services:</label>
                            <div class="col-sm-9 col-md-9">
                                <input type='checkbox' name='service_delete' id='service_delete'>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="modal-footer">
                        <form role="form" class="remove_token_form">
                            <?php echo csrf_field() ?>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger danger" id="service-template-removal"
                                    data-target="service-template-removal">Delete
                            </button>
                            <input type="hidden" name="service_template_id" id="service_template_id" value="">
                            <input type="hidden" name="confirm" id="confirm" value="yes">
                        </form>
                    </div>
                </div>    
            </div>
        </div>
    </div>

    <script>
        $('#confirm-delete').on('show.bs.modal', function (e) {
            service_template_id = $(e.relatedTarget).data('service_template_id');
            $("#service_template_id").val(service_template_id);
        });

        $('#service-template-removal').click('', function (e) {
            e.preventDefault();
            var service_template_id = $("#service_template_id").val();
            var service_delete = $("#service_delete").val();
            $.ajax({
                type: 'POST',
                url: 'ajax_form.php',
                data: {type: "delete-service-template", service_template_id: service_template_id, service_delete: service_delete},
                success: function (result) {
                    if (result.status == 0) {
                        // Yay.
                        $('#message').html('<div class="alert alert-info">' + result.message + '</div>');
                        $("#row_" + service_template_id).remove();
                        $("#" + service_template_id).remove();
                        $("#confirm-delete").modal('hide');
                    }
                    else {
                        // Nay.
                        $("#message").html('<div class="alert alert-danger">' + result.message + '</div>');
                        $("#confirm-delete").modal('hide');
                    }
                },
                error: function () {
                    $("#message").html('<div class="alert alert-info">An error occurred deleting this service.</div>');
                    $("#confirm-delete").modal('hide');
                }
            });
        });
    </script>
    <?php
}
?>
