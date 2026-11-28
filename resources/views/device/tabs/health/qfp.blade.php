@php
    use LibreNMS\Enum\Severity;
    use LibreNMS\Util\Number;
@endphp
@foreach($items as $id => $qfp)
    @php
        $memory = Number::calculatePercent((int) $qfp['memory_used'], (int) $qfp['memory_total']);
        $graphs = [
            'qfp_util' => [__('QFP Utilization'), $qfp['utilization'] . ' %', $qfp['utilization'] < 50 ? Severity::Ok : ($qfp['utilization'] < 75 ? Severity::Warning : Severity::Error)],
            'qfp_relativeutil' => [__('QFP Relative utilization per kpps'), null, null],
            'qfp_packets' => [__('QFP packets'), Number::formatBi($qfp['packets'], 2, 0, 'pps'), Severity::Unknown],
            'qfp_throughput' => [__('QFP Throughput'), Number::formatBi($qfp['throughput'], 2, 0, 'bps'), Severity::Unknown],
            'qfp_avgpktsize' => [__('QFP Average packet size'), ceil((float) $qfp['average_packet']) . ' Bytes', Severity::Unknown],
            'qfp_memory' => [__('QFP Memory'), Number::formatBi($qfp['memory_used'], 2, 0, '') . 'B / ' . Number::formatBi($qfp['memory_total'], 2, 0, '') . 'B', $memory < 75 ? Severity::Ok : ($memory < 90 ? Severity::Warning : Severity::Error)],
        ];
    @endphp
    <x-panel>
        <x-slot:heading>
            <h3 class="panel-title">
                <i class="fa fa-microchip fa-lg icon-theme" aria-hidden="true"></i> <b>{{ $qfp['name'] }}</b>
                <span class="pull-right">
                    <x-label :status="match ($qfp['system_state']) { 'active', 'activeSolo', 'standby', 'hotStandby' => Severity::Ok, 'reset' => Severity::Error, 'init' => Severity::Warning, default => Severity::Unknown }">{{ __('State') }}: {{ $qfp['system_state'] }}</x-label>
                    <x-label :status="match ($qfp['traffic_direction']) { 'none' => Severity::Error, 'ingress', 'egress' => Severity::Warning, 'both' => Severity::Ok, default => Severity::Unknown }">{{ __('Traffic direction') }}: {{ $qfp['traffic_direction'] }}</x-label>
                </span>
            </h3>
            {{ __('Last system load at') }} <b>{{ $qfp['system_last_load'] }}</b>
        </x-slot:heading>
        @foreach($graphs as $type => [$title, $value, $status])
            <x-panel>
                <x-slot:heading>
                    <h3 class="panel-title">{{ $title }} @if($value)<span class="pull-right"><x-label :status="$status">{{ $value }}</x-label></span>@endif</h3>
                </x-slot:heading>
                <x-graph-row :type="$type" :device="$device" :vars="['id' => $id]" columns="responsive" />
            </x-panel>
        @endforeach
    </x-panel>
@endforeach
