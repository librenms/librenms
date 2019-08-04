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

use LibreNMS\Config;

$no_refresh = true;

$config_groups = get_config_by_group('alerting');

if (Config::has('base_url') && filter_var(Config::get('base_url') . '/' . $_SERVER['REQUEST_URI'], FILTER_VALIDATE_URL)) {
    $callback = Config::get('base_url') . '/' . $_SERVER['REQUEST_URI'] . '/';
} else {
    $callback = get_url().'/';
}

$callback = urlencode($callback);

$general_conf = array(
    array('name'               => 'alert.disable',
          'descr'              => 'Disable alerting',
          'type'               => 'checkbox',
    ),
    array('name'               => 'alert.admins',
          'descr'              => 'Issue alerts to admins',
          'type'               => 'checkbox',
    ),
    array('name'               => 'alert.globals',
          'descr'              => 'Issue alerts to read only users',
          'type'               => 'checkbox',
    ),
    array('name'               => 'alert.users',
        'descr'                => 'Issue alerts to normal users',
        'type'                 => 'checkbox',
    ),
    array('name'               => 'alert.syscontact',
          'descr'              => 'Issue alerts to sysContact',
          'type'               => 'checkbox',
    ),
    array('name'               => 'alert.default_only',
          'descr'              => 'Send alerts to default contact only',
          'type'               => 'checkbox',
    ),
    array('name'               => 'alert.default_copy',
          'descr'              => 'Copy all email alerts to default contact',
          'type'               => 'checkbox',
    ),
    array('name'               => 'alert.default_mail',
          'descr'              => 'Default contact',
          'type'               => 'text',
          'pattern'            => '[a-zA-Z0-9_\-\.\+]+@[a-zA-Z0-9_\-\.]+\.[a-zA-Z]{2,18}',
    ),
    array('name'               => 'alert.tolerance_window',
          'descr'              => 'Tolerance window for cron',
          'type'               => 'numeric',
          'required'           => true,
    ),
    array('name'               => 'alert.fixed-contacts',
          'descr'              => 'Updates to contact email addresses not honored',
          'type'               => 'checkbox',
    ),
    [
        'name'                 => 'alert.ack_until_clear',
        'descr'                => 'Default acknowledge until alert clears option',
        'type'                 => 'checkbox',
    ]
);

$mail_conf = [
    [
        'name'     => 'alert.transports.mail',
        'descr'    => 'Enable email alerting',
        'type'     => 'checkbox',
    ],
    [
        'name'     => 'email_backend',
        'descr'    => 'How to deliver mail',
        'options'  => Config::get('email_backend_options', ['mail', 'sendmail', 'smtp']),
        'type'     => 'select',
    ],
    [
        'name'     => 'email_user',
        'descr'    => 'From name',
        'type'     => 'text',
    ],
    [
        'name'     => 'email_from',
        'descr'    => 'From email address',
        'type'     => 'text',
        'pattern'  => '[a-zA-Z0-9_\-\.\+]+@[a-zA-Z0-9_\-\.]+\.[a-zA-Z]{2,18}',
    ],
    [
        'name'     => 'email_html',
        'descr'    => 'Use HTML emails',
        'type'     => 'checkbox',
    ],
    [
        'name'     => 'email_sendmail_path',
        'descr'    => 'Sendmail path',
        'type'     => 'text',
        'class'    => 'sendmail-form',
    ],
    [
        'name'     => 'email_smtp_host',
        'descr'    => 'SMTP Host',
        'type'     => 'text',
        'pattern'  => '[a-zA-Z0-9_\-\.]+',
        'class'    => 'smtp-form',
    ],
    [
        'name'     => 'email_smtp_port',
        'descr'    => 'SMTP Port',
        'type'     => 'numeric',
        'class'    => 'smtp-form',
        'required' => true,
    ],
    [
        'name'     => 'email_smtp_timeout',
        'descr'    => 'SMTP Timeout',
        'type'     => 'numeric',
        'class'    => 'smtp-form',
        'required' => true,
    ],
    [
        'name'     => 'email_smtp_secure',
        'descr'    => 'SMTP Secure',
        'type'     => 'select',
        'class'    => 'smtp-form',
        'options'  => Config::get('email_smtp_secure_options', ['', 'tls', 'ssl']),
    ],
    [
        'name'     => 'email_auto_tls',
        'descr'    => 'SMTP Auto TLS Support',
        'type'     => 'select',
        'class'    => 'smtp-form',
        'options'  => ['true', 'false'],
    ],
    [
        'name'     => 'email_smtp_auth',
        'descr'    => 'SMTP Authentication',
        'type'     => 'checkbox',
        'class'    => 'smtp-form',
    ],
    [
        'name'     => 'email_smtp_username',
        'descr'    => 'SMTP Authentication Username',
        'type'     => 'text',
        'class'    => 'smtp-form',
    ],
    [
        'name'     => 'email_smtp_password',
        'descr'    => 'SMTP Authentication Password',
        'type'     => 'password',
        'class'    => 'smtp-form',
    ],
];

echo '
<div class="panel-group" id="accordion">
    <form class="form-horizontal" role="form" action="" method="post">
';
echo csrf_field();

echo generate_dynamic_config_panel('General alert settings', $config_groups, $general_conf);

echo generate_dynamic_config_panel('Email options', $config_groups, $mail_conf);

echo '
    </form>
</div>
';

?>

<script>

    $(".toolTip").tooltip();

    $('#email_backend').change(function () {
        var type = this.value;
        if (type === 'sendmail') {
            $('.smtp-form').hide();
            $('.sendmail-form').show();
        } else if (type === 'smtp') {
            $('.sendmail-form').hide();
            $('.smtp-form').show();
        } else {
            $('.smtp-form').hide();
            $('.sendmail-form').hide();
        }
    }).change(); // trigger initially

    apiIndex = 0;

    $( 'select[name="global-config-select"]').change(function(event) {
        event.preventDefault();
        var $this = $(this);
        var config_id = $this.data("config_id");
        var config_value = $this.val();
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: {type: "update-config-item", config_id: config_id, config_value: config_value},
            dataType: "json",
            success: function (data) {
                if (data.status == 'ok') {
                    $this.closest('.form-group').addClass('has-success');
                    $this.next().addClass('fa-check');
                    setTimeout(function(){
                        $this.closest('.form-group').removeClass('has-success');
                        $this.next().removeClass('fa-check');
                    }, 2000);
                } else {
                    $(this).closest('.form-group').addClass('has-error');
                    $this.next().addClass('fa-times');
                    setTimeout(function(){
                        $this.closest('.form-group').removeClass('has-error');
                        $this.next().removeClass('fa-times');
                    }, 2000);
                }
            },
            error: function () {
                $("#message").html('<div class="alert alert-danger">An error occurred.</div>');
            }
        });
    });

    $(document).on('blur', 'textarea[name="global-config-textarea"]', function(event) {
        event.preventDefault();
        var $this = $(this);
        var config_id = $this.data("config_id");
        var config_value = $this.val();
        var config_type = $this.data("type");
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: {type: "update-config-item", action: 'update-textarea', config_type: config_type, config_id: config_id, config_value: config_value},
            dataType: "json",
            success: function (data) {
                if (data.status == 'ok') {
                    $this.closest('.form-group').addClass('has-success');
                    $this.next().addClass('fa-check');
                    setTimeout(function(){
                        $this.closest('.form-group').removeClass('has-success');
                        $this.next().removeClass('fa-check');
                    }, 2000);
                } else {
                    $(this).closest('.form-group').addClass('has-error');
                    $this.next().addClass('fa-times');
                    setTimeout(function(){
                        $this.closest('.form-group').removeClass('has-error');
                        $this.next().removeClass('fa-times');
                    }, 2000);
                }
            },
            error: function () {
                $("#message").html('<div class="alert alert-danger">An error occurred.</div>');
            }
        });
    });
</script>

