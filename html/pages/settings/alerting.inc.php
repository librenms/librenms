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

// Default settings config
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
$tolerance_window_config = get_config_by_name('alert,tolerance_window');

// Mail transport config
$email_transport_config = get_config_by_name('alert,transports,mail');
if (strcasecmp($email_transport_config[0]['config_value'],"true") == 0) {
    $email_transport_checked = 'checked';
} else {
    $email_transport_checked = '';
}
$email_backend_config = get_config_by_name('email_backend');
$email_from_config = get_config_by_name('email_from');
$email_user_config = get_config_by_name('email_user');
$email_sendmail_path_config = get_config_by_name('email_sendmail_path');
$email_smtp_host_config = get_config_by_name('email_smtp_host');
$email_smtp_port_config = get_config_by_name('email_smtp_port');
$email_smtp_timeout_config = get_config_by_name('email_smtp_timeout');
$email_smtp_secure_config = get_config_by_name('email_smtp_secure');
$email_smtp_auth_config = get_config_by_name('email_smtp_auth');
if (strcasecmp($email_smtp_auth_config[0]['config_value'],"true") == 0) {
    $email_smtp_auth_checked = 'checked';
} else {
    $email_smtp_auth_checked = '';
}
$email_smtp_username_config = get_config_by_name('email_smtp_username');
$email_smtp_password_config = get_config_by_name('email_smtp_password');

if (isset($config['base_url'])) {
    $callback = $config['base_url'].'/'.$_SERVER['REQUEST_URI'].'/';
} else {
    $callback = get_url().'/';
}
$callback = urlencode($callback);

echo '
<div class="panel-group" id="accordion">
    <form class="form-horizontal" role="form" action="" method="post">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordion" href="#general_settings_expand">General alert settings</a>
                </h4>
            </div>
            <div id="general_settings_expand" class="panel-collapse collapse">
                <div class="panel-body">
                    <div class="form-group">
                        <label for="admins" class="col-sm-4 control-label">$config[\''.str_replace(",", "']['", $admin_config[0]['config_name']).'\'] = </label>
                        <div data-toggle="tooltip" title="'.$admin_config[0]['config_desc'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                        <div class="col-sm-4">
                            <input id="admins" type="checkbox" name="global-config-check" '.$admin_checked.' data-on-text="Yes" data-off-text="No" data-size="small" data-config_id="'.$admin_config[0]['config_id'].'">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="globals" class="col-sm-4 control-label">$config[\''.str_replace(",", "']['", $read_config[0]['config_name']).'\'] = </label>
                        <div data-toggle="tooltip" title="'.$read_config[0]['config_desc'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                        <div class="col-sm-4">
                            <input id="globals" type="checkbox" name="global-config-check" '.$read_checked.' data-on-text="Yes" data-off-text="No" data-size="small" data-config_id="'.$read_config[0]['config_id'].'">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="default_only" class="col-sm-4 control-label">$config[\''.str_replace(",", "']['", $default_only_config[0]['config_name']).'\'] = </label>
                        <div data-toggle="tooltip" title="'.$default_only_config[0]['config_desc'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                        <div class="col-sm-4">
                            <input id="default_only" type="checkbox" name="global-config-check" '.$default_only_checked.' data-on-text="Yes" data-off-text="No" data-size="small" data-config_id="'.$default_only_config[0]['config_id'].'">
                        </div>
                    </div>
                    <div class="form-group has-feedback">
                        <label for="default_mail" class="col-sm-4 control-label">$config[\''.str_replace(",", "']['", $default_mail_config[0]['config_name']).'\'] = </label>
                        <div data-toggle="tooltip" title="'.$default_mail_config[0]['config_desc'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                        <div class="col-sm-4">
                            <input id="default_mail" class="form-control" type="text" name="global-config-input" value="'.$default_mail_config[0]['config_value'].'" data-config_id="'.$default_mail_config[0]['config_id'].'">
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                    </div>
                    <div class="form-group has-feedback">
                        <label for="tolerance_window" class="col-sm-4 control-label">$config[\''.str_replace(",", "']['", $tolerance_window_config[0]['config_name']).'\'] = </label>
                        <div data-toggle="tooltip" title="'.$tolerance_window_config[0]['config_desc'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                        <div class="col-sm-4">
                            <input id="tolerance_window" class="form-control" type="text" name="global-config-input" value="'.$tolerance_window_config[0]['config_value'].'" data-config_id="'.$tolerance_window_config[0]['config_id'].'">
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordion" href="#email_transport_expand">Email transport</a>
                </h4>
            </div>
            <div id="email_transport_expand" class="panel-collapse collapse">
                <div class="panel-body">
                    <div class="form-group">
                        <label for="default_only" class="col-sm-4 control-label">$config[\''.str_replace(",", "']['", $email_transport_config[0]['config_name']).'\'] = </label>
                        <div data-toggle="tooltip" title="'.$email_transport_config[0]['config_desc'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                        <div class="col-sm-4">
                            <input id="default_only" type="checkbox" name="global-config-check" '.$email_transport_checked.' data-on-text="Yes" data-off-text="No" data-size="small" data-config_id="'.$email_transport_config[0]['config_id'].'">
                        </div>
                    </div>
                    <div class="form-group has-feedback">
                        <label for="email_backend" class="col-sm-4 control-label">$config[\''.str_replace(",", "']['", $email_backend_config[0]['config_name']).'\'] = </label>
                        <div data-toggle="tooltip" title="'.$email_backend_config[0]['config_desc'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                        <div class="col-sm-4">
                            <input id="email_backend" class="form-control" type="text" name="global-config-input" value="'.$email_backend_config[0]['config_value'].'" data-config_id="'.$email_backend_config[0]['config_id'].'">
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                    </div>
                    <div class="form-group has-feedback">
                        <label for="email_from" class="col-sm-4 control-label">$config[\''.str_replace(",", "']['", $email_from_config[0]['config_name']).'\'] = </label>
                        <div data-toggle="tooltip" title="'.$email_from_config[0]['config_desc'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                        <div class="col-sm-4">
                            <input id="email_from" class="form-control" type="text" name="global-config-input" value="'.$email_from_config[0]['config_value'].'" data-config_id="'.$email_from_config[0]['config_id'].'">
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                    </div>
                    <div class="form-group has-feedback">
                        <label for="email_user" class="col-sm-4 control-label">$config[\''.str_replace(",", "']['", $email_user_config[0]['config_name']).'\'] = </label>
                        <div data-toggle="tooltip" title="'.$email_user_config[0]['config_desc'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                        <div class="col-sm-4">
                            <input id="email_user" class="form-control" type="text" name="global-config-input" value="'.$email_user_config[0]['config_value'].'" data-config_id="'.$email_user_config[0]['config_id'].'">
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                    </div>
                    <div class="form-group has-feedback">
                        <label for="email_sendmail_path" class="col-sm-4 control-label">$config[\''.str_replace(",", "']['", $email_sendmail_path_config[0]['config_name']).'\'] = </label>
                        <div data-toggle="tooltip" title="'.$email_sendmail_path_config[0]['config_desc'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                        <div class="col-sm-4">
                            <input id="email_sendmail_path" class="form-control" type="text" name="global-config-input" value="'.$email_sendmail_path_config[0]['config_value'].'" data-config_id="'.$email_sendmail_path_config[0]['config_id'].'">
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                    </div>
                    <div class="form-group has-feedback">
                        <label for="email_smtp_host" class="col-sm-4 control-label">$config[\''.str_replace(",", "']['", $email_smtp_host_config[0]['config_name']).'\'] = </label>
                        <div data-toggle="tooltip" title="'.$email_smtp_host_config[0]['config_desc'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                        <div class="col-sm-4">
                            <input id="email_smtp_host" class="form-control" type="text" name="global-config-input" value="'.$email_smtp_host_config[0]['config_value'].'" data-config_id="'.$email_smtp_host_config[0]['config_id'].'">
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                    </div>
                    <div class="form-group has-feedback">
                        <label for="email_smtp_port" class="col-sm-4 control-label">$config[\''.str_replace(",", "']['", $email_smtp_port_config[0]['config_name']).'\'] = </label>
                        <div data-toggle="tooltip" title="'.$email_smtp_port_config[0]['config_desc'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                        <div class="col-sm-4">
                            <input id="email_smtp_port" class="form-control" type="text" name="global-config-input" value="'.$email_smtp_port_config[0]['config_value'].'" data-config_id="'.$email_smtp_port_config[0]['config_id'].'">
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                    </div>
                    <div class="form-group has-feedback">
                        <label for="email_smtp_timeout" class="col-sm-4 control-label">$config[\''.str_replace(",", "']['", $email_smtp_timeout_config[0]['config_name']).'\'] = </label>
                        <div data-toggle="tooltip" title="'.$email_smtp_timeout_config[0]['config_desc'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                        <div class="col-sm-4">
                            <input id="email_smtp_timeout" class="form-control" type="text" name="global-config-input" value="'.$email_smtp_timeout_config[0]['config_value'].'" data-config_id="'.$email_smtp_timeout_config[0]['config_id'].'">
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                    </div>
                    <div class="form-group has-feedback">
                        <label for="email_smtp_secure" class="col-sm-4 control-label">$config[\''.str_replace(",", "']['", $email_smtp_secure_config[0]['config_name']).'\'] = </label>
                        <div data-toggle="tooltip" title="'.$email_smtp_secure_config[0]['config_desc'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                        <div class="col-sm-4">
                            <input id="email_smtp_secure" class="form-control" type="text" name="global-config-input" value="'.$email_smtp_secure_config[0]['config_value'].'" data-config_id="'.$email_smtp_secure_config[0]['config_id'].'">
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email_smtp_auth" class="col-sm-4 control-label">$config[\''.str_replace(",", "']['", $email_smtp_auth_config[0]['config_name']).'\'] = </label>
                        <div data-toggle="tooltip" title="'.$email_smtp_auth_config[0]['config_desc'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                        <div class="col-sm-4">
                            <input id="email_smtp_auth" type="checkbox" name="global-config-check" '.$email_smtp_auth_checked.' data-on-text="Yes" data-off-text="No" data-size="small" data-config_id="'.$email_smtp_auth_config[0]['config_id'].'">
                        </div>
                    </div>
                    <div class="form-group has-feedback">
                        <label for="email_smtp_username" class="col-sm-4 control-label">$config[\''.str_replace(",", "']['", $email_smtp_username_config[0]['config_name']).'\'] = </label>
                        <div data-toggle="tooltip" title="'.$email_smtp_username_config[0]['config_desc'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                        <div class="col-sm-4">
                            <input id="email_smtp_username" class="form-control" type="text" name="global-config-input" value="'.$email_smtp_username_config[0]['config_value'].'" data-config_id="'.$email_smtp_username_config[0]['config_id'].'">
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                    </div>
                    <div class="form-group has-feedback">
                        <label for="email_smtp_password" class="col-sm-4 control-label">$config[\''.str_replace(",", "']['", $email_smtp_password_config[0]['config_name']).'\'] = </label>
                        <div data-toggle="tooltip" title="'.$email_smtp_password_config[0]['config_desc'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                        <div class="col-sm-4">
                            <input id="email_smtp_password" class="form-control" type="text" name="global-config-input" value="'.$email_smtp_password_config[0]['config_value'].'" data-config_id="'.$email_smtp_password_config[0]['config_id'].'">
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordion" href="#api_transport_expand">API transport</a>
                </h4>
            </div>
            <div id="api_transport_expand" class="panel-collapse collapse">
                <div class="panel-body">
                    <div class="form-group">
                        <div class="col-sm-8">
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordion" href="#pagerduty_transport_expand">Pagerduty transport</a>
                </h4>
            </div>
            <div id="pagerduty_transport_expand" class="panel-collapse collapse">
                <div class="panel-body">
                    <div class="form-group">
                        <div class="col-sm-8">
                            <a href="https://connect.pagerduty.com/connect?vendor=2fc7c9f3c8030e74aae6&callback='.$callback.'"><img src="images/pd_connect_button.png" width="202" height="36" alt="Connect to PagerDuty"></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
';

?>

<script>

    $(".toolTip").tooltip();

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
                } else {
                    $("#message").html('<div class="alert alert-info">' + data.message + '</div>');
                }
            },
            error: function () {
                $("#message").html('<div class="alert alert-info">An error occurred.</div>');
            }
        });
    });
    $( 'input[name="global-config-input"]').blur(function(event) {
        event.preventDefault();
        var $this = $(this);
        var config_id = $this.data("config_id");
        var config_value = $this.val();
        $.ajax({
            type: 'POST',
            url: '/ajax_form.php',
            data: {type: "update-config-item", config_id: config_id, config_value: config_value},
            dataType: "json",
            success: function (data) {
                if (data.status == 'ok') {
                    $this.closest('.form-group').addClass('has-success');
                    $this.next().addClass('glyphicon-ok');
                    setTimeout(function(){
                        $this.closest('.form-group').removeClass('has-success');
                        $this.next().removeClass('glyphicon-ok');
                    }, 2000);
                } else {
                    $(this).closest('.form-group').addClass('has-error');
                    $this.next().addClass('glyphicon-remove');
                    setTimeout(function(){
                        $this.closest('.form-group').removeClass('has-error');
                        $this.next().removeClass('glyphicon-remove');
                    }, 2000);
                }
            },
            error: function () {
                $("#message").html('<div class="alert alert-info">An error occurred.</div>');
            }
        });
    });
</script>
