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

    <div class="modal fade" id="discover-service-template" tabindex="-1" role="dialog" aria-labelledby="Discover" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h2 class="modal-title" id="Discover">Confirm Apply Service Template</h2>
                </div>
                <div class='alert alert-info'>Service Template will be Applied.</div>
                <div class='well well-lg'>
                    <div class="modal-body">
                        <p>Please confirm that you would like to discover Devices and apply this Service Template.</p>
                    </div>
                    <hr>
                    <center><button type="submit" class="btn btn-danger danger" id="confirm-discover-service-template"
                        data-target="confirm-discover-service-template">Apply
                    </button></center>
                    <input type="hidden" name="device_group_id" id="device_group_id" value="">
                    <input type="hidden" name="service_template_id" id="service_template_id" value="">
                    <input type="hidden" name="confirm" id="confirm" value="yes">
                </div>
            </div>
        </div>
    </div>

    <script>
        $('#discover-service-template').on('show.bs.modal', function (e) {
            device_group_id = $(e.relatedTarget).data('device_group_id');
            $("#device_group_id").val(device_group_id);
            service_template_id = $(e.relatedTarget).data('service_template_id');
            $("#service_template_id").val(service_template_id);
        });

        $('#confirm-discover-service-template').click('', function (e) {
            e.preventDefault();
            var device_group_id = $("#device_group_id").val();
            var service_template_id = $("#service_template_id").val();
            $.ajax({
                type: 'POST',
                url: 'ajax_form.php',
                data: {type: "discover-service-template", device_group_id: device_group_id, service_template_id: service_template_id},
                success: function (result) {
                    if (result.status == 0) {
                        // Yay.
                        $('#message').html('<div class="alert alert-info">' + result.message + '</div>');
                        $("#discover-service-template").modal('hide');
                    }
                    else {
                        // Nay.
                        $("#message").html('<div class="alert alert-danger">' + result.message + '</div>');
                        $("#discover-service-template").modal('hide');
                    }
                },
                error: function () {
                    $("#message").html('<div class="alert alert-info">No Services were updated when Applying this Service Template.</div>');
                    $("#discover-service-template").modal('hide');
                }
            });
        });
    </script>
    <?php
}
?>
