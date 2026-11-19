<?php

namespace LibreNMS\Applications;

use App\Facades\Rrd;
use App\Models\Application;

class Shoutcast extends ApplicationDefinition
{
    public const string TYPE = 'shoutcast';

    protected const ?array OVERVIEW = [];

    protected const array GRAPHS = [
        'multi_bits' => 'Traffic Statistics - Total of all Shoutcast servers',
        'multi_stats' => 'Shoutcast Statistics - Total of all Shoutcast servers',
    ];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        parent::buildDevicePage($page, $application);
        $files = Rrd::getRrdFiles($application->device->hostname, ['app', self::TYPE, (string) $application->app_id]);
        foreach ($files as $file) {
            $hostname = basename($file, '.rrd');
            $hostname = substr($hostname, strrpos($hostname, '-') + 1);
            if (! str_contains($hostname, '_')) {
                continue;
            }

            [$host, $port] = explode('_', $hostname, 2);
            $page->addGraphs([
                'bits' => "Traffic Statistics - $host (Port: $port)",
                'stats' => "Shoutcast Statistics - $host (Port: $port)",
            ], ['hostname' => $hostname]);
        }
    }
}
