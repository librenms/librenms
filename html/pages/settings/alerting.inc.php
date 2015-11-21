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

$no_refresh = true;

?>

<!-- API URL Modal -->
<div class="modal fade" id="new-config-api" role="dialog" aria-hidden="true" title="Create new config item">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <form role="form" class="new_config_form">
                    <div class="form-group">
                        <span class="message"></span>
                    </div>
                    <div class="form-group">
                        <label for="new_conf_name">Method</label>
                        <select name="new_method" id="new_method" class="form-control">
                            <option value="get">GET</option>
                            <option value="post">POST</option>
                            <option value="put">PUT</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="new_conf_value">API URL</label>
                        <input type="text" class="form-control" name="new_conf_value" id="new_conf_value" placeholder="Enter the config value">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" id="submit">Add config</button>
                <a href="#" class="btn" data-dismiss="modal">Cancel</a>
            </div>
        </div>
    </div>
</div>
<!-- End API URL Modal -->

<!-- Slack Modal -->
<div class="modal fade" id="new-config-slack" role="dialog" aria-hidden="true" title="Create new config item">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <form role="form" class="new_config_form">
                    <div class="form-group">
                        <span class="message"></span>
                    </div>
                    <div class="form-group">
                        <label for="slack_value">Slack API URL</label>
                        <input type="text" class="form-control" name="slack_value" id="slack_value" placeholder="Enter the Slack API url">
                    </div>
                    <div class="form-group">
                        <label for="slack_extra">Slack options (specify one per line key=value)</label>
                        <textarea class="form-control" name="slack_extra" id="slack_extra" placeholder="Enter the config options"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" id="submit-slack">Add config</button>
                <a href="#" class="btn" data-dismiss="modal">Cancel</a>
            </div>
        </div>
    </div>
</div>
<!-- End Slack Modal -->

<!-- Hipchat Modal -->
<div class="modal fade" id="new-config-hipchat" role="dialog" aria-hidden="true" title="Create new config item">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <form role="form" class="new_config_form">
                    <div class="form-group">
                        <span class="message"></span>
                    </div>
                    <div class="form-group">
                        <label for="hipchat_value">Hipchat API URL</label>
                        <input type="text" class="form-control" name="hipchat_value" id="hipchat_value" placeholder="Enter the Hipchat API url">
                    </div>
                    <div class="form-group">
                        <label for="new_room_id">Room ID</label>
                        <input type="text" class="form-control" name="new_room_id" id="new_room_id" placeholder="Enter the room ID">
                    </div>
                    <div class="form-group">
                        <label for="new_from">From</label>
                        <input type="text" class="form-control" name="new_from" id="new_from" placeholder="Enter the from details">
                    </div>
                    <div class="form-group">
                        <label for="hipchat_extra">Hipchat options (specify one per line key=value)</label>
                        <textarea class="form-control" name="hipchat_extra" id="hipchat_extra" placeholder="Enter the config options"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" id="submit-hipchat">Add config</button>
                <a href="#" class="btn" data-dismiss="modal">Cancel</a>
            </div>
        </div>
    </div>
</div>
<!-- End Hipchat Modal -->

<!-- Pushover Modal -->
<div class="modal fade" id="new-config-pushover" role="dialog" aria-hidden="true" title="Create new config item">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <form role="form" class="new_config_form">
                    <div class="form-group">
                        <span class="message"></span>
                    </div>
                    <div class="form-group">
                        <label for="pushover_value">Pushover Apikey</label>
                        <input type="text" class="form-control" name="pushover_value" id="pushover_value" placeholder="Enter the Pushover Apikey">
                    </div>
                    <div class="form-group">
                        <label for="new_userkey">Room ID</label>
                        <input type="text" class="form-control" name="new_userkey" id="new_userkey" placeholder="Enter the Userkey">
                    </div>
                    <div class="form-group">
                        <label for="pushover_extra">Pushover options (specify one per line key=value)</label>
                        <textarea class="form-control" name="pushover_extra" id="pushover_extra" placeholder="Enter the config options"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" id="submit-pushover">Add config</button>
                <a href="#" class="btn" data-dismiss="modal">Cancel</a>
            </div>
        </div>
    </div>
</div>
<!-- End Pushover Modal -->

<!-- Boxcar Modal -->
<div class="modal fade" id="new-config-boxcar" role="dialog" aria-hidden="true" title="Create new config item">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <form role="form" class="new_config_form">
                    <div class="form-group">
                        <span class="message"></span>
                    </div>
                    <div class="form-group">
                        <label for="pushover_value">Boxcar Access token</label>
                        <input type="text" class="form-control" name="boxcar_value" id="boxcar_value" placeholder="Enter the Boxcar Access token">
                    </div>
                    <div class="form-group">
                        <label for="pushover_extra">Boxcar options (specify one per line key=value)</label>
                        <textarea class="form-control" name="boxcar_extra" id="boxcar_extra" placeholder="Enter the config options"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" id="submit-boxcar">Add config</button>
                <a href="#" class="btn" data-dismiss="modal">Cancel</a>
            </div>
        </div>
    </div>
</div>
<!-- End Boxcar Modal -->

<?php
if (isset($_GET['error'])) {
    print_error('We had issues connecting to your Pager Duty account, please try again');
}

if (isset($_GET['account']) && isset($_GET['service_key']) && isset($_GET['service_name'])) {
    set_config_name('alert.transports.pagerduty', $_GET['service_key']);
    set_config_name('alert.pagerduty.account', $_GET['account']);
    set_config_name('alert.pagerduty.service', $_GET['service_name']);
}

$config_groups = get_config_by_group('alerting');

if (isset($config['base_url'])) {
    $callback = $config['base_url'].'/'.$_SERVER['REQUEST_URI'].'/';
}
else {
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
    array('name'               => 'alert.syscontact',
          'descr'              => 'Issue alerts to sysContact',
          'type'               => 'checkbox',
    ),
    array('name'               => 'alert.default_only',
          'descr'              => 'Send alerts to default contact only',
          'type'               => 'checkbox',
    ),
    array('name'               => 'alert.default_mail',
          'descr'              => 'Default contact',
          'type'               => 'text',
    ),
    array('name'               => 'alert.tolerance_window',
          'descr'              => 'Tolerance window for cron',
          'type'               => 'text',
    ),
    array('name'               => 'alert.fixed-contacts',
          'descr'              => 'Updates to contact email addresses not honored',
          'type'               => 'checkbox',
    ),
);

$mail_conf = array(
    array('name'               => 'alert.transports.mail',
          'descr'              => 'Enable email alerting',
          'type'               => 'checkbox',
    ),
    array('name'               => 'email_backend',
          'descr'              => 'How to deliver mail',
          'options'            => $dyn_config['email_backend'],
          'type'               => 'select',
    ),
    array('name'               => 'email_user',
          'descr'              => 'From name',
          'type'               => 'text',
    ),
    array('name'               => 'email_sendmail_path',
          'descr'              => 'Sendmail path',
          'type'               => 'text',
    ),
    array('name'               => 'email_smtp_host',
          'descr'              => 'SMTP Host',
          'type'               => 'text',
    ),
    array('name'               => 'email_smtp_port',
          'descr'              => 'SMTP Port',
          'type'               => 'text',
    ),
    array('name'               => 'email_smtp_timeout',
          'descr'              => 'SMTP Timeout',
          'type'               => 'text',
    ),
    array('name'               => 'email_smtp_secure',
          'descr'              => 'SMTP Secure',
          'type'               => 'select',
          'options'            => $dyn_config['email_smtp_secure'],
    ),
    array('name'               => 'email_smtp_auth',
          'descr'              => 'SMTP Authentication',
          'type'               => 'checkbox',
    ),
    array('name'               => 'email_smtp_username',
          'descr'              => 'SMTP Authentication Username',
          'type'               => 'text',
    ),
    array('name'               => 'email_smtp_password',
          'descr'              => 'SMTP Authentication Password',
          'type'               => 'text',
    ),
);

echo '
<div class="panel-group" id="accordion">
    <form class="form-horizontal" role="form" action="" method="post">
';

echo generate_dynamic_config_panel('General alert settings',true,$config_groups,$general_conf);
echo generate_dynamic_config_panel('Email transport',true,$config_groups,$mail_conf,'mail');

echo '
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordion" href="#api_transport_expand">API transport</a> <button name="test-alert" id="test-alert" type="button" data-transport="api" class="btn btn-primary btn-xs pull-right">Test transport</button>
                </h4>
            </div>
            <div id="api_transport_expand" class="panel-collapse collapse">
                <div class="panel-body">
                    <div class="form-group">
                        <div class="col-sm-8">
                            <button class="btn btn-success btn-xs" type="button" name="new_config" id="new_config_item" data-toggle="modal" data-target="#new-config-api">Add API URL</button>
                        </div>
                    </div>';
                    $api_urls = get_config_like_name('alert.transports.api.%.');
foreach ($api_urls as $api_url) {
    $api_split  = split('\.', $api_url['config_name']);
    $api_method = $api_split[3];
    echo '<div class="form-group has-feedback" id="'.$api_url['config_id'].'">
                        <label for="api_url" class="col-sm-4 control-label">API URL ('.$api_method.') </label>
                        <div class="col-sm-4">
                            <input id="api_url" class="form-control" type="text" name="global-config-input" value="'.$api_url['config_value'].'" data-config_id="'.$api_url['config_id'].'">
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                        <div class="col-sm-2">
                            <button type="button" class="btn btn-danger del-api-config" name="del-api-call" data-config_id="'.$api_url['config_id'].'"><i class="fa fa-minus"></i></button>
                        </div>
                    </div>';
}

                    echo '<div class="form-group has-feedback hide" id="api_url_template">
                        <label for="api_url" class="col-sm-4 control-label api-method">API URL </label>
                        <div class="col-sm-4">
                            <input id="api_url" class="form-control" type="text" name="global-config-input" value="" data-config_id="">
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                        <div class="col-sm-2">
                            <button type="button" class="btn btn-danger del-api-config" name="del-api-call" data-config_id=""><i class="fa fa-minus"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordion" href="#pagerduty_transport_expand">Pagerduty transport</a> <button name="test-alert" id="test-alert" type="button" data-transport="pagerduty" class="btn btn-primary btn-xs pull-right">Test transport</button>
                </h4>
            </div>
            <div id="pagerduty_transport_expand" class="panel-collapse collapse">
                <div class="panel-body">
                    <div class="form-group">
                        <div class="col-sm-2">
                            <a href="https://connect.pagerduty.com/connect?vendor=2fc7c9f3c8030e74aae6&callback='.$callback.'"><img src="images/pd_connect_button.png" width="202" height="36" alt="Connect to PagerDuty"></a>
                        </div>
                        <div class="col-sm-1">';
if (empty($config_groups['alert.transports.pagerduty']['config_value']) === false) {
    echo "<i class='fa fa-check-square-o fa-col-success fa-3x'></i>";
}
else {
    echo "<i class='fa fa-check-square-o fa-col-danger fa-3x'></i>";
}

                    echo '</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordion" href="#nagios_transport_expand">Nagios compatible transport</a> <button name="test-alert" id="test-alert" type="button" data-transport="nagios" class="btn btn-primary btn-xs pull-right">Test transport</button>
                </h4>
            </div>
            <div id="nagios_transport_expand" class="panel-collapse collapse">
                <div class="panel-body">
                    <div class="form-group has-feedback">
                        <label for="nagios" class="col-sm-4 control-label">Nagios compatible FIFO </label>
                        <div data-toggle="tooltip" title="'.$config_groups['alert.transports.nagios']['config_descr'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                        <div class="col-sm-4">
                            <input id="nagios" class="form-control" type="text" name="global-config-input" value="'.$config_groups['alert.transports.nagios']['config_value'].'" data-config_id="'.$config_groups['alert.transports.nagios']['config_id'].'">
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordion" href="#irc_transport_expand">IRC transport</a> <button name="test-alert" id="test-alert" type="button" data-transport="irc" class="btn btn-primary btn-xs pull-right">Test transport</button>
                </h4>
            </div>
            <div id="irc_transport_expand" class="panel-collapse collapse">
                <div class="panel-body">
                    <div class="form-group">
                        <label for="irc" class="col-sm-4 control-label">Enable irc transport </label>
                        <div data-toggle="tooltip" title="'.$config_groups['alert.transports.irc']['config_descr'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                        <div class="col-sm-4">
                            <input id="irc" type="checkbox" name="global-config-check" '.$config_groups['alert.transports.irc']['config_checked'].' data-on-text="Yes" data-off-text="No" data-size="small" data-config_id="'.$config_groups['alert.transports.irc']['config_id'].'">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordion" href="#slack_transport_expand">Slack transport</a> <button name="test-alert" id="test-alert" type="button" data-transport="slack" class="btn btn-primary btn-xs pull-right">Test transport</button>
                </h4>
            </div>
            <div id="slack_transport_expand" class="panel-collapse collapse">
                <div class="panel-body">
                    <div class="form-group">
                        <div class="col-sm-8">
                            <button class="btn btn-success btn-xs" type="button" name="new_config" id="new_config_item" data-toggle="modal" data-target="#new-config-slack">Add Slack URL</button>
                        </div>
                    </div>';
                    $slack_urls = get_config_like_name('alert.transports.slack.%.url');
foreach ($slack_urls as $slack_url) {
    unset($upd_slack_extra);
    $new_slack_extra = array();
    $slack_extras    = get_config_like_name('alert.transports.slack.'.$slack_url['config_id'].'.%');
    foreach ($slack_extras as $extra) {
        $split_extra = explode('.', $extra['config_name']);
        if ($split_extra[4] != 'url') {
            $new_slack_extra[] = $split_extra[4].'='.$extra['config_value'];
        }
    }

    $upd_slack_extra = implode(PHP_EOL, $new_slack_extra);
    echo '<div id="'.$slack_url['config_id'].'">
                        <div class="form-group has-feedback">
                            <label for="slack_url" class="col-sm-4 control-label">Slack URL </label>
                            <div class="col-sm-4">
                                <input id="slack_url" class="form-control" type="text" name="global-config-input" value="'.$slack_url['config_value'].'" data-config_id="'.$slack_url['config_id'].'">
                                <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-danger del-slack-config" name="del-slack-call" data-config_id="'.$slack_url['config_id'].'"><i class="fa fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="form-group has-feedback">
                            <div class="col-sm-offset-4 col-sm-4">
                                <textarea class="form-control" name="global-config-textarea" id="upd_slack_extra" placeholder="Enter the config options" data-config_id="'.$slack_url['config_id'].'" data-type="slack">'.$upd_slack_extra.'</textarea>
                                <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                            </div>
                        </div>
                    </div>';
}//end foreach

                    echo '<div class="hide" id="slack_url_template">
                        <div class="form-group has-feedback">
                            <label for="slack_url" class="col-sm-4 control-label api-method">Slack URL </label>
                            <div class="col-sm-4">
                                <input id="slack_url" class="form-control" type="text" name="global-config-input" value="" data-config_id="">
                                <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-danger del-slack-config" name="del-slack-call" data-config_id=""><i class="fa fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="form-group has-feedback">
                            <div class="col-sm-offset-4 col-sm-4">
                                <textarea class="form-control" name="global-config-textarea" id="upd_slack_extra" placeholder="Enter the config options" data-config_id="" data-type="slack"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordion" href="#hipchat_transport_expand">Hipchat transport</a> <button name="test-alert" id="test-alert" type="button" data-transport="hipchat" class="btn btn-primary btn-xs pull-right">Test transport</button>
                </h4>
            </div>
            <div id="hipchat_transport_expand" class="panel-collapse collapse">
                <div class="panel-body">
                    <div class="form-group">
                        <div class="col-sm-8">
                            <button class="btn btn-success btn-xs" type="button" name="new_config" id="new_config_item" data-toggle="modal" data-target="#new-config-hipchat">Add Hipchat URL</button>
                        </div>
                    </div>';
                    $hipchat_urls = get_config_like_name('alert.transports.hipchat.%.url');
foreach ($hipchat_urls as $hipchat_url) {
    unset($upd_hipchat_extra);
    $new_hipchat_extra = array();
    $hipchat_extras    = get_config_like_name('alert.transports.hipchat.'.$hipchat_url['config_id'].'.%');
    $hipchat_room_id   = get_config_by_name('alert.transports.hipchat.'.$hipchat_url['config_id'].'.room_id');
    $hipchat_from      = get_config_by_name('alert.transports.hipchat.'.$hipchat_url['config_id'].'.from');
    foreach ($hipchat_extras as $extra) {
        $split_extra = explode('.', $extra['config_name']);
        if ($split_extra[4] != 'url' && $split_extra[4] != 'room_id' && $split_extra[4] != 'from') {
            $new_hipchat_extra[] = $split_extra[4].'='.$extra['config_value'];
        }
    }

    $upd_hipchat_extra = implode(PHP_EOL, $new_hipchat_extra);
    echo '<div id="'.$hipchat_url['config_id'].'">
                        <div class="form-group has-feedback">
                            <label for="hipchat_url" class="col-sm-4 control-label">Hipchat URL </label>
                            <div class="col-sm-4">
                                <input id="hipchat_url" class="form-control" type="text" name="global-config-input" value="'.$hipchat_url['config_value'].'" data-config_id="'.$hipchat_url['config_id'].'">
                                <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-danger del-hipchat-config" name="del-hipchat-call" data-config_id="'.$hipchat_url['config_id'].'"><i class="fa fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="form-group has-feedback">
                            <label for="hipchat_room_id" class="col-sm-4 control-label">Room ID</label>
                            <div class="col-sm-4">
                                <input id="hipchat_room_id" class="form-control" type="text" name="global-config-input" value="'.$hipchat_room_id['config_value'].'" data-config_id="'.$hipchat_room_id['config_id'].'">
                                <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                            </div>
                        </div>
                        <div class="form-group has-feedback">
                            <label for="hipchat_from" class="col-sm-4 control-label">From</label>
                            <div class="col-sm-4">
                                <input id="hipchat_from" class="form-control" type="text" name="global-config-input" value="'.$hipchat_from['config_value'].'" data-config_id="'.$hipchat_from['config_id'].'">
                                <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                            </div>
                        </div>
                        <div class="form-group has-feedback">
                            <div class="col-sm-offset-4 col-sm-4">
                                <textarea class="form-control" name="global-config-textarea" id="upd_hipchat_extra" placeholder="Enter the config options" data-config_id="'.$hipchat_url['config_id'].'" data-type="hipchat">'.$upd_hipchat_extra.'</textarea>
                                <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                            </div>
                        </div>
                    </div>';
}//end foreach

                    echo '<div id="hipchat_url_template" class="hide">
                        <div class="form-group has-feedback">
                            <label for="hipchat_url" class="col-sm-4 control-label api-method">Hipchat URL </label>
                            <div class="col-sm-4">
                                <input id="hipchat_url" class="form-control" type="text" name="global-config-input" value="" data-config_id="">
                                <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-danger del-hipchat-config" id="del-hipchat-call" name="del-hipchat-call" data-config_id=""><i class="fa fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="form-group has-feedback">
                            <label for="hipchat_room_id" class="col-sm-4 control-label">Room ID</label>
                            <div class="col-sm-4">
                                <input id="global-config-room_id" class="form-control" type="text" name="global-config-input" value="" data-config_id="">
                                <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                            </div>
                        </div>
                        <div class="form-group has-feedback">
                            <label for="hipchat_from" class="col-sm-4 control-label">From</label>
                            <div class="col-sm-4">
                                <input id="global-config-from" class="form-control" type="text" name="global-config-input" value="" data-config_id="">
                                <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                            </div>
                        </div>
                        <div class="form-group has-feedback">
                            <div class="col-sm-offset-4 col-sm-4">
                                <textarea class="form-control" name="global-config-textarea" id="upd_hipchat_extra" placeholder="Enter the config options" data-config_id="" data-type="hipchat"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordion" href="#pushover_transport_expand">Pushover transport</a> <button name="test-alert" id="test-alert" type="button" data-transport="pushover" class="btn btn-primary btn-xs pull-right">Test transport</button>
                </h4>
            </div>
            <div id="pushover_transport_expand" class="panel-collapse collapse">
                <div class="panel-body">
                    <div class="form-group">
                        <div class="col-sm-8">
                            <button class="btn btn-success btn-xs" type="button" name="new_config" id="new_config_item" data-toggle="modal" data-target="#new-config-pushover">Add Pushover config</button>
                        </div>
                    </div>';
$pushover_appkeys = get_config_like_name('alert.transports.pushover.%.appkey');
foreach ($pushover_appkeys as $pushover_appkey) {
    unset($upd_pushover_extra);
    $new_pushover_extra = array();
    $pushover_extras    = get_config_like_name('alert.transports.pushover.'.$pushover_appkey['config_id'].'.%');
    $pushover_userkey   = get_config_by_name('alert.transports.pushover.'.$pushover_appkey['config_id'].'.userkey');
    foreach ($pushover_extras as $extra) {
        $split_extra = explode('.', $extra['config_name']);
        if ($split_extra[4] != 'appkey' && $split_extra[4] != 'userkey') {
            $new_pushover_extra[] = $split_extra[4].'='.$extra['config_value'];
        }
    }

    $upd_pushover_extra = implode(PHP_EOL, $new_pushover_extra);
    echo '<div id="'.$pushover_appkey['config_id'].'">
                        <div class="form-group has-feedback">
                            <label for="pushover_appkey" class="col-sm-4 control-label">Pushover Appkey </label>
                            <div class="col-sm-4">
                                <input id="pushover_appkey" class="form-control" type="text" name="global-config-input" value="'.$pushover_appkey['config_value'].'" data-config_id="'.$pushover_appkey['config_id'].'">
                                <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-danger del-pushover-config" name="del-pushover-call" data-config_id="'.$pushover_appkey['config_id'].'"><i class="fa fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="form-group has-feedback">
                            <label for="pushover_userkey" class="col-sm-4 control-label">Userkey</label>
                            <div class="col-sm-4">
                                <input id="pushover_userkey" class="form-control" type="text" name="global-config-input" value="'.$pushover_userkey['config_value'].'" data-config_id="'.$pushover_userkey['config_id'].'">
                                <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                            </div>
                        </div>
                        <div class="form-group has-feedback">
                            <div class="col-sm-offset-4 col-sm-4">
                                <textarea class="form-control" name="global-config-textarea" id="upd_pushover_extra" placeholder="Enter the config options" data-config_id="'.$pushover_appkey['config_id'].'" data-type="pushover">'.$upd_pushover_extra.'</textarea>
                                <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                            </div>
                        </div>
                    </div>';
}//end foreach

echo '<div id="pushover_appkey_template" class="hide">
                        <div class="form-group has-feedback">
                            <label for="pushover_appkey" class="col-sm-4 control-label api-method">Pushover Appkey </label>
                            <div class="col-sm-4">
                                <input id="pushover_appkey" class="form-control" type="text" name="global-config-input" value="" data-config_id="">
                                <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-danger del-pushover-config" id="del-pushover-call" name="del-pushover-call" data-config_id=""><i class="fa fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="form-group has-feedback">
                            <label for="pushover_userkey" class="col-sm-4 control-label">Userkey</label>
                            <div class="col-sm-4">
                                <input id="global-config-userkey" class="form-control" type="text" name="global-config-input" value="" data-config_id="">
                                <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                            </div>
                        </div>
                        <div class="form-group has-feedback">
                            <div class="col-sm-offset-4 col-sm-4">
                                <textarea class="form-control" name="global-config-textarea" id="upd_pushover_extra" placeholder="Enter the config options" data-config_id="" data-type="pushover"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordion" href="#boxcar_transport_expand">Boxcar transport</a> <button name="test-alert" id="test-alert" type="button" data-transport="boxcar" class="btn btn-primary btn-xs pull-right">Test transport</button>
                </h4>
            </div>
            <div id="boxcar_transport_expand" class="panel-collapse collapse">
                <div class="panel-body">
                    <div class="form-group">
                        <div class="col-sm-8">
                            <button class="btn btn-success btn-xs" type="button" name="new_config" id="new_config_item" data-toggle="modal" data-target="#new-config-boxcar">Add Boxcar config</button>
                        </div>
                    </div>';
$boxcar_appkeys = get_config_like_name('alert.transports.boxcar.%.access_token');
foreach ($boxcar_appkeys as $boxcar_appkey) {
    unset($upd_boxcar_extra);
    $new_boxcar_extra = array();
    $boxcar_extras    = get_config_like_name('alert.transports.boxcar.'.$boxcar_appkey['config_id'].'.%');
    foreach ($boxcar_extras as $extra) {
        $split_extra = explode('.', $extra['config_name']);
        if ($split_extra[4] != 'access_token') {
            $new_boxcar_extra[] = $split_extra[4].'='.$extra['config_value'];
        }
    }

    $upd_boxcar_extra = implode(PHP_EOL, $new_boxcar_extra);
    echo '<div id="'.$boxcar_appkey['config_id'].'">
                        <div class="form-group has-feedback">
                            <label for="boxcar_access_token" class="col-sm-4 control-label">Boxcar Access token </label>
                            <div class="col-sm-4">
                                <input id="boxcar_access_token" class="form-control" type="text" name="global-config-input" value="'.$boxcar_appkey['config_value'].'" data-config_id="'.$boxcar_appkey['config_id'].'">
                                <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-danger del-boxcar-config" name="del-boxcar-call" data-config_id="'.$boxcar_appkey['config_id'].'"><i class="fa fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="form-group has-feedback">
                            <div class="col-sm-offset-4 col-sm-4">
                                <textarea class="form-control" name="global-config-textarea" id="upd_boxcar_extra" placeholder="Enter the config options" data-config_id="'.$boxcar_appkey['config_id'].'" data-type="boxcar">'.$upd_boxcar_extra.'</textarea>
                                <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                            </div>
                        </div>
                    </div>';
}//end foreach

echo '<div id="boxcar_appkey_template" class="hide">
                        <div class="form-group has-feedback">
                            <label for="boxcar_access_token" class="col-sm-4 control-label api-method">Boxcar Access token </label>
                            <div class="col-sm-4">
                                <input id="boxcar_access_token" class="form-control" type="text" name="global-config-input" value="" data-config_id="">
                                <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-danger del-boxcar-config" id="del-boxcar-call" name="del-boxcar-call" data-config_id=""><i class="fa fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="form-group has-feedback">
                            <div class="col-sm-offset-4 col-sm-4">
                                <textarea class="form-control" name="global-config-textarea" id="upd_boxcar_extra" placeholder="Enter the config options" data-config_id="" data-type="boxcar"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordion" href="#pushbullet_transport_expand">Pushbullet</a> <button name="test-alert" id="test-alert" type="button" data-transport="pushbullet" class="btn btn-primary btn-xs pull-right">Test transport</button>
                </h4>
            </div>
            <div id="pushbullet_transport_expand" class="panel-collapse collapse">
                <div class="panel-body">
                    <div class="form-group has-feedback">
                        <label for="pushbullet" class="col-sm-4 control-label">Pushbullet Access Token </label>
                        <div data-toggle="tooltip" title="'.$config_groups['alert.transports.pushbullet']['config_descr'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                        <div class="col-sm-4">
                            <input id="pushbullet" class="form-control" type="text" name="global-config-input" value="'.$config_groups['alert.transports.pushbullet']['config_value'].'" data-config_id="'.$config_groups['alert.transports.pushbullet']['config_id'].'">
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordion" href="#victorops_transport_expand">VictorOps</a> <button name="test-alert" id="test-alert" type="button" data-transport="victorops" class="btn btn-primary btn-xs pull-right">Test transport</button>
                </h4>
            </div>
            <div id="victorops_transport_expand" class="panel-collapse collapse">
                <div class="panel-body">
                    <div class="form-group has-feedback">
                        <label for="victorops" class="col-sm-4 control-label">Post URL </label>
                        <div data-toggle="tooltip" title="'.$config_groups['alert.transports.victorops.url']['config_descr'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                        <div class="col-sm-4">
                            <input id="victorops" class="form-control" type="text" name="global-config-input" value="'.$config_groups['alert.transports.victorops.url']['config_value'].'" data-config_id="'.$config_groups['alert.transports.victorops.url']['config_id'].'">
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>';

$clickatell   = get_config_by_name('alert.transports.clickatell.token');
$mobiles      = get_config_like_name('alert.transports.clickatell.to.%');
$new_mobiles = array();
foreach ($mobiles as $mobile) {
    $new_mobiles[] = $mobile['config_value'];
}
$upd_mobiles = implode(PHP_EOL, $new_mobiles);

echo '
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordion" href="#clickatell_transport_expand">Clickatell transport</a> <button name="test-alert" id="test-alert" type="button" data-transport="clickatell" class="btn btn-primary btn-xs pull-right">Test transport</button>
                </h4>
            </div>
            <div id="clickatell_transport_expand" class="panel-collapse collapse">
                <div class="panel-body">
                    <div class="form-group has-feedback">
                        <label for="clickatell_token" class="col-sm-4 control-label">Clickatell Token </label>
                        <div class="col-sm-4">
                            <input id="clickatell_token" class="form-control" type="text" name="global-config-input" value="'.$clickatell['config_value'].'" data-config_id="'.$clickatell['config_id'].'">
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                    </div>
                    <div class="form-group has-feedback">
                        <label for="clickatell_to" class="col-sm-4 control-label">Mobile numbers</label>
                        <div class="col-sm-4">
                            <textarea class="form-control" name="global-config-textarea" id="clickatell_to" placeholder="Enter the config options" data-config_id="'.$clickatell['config_id'].'" data-type="clickatell">'.$upd_mobiles.'</textarea>
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
$playsms_url     = get_config_by_name('alert.transports.playsms.url');
$playsms_user    = get_config_by_name('alert.transports.playsms.user');
$playsms_token   = get_config_by_name('alert.transports.playsms.token');
$playsms_from    = get_config_by_name('alert.transports.playsms.from');
$mobiles         = get_config_like_name('alert.transports.playsms.to.%');
$new_mobiles = array();
foreach ($mobiles as $mobile) {
    $new_mobiles[] = $mobile['config_value'];
}
$upd_mobiles = implode(PHP_EOL, $new_mobiles);
echo '
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordion" href="#playsms_transport_expand">PlaySMS transport</a> <button name="test-alert" id="test-alert" type="button" data-transport="playsms" class="btn btn-primary btn-xs pull-right">Test transport</button>
                </h4>
            </div>
            <div id="playsms_transport_expand" class="panel-collapse collapse">
                <div class="panel-body">
                    <div class="form-group has-feedback">
                        <label for="playsms_url" class="col-sm-4 control-label">PlaySMS URL </label>
                        <div class="col-sm-4">
                            <input id="playsms_url" class="form-control" type="text" name="global-config-input" value="'.$playsms_url['config_value'].'" data-config_id="'.$playsms_url['config_id'].'">
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                    </div>
                    <div class="form-group has-feedback">
                        <label for="playsms_user" class="col-sm-4 control-label">User</label>
                        <div class="col-sm-4">
                            <input id="playsms_user" class="form-control" type="text" name="global-config-input" value="'.$playsms_user['config_value'].'" data-config_id="'.$playsms_user['config_id'].'">
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                    </div>
                    <div class="form-group has-feedback">
                        <label for="playsms_token" class="col-sm-4 control-label">Token</label>
                        <div class="col-sm-4">
                            <input id="playsms_token" class="form-control" type="text" name="global-config-input" value="'.$playsms_token['config_value'].'" data-config_id="'.$playsms_token['config_id'].'">
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                    </div>
                    <div class="form-group has-feedback">
                        <label for="playsms_from" class="col-sm-4 control-label">From</label>
                        <div class="col-sm-4">
                            <input id="playsms_from" class="form-control" type="text" name="global-config-input" value="'.$playsms_from['config_value'].'" data-config_id="'.$playsms_from['config_id'].'">
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                    </div>
                    <div class="form-group has-feedback">
                        <label for="clickatell_to" class="col-sm-4 control-label">Mobiles</label>
                        <div class="col-sm-4">
                            <textarea class="form-control" name="global-config-textarea" id="playsms_to" placeholder="Enter the config options" data-config_id="'.$playsms_url['config_id'].'" data-type="playsms">'.$upd_mobiles.'</textarea>
                            <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                        </div>
                </div>
            </div>
        </div>
    </form>
</div>
';
?>

<script>

    <?php
    if (isset($_GET['service_key'])) {
        echo "$('#pagerduty_transport_expand').collapse('show');";
    }
    ?>

    $(".toolTip").tooltip();

    $("button#test-alert").click(function() {
        var $this = $(this);
        var transport = $this.data("transport");
        $.ajax({
            type: 'POST',
            url: '/ajax_form.php',
            data: { type: "test-transport", transport: transport },
            dataType: "json",
            success: function(data){
                if (data.status == 'ok') {
                    $this.removeClass('btn-primary').addClass('btn-success');
                    setTimeout(function(){
                        $this.removeClass('btn-success').addClass('btn-primary');
                    }, 2000);
                }
                else {
                    $this.removeClass('btn-primary').addClass('btn-danger');
                    setTimeout(function(){
                        $this.removeClass('btn-danger').addClass('btn-primary');
                    }, 2000);
                }
            },
            error: function(){
                $this.removeClass('btn-primary').addClass('btn-danger');
                setTimeout(function(){
                    $this.removeClass('btn-danger').addClass('btn-primary');
                }, 2000);
            }
        });
    });

    apiIndex = 0;

    // Add API config
    $("button#submit").click(function(){
        var config_name = 'alert.transports.api.'+$('#new_method').val()+'.';
        var new_api_method = $('#new_method').val();
        var config_value = $('#new_conf_value').val();
        $.ajax({
            type: "POST",
            url: "ajax_form.php",
            data: {type: "config-item", config_group: "alerting", config_sub_group: "transports", config_name: config_name, config_value: config_value},
            dataType: "json",
            success: function(data){
                if (data.status == 'ok') {
                    apiIndex++;
                    var $template = $('#api_url_template'),
                        $clone    = $template
                            .clone()
                            .removeClass('hide')
                            .attr('id',data.config_id)
                            .attr('api-url-index', apiIndex)
                            .insertBefore($template);
                        $clone.find('[name="global-config-input"]').attr('data-config_id',data.config_id);
                        $clone.find('[name="del-api-call"]').attr('data-config_id',data.config_id);
                        $clone.find('[name="global-config-input"]').attr('value', config_value);
                        $clone.find('.api-method').text("API URL (" + new_api_method + ")");
                    console.log(new_api_method);
                    $("#new-config-api").modal('hide');
                } else {
                    $("#message").html('<div class="alert alert-info">' + data.message + '</div>');
                }
            },
            error: function(){
                $("#message").html('<div class="alert alert-info">Error creating config item</div>');
            }
        });
    });// End Add API config

    // Add Slack config
    slackIndex = 0;
    $("button#submit-slack").click(function(){
        var config_value = $('#slack_value').val();
        var config_extra = $('#slack_extra').val();
        $.ajax({
            type: "POST",
            url: "ajax_form.php",
            data: {type: "config-item", action: 'add-slack', config_group: "alerting", config_sub_group: "transports", config_extra: config_extra, config_value: config_value},
            dataType: "json",
            success: function(data){
                if (data.status == 'ok') {
                    slackIndex++;
                    var $template = $('#slack_url_template'),
                        $clone    = $template
                            .clone()
                            .removeClass('hide')
                            .attr('id',data.config_id)
                            .attr('slack-url-index', slackIndex)
                            .insertBefore($template);
                        $clone.find('[name="global-config-input"]').attr('data-config_id',data.config_id);
                        $clone.find('[name="del-slack-call"]').attr('data-config_id',data.config_id);
                        $clone.find('[name="global-config-input"]').attr('value', config_value);
                        $clone.find('[name="global-config-textarea"]').val(config_extra);
                        $clone.find('[name="global-config-textarea"]').attr('data-config_id',data.config_id);
                    $("#new-config-slack").modal('hide');
                } else {
                    $("#message").html('<div class="alert alert-info">' + data.message + '</div>');
                }
            },
            error: function(){
                $("#message").html('<div class="alert alert-info">Error creating config item</div>');
            }
        });
    });// End Add Slack config

    // Add Hipchat config
    hipchatIndex = 0;
    $("button#submit-hipchat").click(function(){
        var config_value = $('#hipchat_value').val();
        var config_extra = $('#hipchat_extra').val();
        var config_room_id = $('#new_room_id').val();
        var config_from = $('#new_from').val();
        $.ajax({
            type: "POST",
            url: "ajax_form.php",
            data: {type: "config-item", action: 'add-hipchat', config_group: "alerting", config_sub_group: "transports", config_extra: config_extra, config_value: config_value, config_room_id: config_room_id, config_from: config_from},
            dataType: "json",
            success: function(data){
                if (data.status == 'ok') {
                    hipchatIndex++;
                    var $template = $('#hipchat_url_template'),
                        $clone    = $template
                            .clone()
                            .removeClass('hide')
                            .attr('id',data.config_id)
                            .attr('hipchat-url-index', hipchatIndex)
                            .insertBefore($template);
                        $clone.find('[id="hipchat_url"]').attr('data-config_id',data.config_id);
                        $clone.find('[id="del-hipchat-call"]').attr('data-config_id',data.config_id);
                        $clone.find('[name="global-config-input"]').attr('value', config_value);
                        $clone.find('[id="global-config-room_id"]').attr('value', config_room_id);
                        $clone.find('[id="global-config-from"]').attr('value', config_from);
                        $clone.find('[id="upd_hipchat_extra"]').val(config_extra);
                        $clone.find('[id="upd_hipchat_extra"]').attr('data-config_id',data.config_id);
                    $("#new-config-hipchat").modal('hide');
                } else {
                    $("#message").html('<div class="alert alert-info">' + data.message + '</div>');
                }
            },
            error: function(){
                $("#message").html('<div class="alert alert-info">Error creating config item</div>');
            }
        });
    });// End Add Hipchat config

    // Add Pushover config
    pushoverIndex = 0;
    $("button#submit-pushover").click(function(){
        var config_value = $('#pushover_value').val();
        var config_extra = $('#pushover_extra').val();
        var config_userkey = $('#new_userkey').val();
        $.ajax({
            type: "POST",
            url: "ajax_form.php",
            data: {type: "config-item", action: 'add-pushover', config_group: "alerting", config_sub_group: "transports", config_extra: config_extra, config_value: config_value, config_userkey: config_userkey},
            dataType: "json",
            success: function(data){
                if (data.status == 'ok') {
                    pushoverIndex++;
                    var $template = $('#pushover_appkey_template'),
                        $clone    = $template
                            .clone()
                            .removeClass('hide')
                            .attr('id',data.config_id)
                            .attr('pushover-appkey-index', pushoverIndex)
                            .insertBefore($template);
                    $clone.find('[id="pushover_appkey"]').attr('data-config_id',data.config_id);
                    $clone.find('[id="del-pushover-call"]').attr('data-config_id',data.config_id);
                    $clone.find('[name="global-config-input"]').attr('value', config_value);
                    $clone.find('[id="global-config-userkey"]').attr('value', config_userkey);
                    $clone.find('[id="global-config-userkey"]').attr('data-config_id',data.additional_id['userkey']);
                    $clone.find('[id="upd_pushover_extra"]').val(config_extra);
                    $clone.find('[id="upd_pushover_extra"]').attr('data-config_id',data.config_id);
                    $("#new-config-pushover").modal('hide');
                } else {
                    $("#message").html('<div class="alert alert-info">' + data.message + '</div>');
                }
            },
            error: function(){
                $("#message").html('<div class="alert alert-info">Error creating config item</div>');
            }
        });
    });// End Add Pushover config

    // Add Boxcar config
    itemIndex = 0;
    $("button#submit-boxcar").click(function(){
        var config_value = $('#boxcar_value').val();
        var config_extra = $('#boxcar_extra').val();
        $.ajax({
            type: "POST",
            url: "ajax_form.php",
            data: {type: "config-item", action: 'add-boxcar', config_group: "alerting", config_sub_group: "transports", config_extra: config_extra, config_value: config_value},
            dataType: "json",
            success: function(data){
                if (data.status == 'ok') {
                    itemIndex++;
                    var $template = $('#boxcar_appkey_template'),
                        $clone    = $template
                            .clone()
                            .removeClass('hide')
                            .attr('id',data.config_id)
                            .attr('boxcar-appkey-index', itemIndex)
                            .insertBefore($template);
                    $clone.find('[id="boxcar_access_token"]').attr('data-config_id',data.config_id);
                    $clone.find('[id="del-boxcar-call"]').attr('data-config_id',data.config_id);
                    $clone.find('[name="global-config-input"]').attr('value', config_value);
                    $clone.find('[id="upd_boxcar_extra"]').val(config_extra);
                    $clone.find('[id="upd_boxcar_extra"]').attr('data-config_id',data.config_id);
                    $("#new-config-boxcar").modal('hide');
                } else {
                    $("#message").html('<div class="alert alert-info">' + data.message + '</div>');
                }
            },
            error: function(){
                $("#message").html('<div class="alert alert-info">Error creating config item</div>');
            }
        });
    });// End Add Boxcar config

    // Delete api config
    $(document).on('click', 'button[name="del-api-call"]', function(event) {
        var config_id = $(this).data('config_id');
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: {type: "config-item", action: 'remove', config_id: config_id},
            dataType: "json",
            success: function (data) {
                if (data.status == 'ok') {
                    $("#"+config_id).remove();
                } else {
                    $("#message").html('<div class="alert alert-info">' + data.message + '</div>');
                }
            },
            error: function () {
                $("#message").html('<div class="alert alert-info">An error occurred.</div>');
            }
        });
    });// End delete api config

    // Delete slack config
    $(document).on('click', 'button[name="del-slack-call"]', function(event) {
        var config_id = $(this).data('config_id');
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: {type: "config-item", action: 'remove-slack', config_id: config_id},
            dataType: "json",
            success: function (data) {
                if (data.status == 'ok') {
                    $("#"+config_id).remove();
                } else {
                    $("#message").html('<div class="alert alert-info">' + data.message + '</div>');
                }
            },
            error: function () {
                $("#message").html('<div class="alert alert-info">An error occurred.</div>');
            }
        });
    });// End delete slack config

    // Delete hipchat config
    $(document).on('click', 'button[name="del-hipchat-call"]', function(event) {
        var config_id = $(this).data('config_id');
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: {type: "config-item", action: 'remove-hipchat', config_id: config_id},
            dataType: "json",
            success: function (data) {
                if (data.status == 'ok') {
                    $("#"+config_id).remove();
                } else {
                    $("#message").html('<div class="alert alert-info">' + data.message + '</div>');
                }
            },
            error: function () {
                $("#message").html('<div class="alert alert-info">An error occurred.</div>');
            }
        });
    });// End delete hipchat config

    // Delete pushover config
    $(document).on('click', 'button[name="del-pushover-call"]', function(event) {
        var config_id = $(this).data('config_id');
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: {type: "config-item", action: 'remove-pushover', config_id: config_id},
            dataType: "json",
            success: function (data) {
                if (data.status == 'ok') {
                    $("#"+config_id).remove();
                } else {
                    $("#message").html('<div class="alert alert-info">' + data.message + '</div>');
                }
            },
            error: function () {
                $("#message").html('<div class="alert alert-info">An error occurred.</div>');
            }
        });
    });// End delete pushover config

    // Delete Boxcar config
    $(document).on('click', 'button[name="del-boxcar-call"]', function(event) {
        var config_id = $(this).data('config_id');
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: {type: "config-item", action: 'remove-boxcar', config_id: config_id},
            dataType: "json",
            success: function (data) {
                if (data.status == 'ok') {
                    $("#"+config_id).remove();
                } else {
                    $("#message").html('<div class="alert alert-info">' + data.message + '</div>');
                }
            },
            error: function () {
                $("#message").html('<div class="alert alert-info">An error occurred.</div>');
            }
        });
    });// End delete Boxcar config

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
