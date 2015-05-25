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
    set_config_name('alert.transports.pagerduty',$_GET['service_key']);
    set_config_name('alert.pagerduty.account',$_GET['account']);
    set_config_name('alert.pagerduty.service',$_GET['service_name']);
}

$config_groups = get_config_by_group('alerting');

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
                        <label for="admins" class="col-sm-4 control-label">Issue alerts to admins </label>
                        <div data-toggle="tooltip" title="'.$config_groups['alert.admins']['config_descr'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                        <div class="col-sm-4">
                            <input id="admins" type="checkbox" name="global-config-check" '.$config_groups['alert.admins']['config_checked'].' data-on-text="Yes" data-off-text="No" data-size="small" data-config_id="'.$config_groups['alert.admins']['config_id'].'">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="globals" class="col-sm-4 control-label">Issue alerts to read only users </label>
                        <div data-toggle="tooltip" title="'.$config_groups['alert.alert_globals']['config_descr'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                        <div class="col-sm-4">
                            <input id="globals" type="checkbox" name="global-config-check" '.$config_groups['alert.alert_globals']['config_checked'].' data-on-text="Yes" data-off-text="No" data-size="small" data-config_id="'.$config_groups['alert.alert_globals']['config_id'].'">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="default_only" class="col-sm-4 control-label">Send alerts to default contact only </label>
                        <div data-toggle="tooltip" title="'.$config_groups['alert.default_only']['config_descr'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                        <div class="col-sm-4">
                            <input id="default_only" type="checkbox" name="global-config-check" '.$config_groups['alert.default_only']['config_checked'].' data-on-text="Yes" data-off-text="No" data-size="small" data-config_id="'.$config_groups['alert.default_only']['config_id'].'">
                        </div>
                    </div>
                    <div class="form-group has-feedback">
                        <label for="default_mail" class="col-sm-4 control-label">Default contact </label>
                        <div data-toggle="tooltip" title="'.$config_groups['alert.default_mail']['config_descr'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                        <div class="col-sm-4">
                            <input id="default_mail" class="form-control" type="text" name="global-config-input" value="'.$config_groups['alert.default_mail']['config_value'].'" data-config_id="'.$config_groups['alert.default_mail']['config_id'].'">
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                    </div>
                    <div class="form-group has-feedback">
                        <label for="tolerance_window" class="col-sm-4 control-label">Tolerance window for cron </label>
                        <div data-toggle="tooltip" title="'.$config_groups['alert.tolerance_window']['config_descr'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                        <div class="col-sm-4">
                            <input id="tolerance_window" class="form-control" type="text" name="global-config-input" value="'.$config_groups['alert.tolerance_window']['config_value'].'" data-config_id="'.$config_groups['alert.tolerance_window']['config_id'].'">
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
                        <label for="default_only" class="col-sm-4 control-label">Enable email alerting </label>
                        <div data-toggle="tooltip" title="'.$config_groups['alert.transport.mail']['config_descr'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                        <div class="col-sm-4">
                            <input id="mail_transport" type="checkbox" name="global-config-check" '.$config_groups['alert.transport.mail']['config_checked'].' data-on-text="Yes" data-off-text="No" data-size="small" data-config_id="'.$config_groups['alert.transport.mail']['config_id'].'">
                        </div>
                    </div>
                    <div class="form-group has-feedback">
                        <label for="email_backend" class="col-sm-4 control-label">How to deliver mail </label>
                        <div data-toggle="tooltip" title="'.$config_groups['email_backend']['config_descr'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                        <div class="col-sm-4">
                            <select id="email_backend" class="form-control" name="global-config-select" data-config_id="'.$config_groups['email_backend']['config_id'].'">';
                            foreach ($dyn_config['email_backend'] as $backend) {
                                echo '<option value="'.$backend.'"';
                                if ($config_groups['email_backend']['config_value'] == $backend) {
                                    echo ' selected';
                                }
                                echo '>'.$backend.'</option>';
                            }                            


                            echo '</select>
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                    </div>
                    <div class="form-group has-feedback">
                        <label for="email_from" class="col-sm-4 control-label">From address </label>
                        <div data-toggle="tooltip" title="'.$config_groups['email_from']['config_descr'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                        <div class="col-sm-4">
                            <input id="email_from" class="form-control" type="text" name="global-config-input" value="'.$config_groups['email_from']['config_value'].'" data-config_id="'.$config_groups['email_from']['config_id'].'">
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                    </div>
                    <div class="form-group has-feedback">
                        <label for="email_user" class="col-sm-4 control-label">From name </label>
                        <div data-toggle="tooltip" title="'.$config_groups['email_user']['config_descr'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                        <div class="col-sm-4">
                            <input id="email_user" class="form-control" type="text" name="global-config-input" value="'.$config_groups['email_user']['config_value'].'" data-config_id="'.$config_groups['email_user']['config_id'].'">
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                    </div>
                    <div class="form-group has-feedback">
                        <label for="email_sendmail_path" class="col-sm-4 control-label">Sendmail path </label>
                        <div data-toggle="tooltip" title="'.$config_groups['email_sendmail_path']['config_descr'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                        <div class="col-sm-4">
                            <input id="email_sendmail_path" class="form-control" type="text" name="global-config-input" value="'.$config_groups['email_sendmail_path']['config_value'].'" data-config_id="'.$config_groups['email_sendmail_path']['config_id'].'">
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                    </div>
                    <div class="form-group has-feedback">
                        <label for="email_smtp_host" class="col-sm-4 control-label">SMTP Host </label>
                        <div data-toggle="tooltip" title="'.$config_groups['email_smtp_host']['config_descr'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                        <div class="col-sm-4">
                            <input id="email_smtp_host" class="form-control" type="text" name="global-config-input" value="'.$config_groups['email_smtp_host']['config_value'].'" data-config_id="'.$config_groups['email_smtp_host']['config_id'].'">
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                    </div>
                    <div class="form-group has-feedback">
                        <label for="email_smtp_port" class="col-sm-4 control-label">SMTP Port </label>
                        <div data-toggle="tooltip" title="'.$config_groups['email_smtp_port']['config_descr'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                        <div class="col-sm-4">
                            <input id="email_smtp_port" class="form-control" type="text" name="global-config-input" value="'.$config_groups['email_smtp_port']['config_value'].'" data-config_id="'.$config_groups['email_smtp_port']['config_id'].'">
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                    </div>
                    <div class="form-group has-feedback">
                        <label for="email_smtp_timeout" class="col-sm-4 control-label">SMTP Timeout </label>
                        <div data-toggle="tooltip" title="'.$config_groups['email_smtp_timeout']['config_descr'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                        <div class="col-sm-4">
                            <input id="email_smtp_timeout" class="form-control" type="text" name="global-config-input" value="'.$config_groups['email_smtp_timeout']['config_value'].'" data-config_id="'.$config_groups['email_smtp_timeout']['config_id'].'">
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                    </div>
                    <div class="form-group has-feedback">
                        <label for="email_smtp_secure" class="col-sm-4 control-label">SMTP Secure </label>
                        <div data-toggle="tooltip" title="'.$config_groups['email_smtp_secure']['config_descr'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                        <div class="col-sm-4">
                            <select id="email_smtp_secure" class="form-control"name="global-config-select" data-config_id="'.$config_groups['email_smtp_secure']['config_id'].'">';
                            foreach ($dyn_config['email_smtp_secure'] as $secure) {
                                echo "<option value='$secure'";
                                if ($config_groups['email_smtp_secure']['config_value'] == $secure) {
                                    echo " selected";
                                }
                                echo ">$secure</option>";
                            }
                            echo '</select>
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email_smtp_auth" class="col-sm-4 control-label">SMTP Authentication </label>
                        <div data-toggle="tooltip" title="'.$config_groups['email_smtp_auth']['config_descr'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                        <div class="col-sm-4">
                            <input id="email_smtp_auth" type="checkbox" name="global-config-check" '.$config_groups['email_smtp_auth']['config_checked'].' data-on-text="Yes" data-off-text="No" data-size="small" data-config_id="'.$config_groups['email_smtp_auth']['config_id'].'">
                        </div>
                    </div>
                    <div class="form-group has-feedback">
                        <label for="email_smtp_username" class="col-sm-4 control-label">SMTP Authentication username </label>
                        <div data-toggle="tooltip" title="'.$config_groups['email_smtp_username']['config_descr'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                        <div class="col-sm-4">
                            <input id="email_smtp_username" class="form-control" type="text" name="global-config-input" value="'.$config_groups['email_smtp_username']['config_value'].'" data-config_id="'.$config_groups['email_smtp_username']['config_id'].'">
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                    </div>
                    <div class="form-group has-feedback">
                        <label for="email_smtp_password" class="col-sm-4 control-label">SMTP AUthentication passoword </label>
                        <div data-toggle="tooltip" title="'.$config_groups['email_smtp_password']['config_descr'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                        <div class="col-sm-4">
                            <input id="email_smtp_password" class="form-control" type="text" name="global-config-input" value="'.$config_groups['email_smtp_password']['config_value'].'" data-config_id="'.$config_groups['email_smtp_password']['config_id'].'">
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
                        <div class="col-sm-2">
                            <a href="https://connect.pagerduty.com/connect?vendor=2fc7c9f3c8030e74aae6&callback='.$callback.'"><img src="images/pd_connect_button.png" width="202" height="36" alt="Connect to PagerDuty"></a>
                        </div>
                        <div class="col-sm-1">';
                            if (empty($config_groups['alert.transports.pagerduty']['config_value']) === FALSE) {
                                echo "<i class='fa fa-check-square-o fa-col-success fa-3x'></i>". $config_groups['alert.transports.pagerduty']['config_value'];
                            } else {
                                echo "<i class='fa fa-check-square-o fa-col-danger fa-3x'></i>";
                            }
                    echo '</div>
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
    $( 'select[name="global-config-select"]').change(function(event) {
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
