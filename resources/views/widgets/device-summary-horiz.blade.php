<x-panel class="table-responsive">
    <x-slot name="table">
     <table class="table table-hover table-condensed table-striped">
        <thead>
            <tr>
                <th>&nbsp;</th>
                <th><span class="grey">@lang('Total')</span></th>
                <th><span class="green">@lang('Up')</span></th>
                <th><span class="red">@lang('Down')</span></th>
                <th><span class="blue">@lang('Ignore tag')</span></th>
                <th><span class="grey">@lang('Alert disabled')</span></th>
                <th><span class="black">@lang('Disabled')</span></th>
                @if($summary_errors)
                    <th class="black">@lang('Errored')</th>
                @endif
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><a href="{{ url('devices') }}">@lang('Devices')</a></td>
                <td><a href="{{ url('devices') }}"><span> {{ $devices['total'] }}</span></a></td>
                <td><a href="{{ url('devices/state=up/format=list_detail') }}"><span class="green"> {{ $devices['up'] }}</span></a></td>
                <td><a href="{{ url('devices/state=down/format=list_detail') }}"><span class="red"> {{ $devices['down'] }}</span></a></td>
                <td><a href="{{ url('devices/ignore=1/format=list_detail') }}"><span class="blue"> {{ $devices['ignored'] }}</span></a></td>
                <td><a href="{{ url('devices/disable_notify=1/format=list_detail') }}"><span class="grey"> {{ $devices['disable_notify'] }}</span></a></td>
                <td><a href="{{ url('devices/disabled=1/format=list_detail') }}"><span class="black"> {{ $devices['disabled'] }}</span></a></td>
                @if($summary_errors)
                    <td>-</td>
                @endif
            </tr>
            <tr>
                <td><a href="{{ url('ports') }}">@lang('Ports')</a></td>
                <td><a href="{{ url('ports') }}"><span>{{ $ports['total'] }}</span></a></td>
                <td><a href="{{ url('ports/format=list_detail/state=up') }}"><span class="green"> {{ $ports['up'] }}</span></a></td>
                <td><a href="{{ url('ports/format=list_detail/state=down') }}"><span class="red"> {{ $ports['down'] }}</span></a></td>
                <td><a href="{{ url('ports/format=list_detail/ignore=1') }}"><span class="blue"> {{ $ports['ignored'] }}</span></a></td>
                 <td><span class="grey"> NA</span></a></td>
                <td><a href="{{ url('ports/format=list_detail/state=admindown') }}"><span class="black"> {{ $ports['shutdown'] }}</span></a></td>
                @if($summary_errors)
                    <td><a href="{{ url('ports/format=list_detail/errors=1') }}"><span class="black"> {{ $ports['errored'] }}</span></a></td>
                @endif
            </tr>
            @if($show_services)
                <tr>
                    <td><a href="{{ url('services') }}">@lang('Services')</a></td>
                    <td><a href="{{ url('services') }}"><span>{{ $services['total'] }}</span></a></td>
                    <td><a href="{{ url('services/state=ok/view=details') }}"><span class="green">{{ $services['ok'] }}</span></a></td>
                    <td><a href="{{ url('services/state=critical/view=details') }}"><span class="red"> {{ $services['critical'] }}</span></a></td>
                    <td><a href="{{ url('services/ignore=1/view=details') }}"><span class="blue"> {{ $services['ignored'] }}</span></a></td>
                    <td><span class="grey"> NA</span></a></td>
                    <td><a href="{{ url('services/disabled=1/view=details') }}"><span class="black"> {{ $services['disabled'] }}</span></a></td>
                    @if($summary_errors)
                        <td>-</td>
                    @endif
                </tr>
            @endif
        </tbody>
    </table>
    </x-slot>
</x-panel>
