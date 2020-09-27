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

    <div class="modal fade" id="remove-service-template" tabindex="-1" role="dialog" aria-labelledby="Remove" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h2 class="modal-title" id="Remove">Confirm Remove Services</h2>
                </div>
                <div class='alert alert-info'>Service Template will be Removed.</div>
                <div class='well well-lg'>
                    <div class="modal-body">
                        <p>Please confirm that you would like to remove all Services created by this Service Template.</p>
                    </div>
                    <hr>
                    <center><button type="submit" class="btn btn-danger danger" id="confirm-remove-service-template"
                        data-target="confirm-remove-service-template">Remove
                    </button></center>
                    <input type="hidden" name="service_template_id" id="service_template_id" value="">
                    <input type="hidden" name="confirm" id="confirm" value="yes">
                </div>
            </div>
        </div>
    </div>

    <script>
        $('#remove-service-template').on('show.bs.modal', function (e) {
            service_template_id = $(e.relatedTarget).data('service_template_id');
            $("#service_template_id").val(service_template_id);
        });

        $('#confirm-remove-service-template').click('', function (e) {
            e.preventDefault();
            var device_group_id = $("#device_group_id").val();
            var service_template_id = $("#service_template_id").val();
            $.ajax({
                type: 'POST',
                url: 'ajax_form.php',
                data: {type: "remove-service-template", service_template_id: service_template_id},
                success: function (result) {
                    if (result.status == 0) {
                        // Yay.
                        $('#message').html('<div class="alert alert-info">' + result.message + '</div>');
                        $("#remove-service-template").modal('hide');
                    }
                    else {
                        // Nay.
                        $("#message").html('<div class="alert alert-danger">' + result.message + '</div>');
                        $("#remove-service-template").modal('hide');
                    }
                },
                error: function () {
                    $("#message").html('<div class="alert alert-info">No Services were removed for this Service Template.</div>');
                    $("#remove-service-template").modal('hide');
                }
            });
        });
    </script>
    <?php
}
?>
