<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultWidgetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $widgets = [
            [
                'widget_title' => 'Availability map',
                'widget' => 'availability-map',
                'base_dimensions' => '6,3',
            ],
            [
                'widget_title' => 'Device summary horizontal',
                'widget' => 'device-summary-horiz',
                'base_dimensions' => '6,3',
            ],
            [
                'widget_title' => 'Alerts',
                'widget' => 'alerts',
                'base_dimensions' => '6,3',
            ],
            [
                'widget_title' => 'Device summary vertical',
                'widget' => 'device-summary-vert',
                'base_dimensions' => '6,3',
            ],
            [
                'widget_title' => 'Globe map',
                'widget' => 'globe',
                'base_dimensions' => '6,3',
            ],
            [
                'widget_title' => 'Syslog',
                'widget' => 'syslog',
                'base_dimensions' => '6,3',
            ],
            [
                'widget_title' => 'Eventlog',
                'widget' => 'eventlog',
                'base_dimensions' => '6,3',
            ],
            [
                'widget_title' => 'World map',
                'widget' => 'worldmap',
                'base_dimensions' => '6,3',
            ],
            [
                'widget_title' => 'Graylog',
                'widget' => 'graylog',
                'base_dimensions' => '6,3',
            ],
            [
                'widget_title' => 'Graph',
                'widget' => 'generic-graph',
                'base_dimensions' => '6,3',
            ],
            [
                'widget_title' => 'Top Devices',
                'widget' => 'top-devices',
                'base_dimensions' => '6,3',
            ],
            [
                'widget_title' => 'Top Interfaces',
                'widget' => 'top-interfaces',
                'base_dimensions' => '6,3',
            ],
            [
                'widget_title' => 'Notes',
                'widget' => 'notes',
                'base_dimensions' => '6,3',
            ],
            [
                'widget_title' => 'External Images',
                'widget' => 'generic-image',
                'base_dimensions' => '6,3',
            ],
            [
                'widget_title' => 'Component Status',
                'widget' => 'component-status',
                'base_dimensions' => '6,3',
            ],
            [
                'widget_title' => 'Alert History Stats',
                'widget' => 'alertlog-stats',
                'base_dimensions' => '6,3',
            ],
            [
                'widget_title' => 'Server Stats',
                'widget' => 'server-stats',
                'base_dimensions' => '6,3',
            ],
            [
                'widget_title' => 'Alert History',
                'widget' => 'alertlog',
                'base_dimensions' => '6,3',
            ],
            [
                'widget_title' => 'Top Errors',
                'widget' => 'top-errors',
                'base_dimensions' => '6,3',
            ],
        ];

        $existing = DB::table('widgets')->pluck('widget');

        DB::table('widgets')->insert(array_filter($widgets, function ($entry) use ($existing) {
            return ! $existing->contains($entry['widget']);
        }));
    }
}
