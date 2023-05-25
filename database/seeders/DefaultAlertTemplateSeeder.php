<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultAlertTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'BGP Sessions.',
                'template' => '{{ $alert->title }}' . PHP_EOL . 'Severity: {{ $alert->severity }}' . PHP_EOL . '@if ($alert->state == 0)Time elapsed: {{ $alert->elapsed }} @endif' . PHP_EOL . 'Timestamp: {{ $alert->timestamp }}' . PHP_EOL . 'Unique-ID: {{ $alert->uid }}' . PHP_EOL . 'Rule: @if ($alert->name) {{ $alert->name }} @else {{ $alert->rule }} @endif' . PHP_EOL . '@if ($alert->faults) Faults:' . PHP_EOL . '@foreach ($alert->faults as $key => $value)' . PHP_EOL . '  #{{ $key }}: {{ $value[\'string\'] }}' . PHP_EOL . '  Peer: {{ $value[\'astext\'] }}' . PHP_EOL . '  Peer IP: {{ $value[\'bgpPeerIdentifier\'] }}' . PHP_EOL . '  Peer AS: {{ $value[\'bgpPeerRemoteAs\'] }}' . PHP_EOL . '  Peer EstTime: {{ $value[\'bgpPeerFsmEstablishedTime\'] }}' . PHP_EOL . '  Peer State: {{ $value[\'bgpPeerState\'] }}' . PHP_EOL . '@endforeach' . PHP_EOL . '@endif',
                'title' => '',
                'title_rec' => '',
            ],
            [
                'name' => 'Ports',
                'template' => '{{ $alert->title }}' . PHP_EOL . 'Severity: {{ $alert->severity }}' . PHP_EOL . '@if ($alert->state == 0)Time elapsed: {{ $alert->elapsed }} @endif' . PHP_EOL . 'Timestamp: {{ $alert->timestamp }}' . PHP_EOL . 'Unique-ID: {{ $alert->uid }}' . PHP_EOL . 'Rule: @if ($alert->name) {{ $alert->name }} @else {{ $alert->rule }} @endif' . PHP_EOL . '@if ($alert->faults) Faults:' . PHP_EOL . '@foreach ($alert->faults as $key => $value)' . PHP_EOL . '  #{{ $key }}: {{ $value[\'string\'] }}' . PHP_EOL . '  Port: {{ $value[\'ifName\'] }}' . PHP_EOL . '  Port Name: {{ $value[\'ifAlias\'] }}' . PHP_EOL . '  Port Status: {{ $value[\'message\'] }}' . PHP_EOL . '@endforeach' . PHP_EOL . '@endif',
                'title' => '',
                'title_rec' => '',
            ],
            [
                'name' => 'Sensors',
                'template' => <<<'EOD'
                    {{ $alert->title }}

                    Device Name: {{ $alert->hostname }}
                    Severity: {{ $alert->severity }}
                    Timestamp: {{ $alert->timestamp }}
                    Uptime: {{ $alert->uptime_short }}
                    @if ($alert->state == 0)
                    Time elapsed: {{ $alert->elapsed }}
                    @endif
                    Location: {{ $alert->location }}
                    Description: {{ $alert->description }}
                    Features: {{ $alert->features }}
                    Notes: {{ $alert->notes }}

                    Rule: {{ $alert->name ?? $alert->rule }}
                    @if ($alert->faults)
                    Faults:
                    @foreach ($alert->faults as $key => $value)
                    @php($unit = __("sensors.${value["sensor_class"]}.unit"))
                    #{{ $key }}: {{ $value['sensor_descr'] ?? 'Sensor' }}

                    Current: {{ $value['sensor_current'].$unit }}
                    Previous: {{ $value['sensor_prev'].$unit }}
                    Limit: {{ $value['sensor_limit'].$unit }}
                    Over Limit: {{ round($value['sensor_current']-$value['sensor_limit'], 2).$unit }}

                    @endforeach
                    @endif
                    EOD,
                'title' => '',
                'title_rec' => '',
            ],
            [
                'name' => 'Default Alert Template',
                'template' => '{{ $alert->title }}' . PHP_EOL . 'Severity: {{ $alert->severity }}' . PHP_EOL . '@if ($alert->state == 0)Time elapsed: {{ $alert->elapsed }} @endif' . PHP_EOL . 'Timestamp: {{ $alert->timestamp }}' . PHP_EOL . 'Unique-ID: {{ $alert->uid }}' . PHP_EOL . 'Rule: @if ($alert->name) {{ $alert->name }} @else {{ $alert->rule }} @endif' . PHP_EOL . '@if ($alert->faults) Faults:' . PHP_EOL . '@foreach ($alert->faults as $key => $value)' . PHP_EOL . '  #{{ $key }}: {{ $value[\'string\'] }}' . PHP_EOL . '@endforeach' . PHP_EOL . '@endif' . PHP_EOL . 'Alert sent to:' . PHP_EOL . '@foreach ($alert->contacts as $key => $value)' . PHP_EOL . '  {{ $value }} <{{ $key }}>' . PHP_EOL . '@endforeach',
                'title' => null,
                'title_rec' => null,
            ],
        ];

        $existing = DB::table('alert_templates')->pluck('name');

        DB::table('alert_templates')->insert(array_filter($templates, function ($entry) use ($existing) {
            return ! $existing->contains($entry['name']);
        }));
    }
}
