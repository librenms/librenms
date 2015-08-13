<?php

$sql  = "SELECT *, DATE_FORMAT(timestamp, '".$config['dateformat']['mysql']['compact']."') AS date from syslog ORDER BY timestamp DESC LIMIT 20";

$syslog_output = '
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default panel-condensed">
                <div class="panel-heading">
                    <strong>Syslog entries</strong>
                </div>
                <table class="table table-hover table-condensed table-striped">';

foreach (dbFetchRows($sql) as $entry) {
    $entry = array_merge($entry, device_by_id_cache($entry['device_id']));
    include 'includes/print-syslog.inc.php';
}

$syslog_output .= '
                </table>
            </div>
        </div>
    </div>
</div>
';

$common_output[] = $syslog_output;
