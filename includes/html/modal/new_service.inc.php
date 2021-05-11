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
                $stype .= "<option value='$check_name'>$check_name</option>";
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
                        <input type="hidden" name="type" id="type" value="create-service">
                        <div class="form-group">
                            <div class="col-sm-12">
                                <span id="ajax_response">&nbsp;</span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for='name' class='col-sm-3 control-label'>Name: </label>
                            <div class="col-sm-9">
                                <input type='text' id='name' name='name' class='form-control input-sm' placeholder=''/>
                            </div>
                            <div class='col-sm-9'>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for='stype' class='col-sm-3 control-label'>Check Type: </label>
                            <div class="col-sm-9">
                                <select id='stype' name='stype' placeholder='type' class='form-control has-feedback'>
                                    <?php echo $stype?>
                                </select>
                            </div>
                            <div class='col-sm-9'>
                            </div>
                        </div>
                        <div class='form-group row'>
                            <label for='desc' class='col-sm-3 control-label'>Description: </label>
                            <div class='col-sm-9'>
                                <textarea id='desc' name='desc' class='form-control' rows='5'></textarea>
                            </div>
                            <div class='col-sm-9'>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for='ip' class='col-sm-3 control-label'>Remote Host: </label>
                            <div class="col-sm-9">
                                <input type='text' id='ip' name='ip' class='form-control has-feedback' placeholder='IP Address or Hostname'/>
                            </div>
                            <div class='col-sm-9'>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for='param' class='col-sm-3 control-label'>Parameters: </label>
                            <div class="col-sm-9">
                                <input type='text' id='param' name='param' class='form-control has-feedback' placeholder=''/>
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
                            <label for='ignore' class='col-sm-3 control-label'>Ignore alert tag: </label>
                            <div class="col-sm-9">
                                <input type="hidden" name="ignore" id='ignore' value="0">
                                <input type='checkbox' id='ignore_box' name='ignore_box' onclick="$('#ignore').attr('value', $('#ignore_box').prop('checked')?1:0);">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for='disabled' class='col-sm-3 control-label'>Disable polling and alerting: </label>
                            <div class="col-sm-9">
                                <input type='hidden' id='disabled' name='disabled' value="0">
                                <input type='checkbox' id='disabled_box' name='disabled_box' onclick="$('#disabled').attr('value', $('#disabled_box').prop('checked')?1:0);">
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
    $('#stype').val('');
    $("#stype").prop("disabled", false);
    $('#ip').val('');
    $('#desc').val('');
    $('#param').val('');
    $('#ignore').val('');
    $('#ignore_box').val('');
    $('#disabled').val('');
    $('#disabled_box').val('');
    $('#service_template_id').val('');
    $('#name').val('');
    $('#service_template_name').val('');
});

// on-load
$('#create-service').on('show.bs.modal', function (e) {
    var button = $(e.relatedTarget);
    var service_id = button.data('service_id');
    var modal = $(this)
    $('#service_id').val(service_id);
    $.ajax({
        type: "POST",
        url: "ajax_form.php",
        data: { type: "parse-service", service_id: service_id },
        dataType: "json",
        success: function(output) {
            $('#stype').val(output['stype']);
            $("#stype").prop("disabled", true);
            $('#ip').val(output['ip']);
            $('#desc').val(output['desc']);
            $('#param').val(output['param']);
            $('#ignore').val(output['ignore']);
            $('#disabled').val(output['disabled']);
            $('#ignore_box').val(output['ignore']);
            $('#disabled_box').val(output['disabled']);
            if ($('#ignore').attr('value') == 1) {
                $('#ignore_box').prop("checked", true);
            }
            if ($('#disabled').attr('value') == 1) {
                $('#disabled_box').prop("checked", true);
            }
            $('#service_template_id').val(output['service_template_id']);
            $('#name').val(output['name']);
        }
    });

});

// on-submit
$('#service-submit').on("click", function(e) {
    e.preventDefault();
    $.ajax({
        type: "POST",
        url: "ajax_form.php",
        data: $('form.service-form').serialize(),
        success: function(result){
            if (result.status == 0) {
                // Yay.
                $("#create-service").modal('hide');
                $('#message').html('<div class="alert alert-info">' + result.message + '</div>');
                setTimeout(function() {
                    location.reload(1);
                }, 1500);
            }
            else {
                // Nay.
                $("#ajax_response").html('<div class="alert alert-danger">'+result.message+'</div>');
            }
        },
        error: function(){
            $("#ajax_response").html('<div class="alert alert-info">An error occurred creating this service.</div>');
        }
    });
});

</script>
    <?php
}
