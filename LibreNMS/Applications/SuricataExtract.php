<?php

namespace LibreNMS\Applications;

class SuricataExtract extends ApplicationDefinition
{
    public const string TYPE = 'suricata_extract';
    protected const ?string DOCS = 'Suricata Extract';

    protected const ?array OVERVIEW = [
        'errors',
        'ignored_host',
        'ignored_ip',
        'ignored_ip_dest',
        'ignored_ip_src',
        'sub',
        'sub_2xx',
        'sub_3xx',
        'sub_4xx',
        'sub_5xx',
        'sub_codes',
        'sub_fail',
        'truncated',
        'zero_sized',
        'sub_size',
    ];

    protected const array GRAPHS = [
        'sub' => 'Submission',
        'ignored_host' => 'Ignored By Host',
        'ignored_ip' => 'Ignored By IP',
        'ignored_ip_src' => 'Ignored By IP Source',
        'ignored_ip_dest' => 'Ignored By IP Destination',
        'sub_fail' => 'Submission Failure',
        'errors' => 'Errors',
        'truncated' => 'File Truncated',
        'zero_sized' => 'File Zero Sized',
        'sub_size' => 'Total Size Of Submissions',
        'sub_codes' => 'HTTP Submission Result Codes',
        'sub_2xx' => 'HTTP Submission Result Code, 2xx',
        'sub_3xx' => 'HTTP Submission Result Code, 3xx',
        'sub_4xx' => 'HTTP Submission Result Code, 4xx',
        'sub_5xx' => 'HTTP Submission Result Code, 5xx',
    ];
}
