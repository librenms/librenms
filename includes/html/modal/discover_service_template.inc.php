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

    <div class="modal fade" id="confirm-discovery" tabindex="-1" role="dialog" aria-labelledby="Discover" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h5 class="modal-title" id="Discover">Confirm Discover</h5>
                </div>
                <div class="modal-body">
                    <p>Please confirm that you would like to discover devices and apply this service.</p>
                </div>
                <div class="modal-footer">
                    <form role="form" class="remove_token_form">
                        <?php echo csrf_field() ?>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger danger" id="discovery-service-template"
                                data-target="discovery-service-template">Discover
                        </button>
                        <input type="hidden" name="device_group_id" id="device_group_id" value="">
                        <input type="hidden" name="service_template_id" id="service_template_id" value="">
                        <input type="hidden" name="confirm" id="confirm" value="yes">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $('#confirm-discovery').on('show.bs.modal', function (e) {
            device_group_id = $(e.relatedTarget).data('device_group_id');
            $("#device_group_id").val(device_group_id);
            service_template_id = $(e.relatedTarget).data('service_template_id');
            $("#service_template_id").val(service_template_id);
        });

        $('#discovery-service-template').click('', function (e) {
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
                        $("#confirm-discover").modal('hide');
                    }
                    else {
                        // Nay.
                        $("#message").html('<div class="alert alert-danger">' + result.message + '</div>');
                        $("#confirm-discover").modal('hide');
                    }
                },
                error: function () {
                    $("#message").html('<div class="alert alert-info">An error occurred discovering this service template.</div>');
                    $("#confirm-discover").modal('hide');
                }
            });
        });
    </script>
    <?php
}
?>
