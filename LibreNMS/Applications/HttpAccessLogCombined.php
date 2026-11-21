<?php

namespace LibreNMS\Applications;

use App\Models\Application;

class HttpAccessLogCombined extends ApplicationDefinition
{
    public const string TYPE = 'http_access_log_combined';
    protected const ?string DOCS = 'HTTP Access Log Combined';
    protected const ?string NAME = 'HTTP Access Log Combined';

    protected const ?array OVERVIEW = [
        'bytes',
        'codes_general',
        'codes_1xx',
        'codes_2xx',
        'codes_3xx',
        'codes_4xx',
        'codes_5xx',
        'methods',
        'version',
        'refer',
        'user',
        'log_size',
        'error_size',
    ];

    protected const array SECTIONS = [
        'bytes' => 'Bytes',
        'method' => 'Method',
        'version' => 'Version',
        'log_size' => 'Log Size',
        'refer' => 'Refer',
        'user' => 'User',
        '1xx' => '1xx',
        '2xx' => '2xx',
        '3xx' => '3xx',
        '4xx' => '4xx',
        '5xx' => '5xx',
    ];

    protected const array STATS = [
        'bytes' => [
            'bytes' => 'Bytes',
            'bytes_max' => 'Bytes, Max',
            'bytes_mean' => 'Bytes, Mean',
            'bytes_median' => 'Bytes, Median',
            'bytes_mode' => 'Bytes, Mode',
            'bytes_min' => 'Bytes, Min',
            'bytes_range' => 'Bytes, Range',
        ],
        'method' => [
            'CONNECT' => 'CONNECT',
            'DELETE' => 'DELETE',
            'GET' => 'GET',
            'HEAD' => 'HEAD',
            'OPTIONS' => 'OPTIONS',
            'PATCH' => 'PATCH',
            'POST' => 'POST',
            'PUT' => 'PUT',
        ],
        'version' => [
            'http1_0' => 'HTTP/1.0',
            'http1_1' => 'HTTP/1.1',
            'http2' => 'HTTP/2',
            'http3' => 'HTTP/3',
        ],
        'log_size' => [
            'size' => 'Log Size',
            'error_size' => 'Error Log Size',
        ],
        'refer' => [
            'refer' => 'Refer Set, not "-"',
            'no_refer' => 'No Refer Set, "-"',
        ],
        'user' => [
            'user' => 'User Set, not "-"',
            'no_user' => 'No User Set, "-"',
        ],
        '1xx' => [
            '1xx' => 'Any 1xx Response Code',
            '100' => '100: Continue',
            '101' => '101: Switching Protocols',
            '102' => '102: Processing (WebDAV; RFC 2518)',
            '103' => '103: Early Hints (RFC 8297)',
        ],
        '2xx' => [
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
        ],
        '3xx' => [
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
        ],
        '4xx' => [
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
        ],
        '5xx' => [
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
        ],
    ];

    protected const array GRAPHS = [
        'bytes' => 'Bytes, Total',
        'bytes_stats' => 'Bytes, Stats',
        'codes_general' => 'Response Status Codes, General',
        'codes_1xx' => 'Response Status Codes, 1xx',
        'codes_2xx' => 'Response Status Codes, 2xx',
        'codes_3xx' => 'Response Status Codes, 3xx',
        'codes_4xx' => 'Response Status Codes, 4xx',
        'codes_5xx' => 'Response Status Codes, 5xx',
        'methods' => 'Request Methods',
        'version' => 'HTTP Version',
        'refer' => 'Refer Present/Not Present',
        'user' => 'User Present/Not Present',
        'log_size' => 'Access Log File Size',
        'error_size' => 'Error Log File Size',
    ];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        $logs = $application->data['logs'] ?? [];
        $log = $page->option('log');
        $section = $page->option('access_log_page');
        // detail graphs always cover every set, so a detail page and a set are never selected together
        $page->addFilter('Details', 'access_log_page', ['' => 'Totals'] + self::SECTIONS, $section === null && $log !== null ? false : $section);
        $page->addFilter('Sets', 'log', array_combine($logs, $logs), $section === null ? $log : false);

        if ($section !== null && isset(self::STATS[$section])) {
            foreach (self::STATS[$section] as $stat => $title) {
                $page->addGraphs(['stat' => $title], ['log_stat' => $stat]);
            }

            return;
        }

        $page->addGraphs(self::GRAPHS, ['log' => $log]);
    }
}
