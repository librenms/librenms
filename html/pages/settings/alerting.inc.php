<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if (isset($_GET['error'])) {
    print_error('We had issues connecting to your Pager Duty account, please try again');
}

if (isset($_GET['account']) && isset($_GET['service_key']) && isset($_GET['service_name'])) {
    set_config_name('alert,transports,pagerduty',$_GET['service_key']);
    set_config_name('alert,pagerduty,account',$_GET['account']);
    set_config_name('alert,pagerduty,service',$_GET['service_name']);
}

$admin_config = get_config_by_name('alert,admins');
if (strcasecmp($admin_config[0]['config_value'],"true") == 0) {
    $admin_checked = 'checked';
} else {
    $admin_checked = '';
}
$read_config = get_config_by_name('alert,globals');
if (strcasecmp($read_config[0]['config_value'],"true") == 0) {
    $read_checked = 'checked';
} else {
    $read_checked = '';
}
$default_only_config = get_config_by_name('alert,default_only');
if (strcasecmp($default_only_config[0]['config_value'],"true") == 0) {
    $default_only_checked = 'checked';
} else {
    $default_only_checked = '';
}
$default_mail_config = get_config_by_name('alert,default_mail');

if (isset($config['base_url'])) {
    $callback = $config['base_url'].'/'.$_SERVER['REQUEST_URI'].'/';
} else {
    $callback = get_url().'/';
}
$callback = urlencode($callback);

echo '
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <form class="form-horizontal">
                <div class="form-group">
                    <label for="admins" class="col-sm-4 control-label">'.$admin_config[0]['config_desc'].': </label>
                    <div class="col-sm-8">
                        <input id="admins" type="checkbox" name="global-config-check" '.$admin_checked.' data-on-text="Yes" data-off-text="No" data-size="small" data-config_id="'.$admin_config[0]['config_id'].'">
                    </div>
                </div>
                <div class="form-group">
                    <label for="globals" class="col-sm-4 control-label">'.$read_config[0]['config_desc'].': </label>
                    <div class="col-sm-8">
                        <input id="globals" type="checkbox" name="global-config-check" '.$read_checked.' data-on-text="Yes" data-off-text="No" data-size="small" data-config_id="'.$read_config[0]['config_id'].'">
                    </div>
                </div>
                <div class="form-group">
                    <label for="default_only" class="col-sm-4 control-label">'.$default_only_config[0]['config_desc'].': </label>
                    <div class="col-sm-8">
                        <input id="default_only" type="checkbox" name="global-config-check" '.$default_only_checked.' data-on-text="Yes" data-off-text="No" data-size="small" data-config_id="'.$default_only_config[0]['config_id'].'">
                    </div>
                </div>
                <div class="form-group">
                    <label for="default_mail" class="col-sm-4 control-label">'.$default_mail_config[0]['config_desc'].': </label>
                    <div class="col-sm-8">
                        <input id="default_mail" type="text" name="global-config-input" value"'.$default_mail_config[0]['config_value'].'" data-config_id="'.$default_mail_config[0]['config_id'].'">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-8">
                        <a href="https://connect.pagerduty.com/connect?vendor=2fc7c9f3c8030e74aae6&callback='.$callback.'"><img src="images/pd_connect_button.png" width="202" height="36" alt="Connect to PagerDuty"></a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
';

?>

<script>
    $("[name='global-config-check']").bootstrapSwitch('offColor','danger');
    $('input[name="global-config-check"]').on('switchChange.bootstrapSwitch',  function(event, state) {
        event.preventDefault();
        var config_id = $(this).data("config_id");
        $.ajax({
            type: 'POST',
            url: '/ajax_form.php',
            data: {type: "update-config-item", config_id: config_id, config_value: state},
            dataType: "json",
            success: function (data) {
                if (data.status == 'ok') {
                    $("#message").html('<div class="alert alert-info">' + data.message + '</div>');
                } else {
                    $("#message").html('<div class="alert alert-info">' + data.message + '</div>');
                }
            },
            error: function () {
                $("#message").html('<div class="alert alert-info">An error occurred.</div>');
            }
        });
    });
</script>
