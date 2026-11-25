<?php

/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage nfs-server
 * @link       https://www.librenms.org
 * @copyright  2017 LibreNMS
 * @author     SvennD <svennd@svennd.be>
*/

namespace LibreNMS\Applications;

use App\Facades\Rrd;
use App\Models\Application;

class NfsServer extends ApplicationDefinition
{
    public const string TYPE = 'nfs-server';
    protected const ?string DOCS = 'NFS Linux Server';
    protected const ?string NAME = 'NFS Server';

    protected const ?array OVERVIEW = [
        'io',
        'net_tcp_conns',
        'rpc',
    ];

    protected const array SECTIONS = [
        'default' => 'NFS',
        'proc2' => 'NFS v2',
        'proc3' => 'NFS v3',
        'proc4' => 'NFS v4',
    ];

    protected const array GRAPH_SETS = [
        'default' => [
            'net' => 'Network stats',
            'rpc' => 'RPC Stats',
            'io' => 'IO',
            'fh' => 'File handler',
            'rc' => 'Reply cache',
            'ra' => 'Read ahead cache',
        ],
        'proc2' => [
            'stats_v2' => 'NFS v2 Statistics',
        ],
        'proc3' => [
            'stats_io' => 'NFS v3 Read/Write operations',
            'stats_read' => 'NFS v3 other read operations',
            'stats_write' => 'NFS v3 other write operations',
        ],
        'proc4' => [
            'stats_v4' => 'NFS v4 Statistics',
            'v4ops' => 'NFS v4ops Statistics',
        ],
    ];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        $sections = array_filter(self::SECTIONS, fn (string $section) => $this->hasRrd($application, $section), ARRAY_FILTER_USE_KEY);
        $section = $page->option('app_section');
        if ($section === null || ! isset($sections[$section])) {
            $section = array_key_first($sections);
        }
        $page->addFilter('NFS Server', 'app_section', $sections, $section);

        $graphs = $section === null ? [] : self::GRAPH_SETS[$section];
        if (isset($graphs['v4ops']) && ! $this->hasRrd($application, 'proc4ops')) {
            unset($graphs['v4ops']);
        }
        $page->addGraphs($graphs);
    }

    private function hasRrd(Application $application, string $section): bool
    {
        $name = Rrd::name($application->device->hostname, ['app', self::TYPE, $section, $application->app_id]);

        return Rrd::checkRrdExists($name);
    }
}
