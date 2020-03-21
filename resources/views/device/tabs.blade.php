<ul class="nav nav-tabs">
    <li role="presentation" @if( $tab == 'overview' ) class="active" @endif>
        <a href="{{ route('device', [$device_id, 'overview']) }}"><i class="fa fa-lightbulb-o fa-lg icon-theme" aria-hidden="true"></i> @lang('Overview')</a>
    </li>

    <li role="presentation" @if( $tab == 'graphs' ) class="active" @endif>
        <a href="{{ route('device', [$device_id, 'graphs']) }}"><i class="fa fa-area-chart fa-lg icon-theme" aria-hidden="true"></i> @lang('Graphs')</a>
    </li>

    @if($show_health_tab)
        <li role="presentation" @if( $tab == 'health' ) class="active" @endif>
            <a href="{{ route('device', [$device_id, 'health']) }}"><i class="fa fa-heartbeat fa-lg icon-theme" aria-hidden="true"></i> @lang('Health')</a>
        </li>
    @endif

    @if($show_apps_tab)
        <li role="presentation" @if( $tab == 'apps' ) class="active" @endif>
            <a href="{{ route('device', [$device_id, 'apps']) }}"><i class="fa fa-cubes fa-lg icon-theme" aria-hidden="true"></i> @lang('Apps')</a>
        </li>
    @endif

    @if($show_processes_tab)
        <li role="presentation" @if( $tab == 'processes' ) class="active" @endif>
            <a href="{{ route('device', [$device_id, 'processes']) }}"><i class="fa fa-microchip fa-lg icon-theme" aria-hidden="true"></i> @lang('Processes')</a>
        </li>
    @endif

    @if($show_collectd_tab)
        <li role="presentation" @if( $tab == 'collectd' ) class="active" @endif>
            <a href="{{ route('device', [$device_id, 'collectd']) }}"><i class="fa fa-pie-chart fa-lg icon-theme" aria-hidden="true"></i> @lang('CollectD')</a>
        </li>
    @endif

    @if($show_munin_tab)
        <li role="presentation" @if( $tab == 'munin' ) class="active" @endif>
            <a href="{{ route('device', [$device_id, 'munin']) }}"><i class="fa fa-pie-chart fa-lg icon-theme" aria-hidden="true"></i> @lang('Munin')</a>
        </li>
    @endif

    @if($show_ports_tab)
        <li role="presentation" @if( $tab == 'ports' ) class="active" @endif>
            <a href="{{ route('device', [$device_id, 'ports']) }}"><i class="fa fa-link fa-lg icon-theme" aria-hidden="true"></i> @lang('Ports')</a>
        </li>
    @endif

</ul>