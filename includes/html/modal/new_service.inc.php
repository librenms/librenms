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
    // Build the types list.
    $dir = \LibreNMS\Config::get('nagios_plugins');
    if (file_exists($dir) && is_dir($dir)) {
        $files = scandir($dir);
        $dir .= DIRECTORY_SEPARATOR;
        foreach ($files as $file) {
            if (is_executable($dir . $file) && is_file($dir . $file) && strstr($file, 'check_')) {
                [,$check_name] = explode('_', $file, 2);
                $service_type .= "<option value='$check_name'>$check_name</option>";
            }
        }
    } ?>

<div class="modal fade bs-example-modal-sm" id="create-service" tabindex="-1" role="dialog" aria-labelledby="Create" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h2 class="modal-title" id="Create">Add / Edit Service</h2>
            </div>
            <div class='alert alert-info'>Service will modified for the specified Device.</div>
            <div class='well well-lg'>
                <div class="modal-body">
                    <form method="post" role="form" id="service" class="form-horizontal service-form">
                        <?php echo csrf_field() ?>
                        <input type="hidden" name="service_id" id="service_id" value="">
                        <input type="hidden" name="service_template_id" id="service_template_id" value="">
                        <input type="hidden" name="device_id" id="device_id" value="<?php echo $device['device_id']?>">
                        <div class="form-group">
                            <div class="col-sm-12">
                                <span id="ajax_response">&nbsp;</span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for='service_name' class='col-sm-3 control-label'>Name </label>
                            <div class="col-sm-9">
                                <input type='text' id='service_name' name='service_name' class='form-control input-sm' placeholder=''/>
                            </div>
                            <div class='col-sm-9'>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for='service_type' class='col-sm-3 control-label'>Check Type </label>
                            <div class="col-sm-9">
                                <select id='service_type' name='service_type' class='form-control has-feedback'>
                                    <?php echo $service_type?>
                                </select>
                            </div>
                            <div class='col-sm-9'>
                            </div>
                        </div>
                        <div class='form-group row'>
                            <label for='service_desc' class='col-sm-3 control-label'>Description </label>
                            <div class='col-sm-9'>
                                <textarea id='service_desc' name='service_desc' class='form-control' rows='5'></textarea>
                            </div>
                            <div class='col-sm-9'>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for='service_ip' class='col-sm-3 control-label'>Remote Host </label>
                            <div class="col-sm-9">
                                <input type='text' id='service_ip' name='service_ip' class='form-control has-feedback' placeholder='IP Address or Hostname'/>
                            </div>
                            <div class='col-sm-9'>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for='service_param' class='col-sm-3 control-label'>Parameters </label>
                            <div class="col-sm-9">
                                <input type='text' id='service_param' name='service_param' class='form-control has-feedback' placeholder=''/>
                            </div>
                            <div class='col-sm-9'>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-12 alert alert-info">
                                <label class='control-label text-left input-sm'>Parameters may be required and will be different depending on the service check.</label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for='service_ignore' class='col-sm-3 control-label'>Ignore alert tag </label>
                            <div class="col-sm-9">
                                <input type="hidden" name="service_ignore" id='service_ignore' value="0">
                                <input type='checkbox' id='ignore_box' name='ignore_box' onclick="$('#service_ignore').attr('value', $('#ignore_box').prop('checked')?1:0);">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for='service_disabled' class='col-sm-3 control-label'>Disable polling and alerting </label>
                            <div class="col-sm-9">
                                <input type='hidden' id='service_disabled' name='service_disabled' value="0">
                                <input type='checkbox' id='disabled_box' name='disabled_box' onclick="$('#service_disabled').attr('value', $('#disabled_box').prop('checked')?1:0);">
                            </div>
                        </div>
                        <hr>
                        <div class="form-group row">
                            <center><button class="btn btn-default btn-sm" type="submit" name="service-submit" id="service-submit" value="save">Save Service</button></center>
                        </div>
                        <div class="clearfix"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>

// on-hide
$('#create-service').on('hide.bs.modal', function (event) {
    $('#service_type').val('');
    $("#service_type").prop("disabled", false);
    $('#service_ip').val('');
    $('#service_desc').val('');
    $('#service_param').val('');
    $('#service_ignore').val('');
    $('#ignore_box').val('');
    $('#service_disabled').val('');
    $('#disabled_box').val('');
    $('#service_template_id').val('');
    $('#service_name').val('');
    $('#service_template_name').val('');
    $("#ajax_response").html('');
});

// on-load
$('#create-service').on('show.bs.modal', function (e) {
    var button = $(e.relatedTarget);
    var service_id = button.data('service_id');
    var modal = $(this)
    $('#service_id').val(service_id);
    $.ajax({
        type: "GET",
        url: "<?php echo route('services.show', ['service' => '?']) ?>".replace('?', service_id),
        dataType: "json",
        success: function(service) {
            $('#service_type').val(service.service_type);
            $("#service_type").prop("disabled", true);
            $('#service_ip').val(service.service_ip);
            $('#device_id').val(service.device_id);
            $('#service_desc').val(service.service_desc);
            $('#service_param').val(service.service_param);
            $('#service_ignore').val(service.service_ignore === true ? 1 : 0);
            $('#service_disabled').val(service.service_disabled === true ? 1 : 0);
            $('#ignore_box').prop("checked", service.service_ignore);
            $('#disabled_box').prop("checked", service.service_disabled);
            $('#service_template_id').val(service.service_template_id === 0 ? '' : service.service_template_id);
            $('#service_name').val(service.service_name);
        }
    });

});

// on-submit
$('#service-submit').on("click", function(e) {
    e.preventDefault();
    var service_id = $('#service_id').val();
    $.ajax({
        type: service_id ? 'PUT' : 'POST',
        url: "<?php echo route('services.store') ?>" + (service_id ? '/' + service_id : ''),
        data: $('form.service-form').serializeArray(),
        success: function (result) {
            $('#message').html('<div class="alert alert-info">' + result.message + '</div>');
            $("#create-service").modal('hide');
            setTimeout(function () {
                location.reload();
            }, 1500);
        },
        error: function (result) {
            var message = result.responseJSON.message;
            for (const field in result.responseJSON.errors) {
                message += '<br />' + field + ': ' + result.responseJSON.errors[field];
            }

            $("#ajax_response").html('<div class="alert alert-danger">' + message + '</div>');
        }
    });
});

</script>
    <?php
}
