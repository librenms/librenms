<?php

use App\Facades\LibrenmsConfig;

if (LibrenmsConfig::get('graylog.server')) {
    echo '
        <div class="overview-panel tw:mb-5" id="graylog-card">
                    <div class="tw:px-4 tw:py-2.5 tw:bg-neutral-100 tw:border-b tw:border-gray-300 tw:text-neutral-700 tw:dark:bg-dark-gray-200 tw:dark:border-zinc-800 tw:dark:text-dark-white-200">
                        <a href="' . route('device.graylog', ['device' => $device['device_id']]) . '">
                            <i class="fa fa-clone fa-lg icon-theme"
                            aria-hidden="true"></i>
                            <strong>Recent Graylog</strong>
                        </a>
                    </div>
                    <div class="tw:flex tw:flex-col tw:bg-white tw:divide-y tw:divide-gray-300 tw:dark:bg-dark-gray-400 tw:dark:divide-zinc-800">';

    $filter_device = $device['device_id'];
    $tmp_output = '
        <div class="table-responsive">
        <table id="graylog" class="table table-hover table-striped">
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
    $rowCount = LibrenmsConfig::get('graylog.rowCount', 10);
    $loglevel = LibrenmsConfig::get('graylog.loglevel', 7);
    $tmp_output .= '
        $.ajax({
            type: "post",
            data: {
                device: "' . ($filter_device ?? '') . '",
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
                    </div>
                </div>';
}
