<?php

namespace LibreNMS\Applications;

use App\Models\Application;

class Privoxy extends ApplicationDefinition
{
    public const string TYPE = 'privoxy';
    protected const ?string DOCS = 'Privoxy';

    protected const ?array OVERVIEW = [
        'client_requests',
        'blocks',
        'crunches',
        'unique_bdomains',
        'bytes_to_client',
        'req',
        'resp_xxx',
        'ver',
    ];

    protected const array SECTIONS = [
        'general' => 'General',
        'blocks' => 'Blocks',
        'conn' => 'Connections',
        'domains' => 'Domains',
        'http_type' => 'HTTP Req Type',
        'http_resp' => 'HTTP Response Codes',
        'http_ver' => 'HTTP Version',
    ];

    protected const array GRAPH_SETS = [
        'general' => [
            'client_requests' => 'Client Requests',
            'blocks' => 'Blocked Requests',
            'block_percent' => 'Blocked Requests, Percent',
            'crunches' => 'Crunches',
            'unique_domains' => 'Unique Domains',
            'unique_bdomains' => 'Unique Domains Blocked',
            'ubd_per' => 'Unique Domains Blocked, Percent',
            'bytes_to_client' => 'Bytes Sent To Clients',
            'imp_accounted' => 'Improperly Accounted',
        ],
        'conn' => [
            'nog_conns' => 'New Outgoing Requests',
            'reused_server_cons' => 'Reused Server Connections',
            'max_reqs' => 'Max Requests Per TCP Session',
            'out_requests' => 'Out Going Requests',
            'bytes_to_client' => 'Bytes To Client',
        ],
        'blocks' => [
            'blocks' => 'Blocked Requests',
            'block_percent' => 'Blocked Requests, Percent',
            'crunches' => 'Crunches',
            'unique_bdomains' => 'Unique Domains, Blocked',
            'unique_bdomains_np' => 'Unique Domains Without Port, Blocked',
        ],
        'http_ver' => [
            'ver' => 'HTTP Versions',
            'ver_1_0' => 'HTTP/1.0',
            'ver_1_1' => 'HTTP/1.1',
            'ver_2' => 'HTTP/2',
            'ver_3' => 'HTTP/3',
        ],
        'http_type' => [
            'req' => 'HTTP Request Types',
            'req_connect' => 'CONNECT',
            'req_delete' => 'DELETE',
            'req_get' => 'GET',
            'req_head' => 'HEAD',
            'req_options' => 'OPTIONS',
            'req_patch' => 'PATCH',
            'req_post' => 'POST',
            'req_put' => 'PUT',
            'req_trace' => 'TRACE',
        ],
        'http_resp' => [
            'resp_xxx' => 'Response Code Types',
            'resp_1xx' => '1xx',
            'resp_2xx' => '2xx',
            'resp_200' => '200',
            'resp_2xx_other' => '2xx Other',
            'resp_3xx' => '3xx',
            'resp_301' => '301',
            'resp_302' => '302',
            'resp_303' => '303',
            'resp_3xx_other' => '3xx Other',
            'resp_4xx' => '4xx',
            'resp_403' => '403',
            'resp_404' => '404',
            'resp_451' => '451',
            'resp_4xx_other' => '4xx Other',
            'resp_5xx' => '5xx',
            'resp_500' => '500',
            'resp_502' => '502',
            'resp_503' => '503',
            'resp_504' => '504',
            'resp_5xx_other' => '5xx Other',
        ],
        'domains' => [
            'unique_domains' => 'Unique Domains',
            'unique_bdomains' => 'Unique Domains, Blocked',
            'unique_domains_np' => 'Unique Domains Without Port',
            'unique_bdomains_np' => 'Unique Domains Without Port, Blocked',
            'ubd_per' => 'Percent Of Unique Domains Blocked',
            'ubd_np_per' => 'Percent Of Unique Domains Blocked, No Port',
        ],
    ];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        $section = $page->option('privoxyPage');
        if ($section === null || ! isset(self::SECTIONS[$section])) {
            $section = 'general';
        }
        $page->addFilter('Graphs', 'privoxyPage', self::SECTIONS, $section);
        $page->addGraphs(self::GRAPH_SETS[$section]);
    }
}
