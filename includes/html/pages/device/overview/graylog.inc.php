<?php

if (\LibreNMS\Config::get('graylog.server')) {
    echo
        '<div class="row" id="graylog-card">'.
        '  <div class="col-md-12">'.
        '    <div class="panel panel-default panel-condensed">'.
        '      <div class="panel-heading">'.
        '        <a href="device/device='.$device['device_id'].
        '/tab=logs/section=syslog/"><i class="fa fa-clone fa-lg icon-theme"'.
        ' aria-hidden="true"></i> <strong>Recent Graylog</strong></a>'.
        '      </div>'.
        '      <table class="table table-hover table-condensed table-striped">';

    $filter_device = $device["device_id"];
    $no_form = true;
    require_once 'includes/html/print-graylog.inc.php';
    echo implode('', $common_output);
    unset($no_form);
    echo
        '      </table>'.
        '    </div>'.
        '  </div>'.
        '</div>';
}
