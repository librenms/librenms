<?php

use LibreNMS\Config;

if (Config::get('graylog.server')) {
    echo '
        <div class="row" id="graylog-card">
            <div class="col-md-12">
                <div class="panel panel-default panel-condensed">
                    <div class="panel-heading">
                        <a href="device/device=' . $device['device_id'] . '/tab=logs/section=graylog/">
                            <i class="fa fa-clone fa-lg icon-theme"
                            aria-hidden="true"></i>
                            <strong>Recent Graylog</strong>
                        </a>
                    </div>
                    <table class="table table-hover table-condensed table-striped">';

    $filter_device = $device['device_id'];
    $tmp_output = '
        <div class="table-responsive">
        <table id="graylog" class="table table-hover table-condensed table-striped">
            <thead>
                <tr>
                <th data-column-id="severity"></th>
                <th data-column-id="timestamp">Timestamp</th>
                <th data-column-id="level">Level</th>
                <th data-column-id="message">Message</th>
                <th data-column-id="facility">Facility</th>
                </tr>
            </thead>
        </table>
    </div>
    <script>
    ';
    $rowCount = Config::get('graylog.device-page.rowCount', 10);
    $loglevel = Config::get('graylog.device-page.loglevel', 7);
    $tmp_output .= '
        $.ajax({
            type: "post",
            data: {
                device: "' . (isset($filter_device) ? $filter_device : '') . '",
                ' . ($rowCount ? 'rowCount: ' . $rowCount . ',' : '') . '
                ' . ($loglevel ? 'loglevel: ' . $loglevel . ',' : '') . '
            },
            url: "' . url('/ajax/table/graylog') . '",
            success: function(data){
                if (data.rowCount == 0) {
                    $("#graylog-card").remove();
                    return;
                }
                var html = "<tbody>";
                $("#graylog").append("<tbody></tbody>");
                $.each(data.rows, function(i,v){
                    html = html + "<tr><td>"+v.severity+"</td><td>"+
                        v.timestamp+"</td><td>"+v.level+"</td><td>"+
                        v.message+"</td><td>"+v.facility+"</td></tr>";
                });
                html = html + "</tbody>";
                $("#graylog").append(html);
            }
        });
        </script>
    ';
    $common_output[] = $tmp_output;
    echo implode('', $common_output);
    echo '
                    </table>
                </div>
            </div>
        </div>';
}
