<?php

$name = 'http_access_log_combined';

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'http_access_log_combined',
];

$app_data = $app->data;

if (isset($vars['access_log_page'])) {
    $vars['access_log_page'] = htmlspecialchars($vars['access_log_page']);
}

print_optionbar_start();

// print the link to the totals
$label = (isset($vars['access_log_page']) || isset($vars['log']))
    ? 'Totals'
    : '<span class="pagemenu-selected">Totals</span>';
echo generate_link($label, $link_array);

echo ' | Details(';

$label = (! isset($vars['access_log_page']) || $vars['access_log_page'] != 'bytes')
    ? 'Bytes'
    : '<span class="pagemenu-selected">Bytes</span>';
echo generate_link($label, $link_array, ['access_log_page' => 'bytes']) . ',';

$label = (! isset($vars['access_log_page']) || $vars['access_log_page'] != 'method')
    ? 'Method'
    : '<span class="pagemenu-selected">Method</span>';
echo generate_link($label, $link_array, ['access_log_page' => 'method']) . ',';

$label = (! isset($vars['access_log_page']) || $vars['access_log_page'] != 'version')
    ? 'Version'
    : '<span class="pagemenu-selected">Version</span>';
echo generate_link($label, $link_array, ['access_log_page' => 'version']) . ',';

$label = (! isset($vars['access_log_page']) || $vars['access_log_page'] != 'log_size')
    ? 'Log Size'
    : '<span class="pagemenu-selected">Log Size</span>';
echo generate_link($label, $link_array, ['access_log_page' => 'log_size']) . ',';

$label = (! isset($vars['access_log_page']) || $vars['access_log_page'] != 'refer')
    ? 'Refer'
    : '<span class="pagemenu-selected">Refer</span>';
echo generate_link($label, $link_array, ['access_log_page' => 'refer']) . ',';

$label = (! isset($vars['access_log_page']) || $vars['access_log_page'] != 'user')
    ? 'User'
    : '<span class="pagemenu-selected">User</span>';
echo generate_link($label, $link_array, ['access_log_page' => 'user']) . ',';

$label = (! isset($vars['access_log_page']) || $vars['access_log_page'] != '1xx')
    ? '1xx'
    : '<span class="pagemenu-selected">1xx</span>';
echo generate_link($label, $link_array, ['access_log_page' => '1xx']) . ',';

$label = (! isset($vars['access_log_page']) || $vars['access_log_page'] != '2xx')
    ? '2xx'
    : '<span class="pagemenu-selected">2xx</span>';
echo generate_link($label, $link_array, ['access_log_page' => '2xx']) . ',';

$label = (! isset($vars['access_log_page']) || $vars['access_log_page'] != '3xx')
    ? '3xx'
    : '<span class="pagemenu-selected">2xx</span>';
echo generate_link($label, $link_array, ['access_log_page' => '3xx']) . ',';

$label = (! isset($vars['access_log_page']) || $vars['access_log_page'] != '4xx')
    ? '4xx'
    : '<span class="pagemenu-selected">4xx</span>';
echo generate_link($label, $link_array, ['access_log_page' => '4xx']) . ',';

$label = (! isset($vars['access_log_page']) || $vars['access_log_page'] != '5xx')
    ? '5xx'
    : '<span class="pagemenu-selected">5xx</span>';
echo generate_link($label, $link_array, ['access_log_page' => '5xx']);

echo ') | Sets: ';

$index_int = 0;
foreach ($app_data['logs'] as $index => $log_name) {
    $log_name = htmlspecialchars($log_name);
    $label = (isset($vars['access_log_page']) || $vars['log'] != $log_name)
        ? $log_name
        : '<span class="pagemenu-selected">' . $log_name . '</span>';
    $index_int++;
    echo generate_link($label, $link_array, ['log' => $log_name]);
    if (isset($app_data['logs'][$index_int])) {
        echo ', ';
    }
}

print_optionbar_end();

if (isset($vars['access_log_page']) && $vars['access_log_page'] == 'bytes') {
    $graphs = [];
    $stats = [
        'bytes' => 'Bytes',
        'bytes_max' => 'Bytes, Max',
        'bytes_mean' => 'Bytes, Mean',
        'bytes_median' => 'Bytes, Median',
        'bytes_mode' => 'Bytes, Mode',
        'bytes_min' => 'Bytes, Min',
        'bytes_range' => 'Bytes, Range',
    ];
    foreach ($stats as $key => $val) {
        $graphs[] = [
            'type' => 'stat',
            'description' => $val,
            'stat' => $key,
        ];
    }
} elseif (isset($vars['access_log_page']) && $vars['access_log_page'] == 'method') {
    $graphs = [];
    $stats = [
        'CONNECT',
        'DELETE',
        'GET',
        'HEAD',
        'OPTIONS',
        'PATCH',
        'POST',
        'PUT',
    ];
    foreach ($stats as $key => $val) {
        $graphs[] = [
            'type' => 'stat',
            'description' => $val,
            'stat' => $val,
        ];
    }
} elseif (isset($vars['access_log_page']) && $vars['access_log_page'] == 'version') {
    $graphs = [];
    $stats = [
        'http1_0' => 'HTTP/1.0',
        'http1_1' => 'HTTP/1.1',
        'http2' => 'HTTP/2',
        'http3' => 'HTTP/3',
    ];
    foreach ($stats as $key => $val) {
        $graphs[] = [
            'type' => 'stat',
            'description' => $val,
            'stat' => $key,
        ];
    }
} elseif (isset($vars['access_log_page']) && $vars['access_log_page'] == 'log_size') {
    $graphs = [];
    $stats = [
        'size' => 'Log Size',
        'error_size' => 'Error Log Size',
    ];
    foreach ($stats as $key => $val) {
        $graphs[] = [
            'type' => 'stat',
            'description' => $val,
            'stat' => $key,
        ];
    }
} elseif (isset($vars['access_log_page']) && $vars['access_log_page'] == 'refer') {
    $graphs = [];
    $stats = [
        'refer' => 'Refer Set, not "-"',
        'no_refer' => 'No Refer Set, "-"',
    ];
    foreach ($stats as $key => $val) {
        $graphs[] = [
            'type' => 'stat',
            'description' => $val,
            'stat' => $key,
        ];
    }
} elseif (isset($vars['access_log_page']) && $vars['access_log_page'] == 'user') {
    $graphs = [];
    $stats = [
        'user' => 'User Set, not "-"',
        'no_user' => 'No User Set, "-"',
    ];
    foreach ($stats as $key => $val) {
        $graphs[] = [
            'type' => 'stat',
            'description' => $val,
            'stat' => $key,
        ];
    }
} elseif (isset($vars['access_log_page']) && $vars['access_log_page'] == '1xx') {
    $graphs = [];
    $stats = [
        '1xx' => 'Any 1xx Response Code',
        '100' => '100: Continue',
        '101' => '101: Switching Protocols',
        '102' => '102: Processing (WebDAV; RFC 2518)',
        '103' => '103: Early Hints (RFC 8297)',
    ];
    foreach ($stats as $key => $val) {
        $graphs[] = [
            'type' => 'stat',
            'description' => $val,
            'stat' => $key,
        ];
    }
} elseif (isset($vars['access_log_page']) && $vars['access_log_page'] == '2xx') {
    $graphs = [];
    $stats = [
        '2xx' => 'Any 2xx Response Code',
        '200' => '200: OK',
        '201' => '201: Created',
        '202' => '202: Accepted',
        '203' => '203: Non-Authoritative Information (since HTTP/1.1)',
        '204' => '204: No Content',
        '205' => '205: Reset Content',
        '206' => '206: Partial Content',
        '207' => '207: Multi-Status (WebDAV; RFC 4918)',
        '208' => '208: Already Reported (WebDAV; RFC 5842)',
        '218' => '218: This is fine (Apache HTTP Server)',
        '226' => '226 IM Used (RFC 3229)',
    ];
    foreach ($stats as $key => $val) {
        $graphs[] = [
            'type' => 'stat',
            'description' => $val,
            'stat' => $key,
        ];
    }
} elseif (isset($vars['access_log_page']) && $vars['access_log_page'] == '3xx') {
    $graphs = [];
    $stats = [
        '3xx' => 'Any 3xx Response Code',
        '300' => '300: Multiple Choices',
        '301' => '301: Moved Permanently',
        '302' => '302: Found (Previously "Moved temporarily")',
        '303' => '303: See Other (since HTTP/1.1)',
        '304' => '304: Not Modified',
        '305' => '305: Use Proxy (since HTTP/1.1)',
        '306' => '306 Switch Proxy',
        '307' => '307 Temporary Redirect (since HTTP/1.1)',
        '308' => '308 Permanent Redirect',
    ];
    foreach ($stats as $key => $val) {
        $graphs[] = [
            'type' => 'stat',
            'description' => $val,
            'stat' => $key,
        ];
    }
} elseif (isset($vars['access_log_page']) && $vars['access_log_page'] == '4xx') {
    $graphs = [];
    $stats = [
        '4xx' => 'Any 4xx Response Code',
        '400' => '400: Bad Request',
        '401' => '401: Unauthorized',
        '402' => '402: Payment Required',
        '403' => '403: Forbidden',
        '404' => '404: Not Found',
        '405' => '405: Method Not Allowed',
        '406' => '406: Not Acceptable',
        '407' => '407: Proxy Authentication Required',
        '408' => '408: Request Timeout',
        '409' => '409: Conflict',
        '410' => '410: Gone',
        '411' => '411: Length Required',
        '412' => '412: Precondition Failed',
        '413' => '413: Payload Too Large',
        '414' => '414: URI Too Long',
        '415' => '415: Unsupported Media Type',
        '416' => '416: Range Not Satisfiable',
        '417' => '417: Expectation Failed',
        '419' => '419: Page Expired (Laravel Framework)',
        '420' => '420: Method Failure (Spring Framework)',
        '421' => '421: Misdirected Request',
        '422' => '422: Unprocessable Content',
        '423' => '423: Locked (WebDAV; RFC 4918)',
        '424' => '424: Failed Dependency (WebDAV; RFC 4918)',
        '425' => '425: Too Early (RFC 8470)',
        '426' => '426: Upgrade Required',
        '428' => '428: Precondition Required (RFC 6585)                    ',
        '429' => '429: Too Many Requests (RFC 6585)',
        '431' => '431: Request Header Fields Too Large (RFC 6585)',
        '444' => '444: nginx No Response',
        '451' => '451: Unavailable For Legal Reasons (RFC 7725)',
        '494' => '494: nginx Request header too large',
        '495' => '495: nginx SSL Certificate Error',
        '496' => '496: nginx SSL Certificate Required',
        '497' => '497: nginx HTTP Request Sent to HTTPS Port',
        '499' => '499: nginx Client Closed Request',
    ];
    foreach ($stats as $key => $val) {
        $graphs[] = [
            'type' => 'stat',
            'description' => $val,
            'stat' => $key,
        ];
    }
} elseif (isset($vars['access_log_page']) && $vars['access_log_page'] == '5xx') {
    $graphs = [];
    $stats = [
        '5xx' => 'Any 5xx Response Code',
        '500' => '500: Internal Server Error',
        '501' => '501: Not Implemented',
        '502' => '502: Bad Gateway',
        '503' => '503: Service Unavailable',
        '504' => '504: Gateway Timeout',
        '505' => '505: HTTP Version Not Supported',
        '506' => '506: Variant Also Negotiates (RFC 2295)',
        '507' => '507: Insufficient Storage (WebDAV; RFC 4918)',
        '508' => '508: Loop Detected (WebDAV; RFC 5842)',
        '509' => '509: Bandwidth Limit Exceeded (Apache Web Server/cPanel)',
        '510' => '510: Not Extended (RFC 2774)',
        '511' => '511: Network Authentication Required (RFC 6585)',
    ];
    foreach ($stats as $key => $val) {
        $graphs[] = [
            'type' => 'stat',
            'description' => $val,
            'stat' => $key,
        ];
    }
} else {
    $graphs = [
        [
            'type' => 'bytes',
            'description' => 'Bytes, Total',
        ],
        [
            'type' => 'bytes_stats',
            'description' => 'Bytes, Stats',
        ],
        [
            'type' => 'codes_general',
            'description' => 'Response Status Codes, General',
        ],
        [
            'type' => 'codes_1xx',
            'description' => 'Response Status Codes, 1xx',
        ],
        [
            'type' => 'codes_2xx',
            'description' => 'Response Status Codes, 2xx',
        ],
        [
            'type' => 'codes_3xx',
            'description' => 'Response Status Codes, 3xx',
        ],
        [
            'type' => 'codes_4xx',
            'description' => 'Response Status Codes, 4xx',
        ],
        [
            'type' => 'codes_5xx',
            'description' => 'Response Status Codes, 5xx',
        ],
        [
            'type' => 'methods',
            'description' => 'Request Methods',
        ],
        [
            'type' => 'version',
            'description' => 'HTTP Version',
        ],
        [
            'type' => 'refer',
            'description' => 'Refer Present/Not Present',
        ],
        [
            'type' => 'user',
            'description' => 'User Present/Not Present',
        ],
        [
            'type' => 'log_size',
            'description' => 'Access Log File Size',
        ],
        [
            'type' => 'error_size',
            'description' => 'Error Log File Size',
        ],
    ];
}

foreach ($graphs as $key => $graph_info) {
    $graph_type = $graph_info['type'];
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = time();
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $name . '_' . $graph_info['type'];
    if (isset($vars['log'])) {
        $graph_array['log'] = $vars['log'];
    }
    if (isset($graph_info['stat'])) {
        $graph_array['log_stat'] = $graph_info['stat'];
    }

    echo '<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">' . $graph_info['description'] . '</h3>
    </div>
    <div class="panel-body">
    <div class="row">';
    include 'includes/html/print-graphrow.inc.php';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
