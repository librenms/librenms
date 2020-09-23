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
    foreach (dbFetchRows('SELECT * FROM `device_groups` ORDER BY `name`') as $device_group) {
        $devicegroupsform .= "<option value='" . $device_group['id'] . "'>" . $device_group['name'] . '</option>';
    }
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

<div class="modal fade bs-example-modal-sm" id="create-service-template" tabindex="-1" role="dialog" aria-labelledby="Create" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h5 class="modal-title" id="Create">Services Template</h5>
            </div>
            <div class="modal-body">
                <form method="post" role="form" id="service-template" class="form-horizontal service-template-form">
                    <?php echo csrf_field() ?>
                    <input type="hidden" name="service_template_id" id="service_template_id" value="">
                    <input type="hidden" name="device_group_id" id="device_group_id" value="<?php echo $device_group['id']?>">
                    <input type="hidden" name="type" id="type" value="create-service-template">
                    <div class="form-service-template">
                        <div class="col-sm-12">
                            <span id="ajax_response">&nbsp;</span>
                        </div>
                    </div>
                    <div class="form-service-template row">
                        <label for='device_group' class='col-sm-3 control-label'>Device Group: </label>
                        <div class="col-sm-9">
                            <select name='device_group' class='form-control input-sm'>
                                $devicegroupsform
                            </select>
                        </div>
                        <div class='col-sm-5'>
                        </div>
                    </div>
                    <div class="form-service-template row">
                        <label for='stype' class='col-sm-3 control-label'>Type: </label>
                        <div class="col-sm-9">
                            <select id='stype' name='stype' placeholder='type' class='form-control has-feedback'>
                                <?php echo $stype?>
                            </select>
                        </div>
                    </div>
                    <div class='form-service-template row'>
                        <label for='desc' class='col-sm-3 control-label'>Description: </label>
                        <div class='col-sm-9'>
                            <textarea id='desc' name='desc' class='form-control'></textarea>
                        </div>
                    </div>
                    <div class="form-service-template row">
                        <label for='ip' class='col-sm-3 control-label'>IP Address: </label>
                        <div class="col-sm-9">
                            <input type='text' id='ip' name='ip' class='form-control has-feedback' placeholder=''/>
                        </div>
                    </div>
                    <div class="form-service-template row">
                        <label for='param' class='col-sm-3 control-label'>Parameters: </label>
                        <div class="col-sm-9">
                           <input type='text' id='param' name='param' class='form-control has-feedback' placeholder=''/>
                        </div>
                    </div>
                    <div class="form-service-template row">
                        <label for='ignore' class='col-sm-3 control-label'>Ignore alert tag: </label>
                        <div class="col-sm-9">
                            <input type='checkbox' id='ignore' name='ignore'>
                        </div>
                    </div>
                    <div class="form-service-template row">
                        <label for='disabled' class='col-sm-3 control-label'>Disable polling and alerting: </label>
                        <div class="col-sm-9">
                            <input type='checkbox' id='disabled' name='disabled'>
                        </div>
                    </div>
                    <div class="form-service-template row">
                        <div class="col-sm-offset-3 col-sm-9">
                            <button class="btn btn-success btn-sm" type="submit" name="service-template-submit" id="service-template-submit" value="save">Save Service Template</button>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>

// on-hide
$('#create-service-template').on('hide.bs.modal', function (event) {
    $('#device_group_id').val('');
    $('#stype').val('');
    $("#stype").prop("disabled", false);
    $('#desc').val('');
    $('#ip').val('');
    $('#param').val('');
    $('#ignore').val('');
    $('#disabled').val('');
});

// on-load
$('#create-service-template').on('show.bs.modal', function (e) {
    var button = $(e.relatedTarget);
    var service_template_id = button.data('service_template_id');
    var modal = $(this)
    $('#service_template_id').val(service_template_id);
    $.ajax({
        type: "POST",
        url: "ajax_form.php",
        data: { type: "parse-service-template", service_template_id: service_template_id },
        dataType: "json",
        success: function(output) {
            $('#device_group_id').val(output['device_group_id']);
            $('#stype').val(output['stype']);
            $("#stype").prop("disabled", true);
            $('#desc').val(output['desc']);
            $('#ip').val(output['ip']);
            $('#param').val(output['param']);
            $('#ignore').val(output['ignore']);
            $('#disabled').val(output['disabled']);
            if ($('#ignore').attr('value') == 1) {
                $('#ignore').prop("checked", true);
            }
            if ($('#disabled').attr('value') == 1) {
                $('#disabled').prop("checked", true);
            }
        }
    });

});

// on-submit
$('#service-template-submit').click('', function(e) {
    e.preventDefault();
    $.ajax({
        type: "POST",
        url: "ajax_form.php",
        data: $('form.service-template-form').serialize(),
        success: function(result){
            if (result.status == 0) {
                // Yay.
                $("#create-service-template").modal('hide');
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
            $("#ajax_response").html('<div class="alert alert-info">An error occurred creating this service template.</div>');
        }
    });
});

</script>
    <?php
}
