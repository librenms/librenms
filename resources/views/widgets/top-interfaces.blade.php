<h4>Top {{ $interface_count }} interfaces polled within {{ $time_interval }} minutes</h4>
<div class="table-responsive">
    <table class="table table-hover table-condensed table-striped bootgrid-table">
        <thead>
            <tr>
                <th class="text-left">@lang('Device')</th>
                <th class="text-left">@lang('Interface')</th>
                <th class="text-left">@lang('Total traffic')</th>
            </tr>
        </thead>
        <tbody>
        @foreach($ports as $port)
            <tr>
                <td class="text-left"><x-device-link :device="$port->device">{{$port->device->shortDisplayName() }}</x-device-link></td>
                <td class="text-left"><x-port-link :port="$port">{{ $port->getShortLabel() }}</x-port-link></td>
                <td class="text-left"><x-port-link :port="$port">{{ \LibreNMS\Util\Url::portThumbnail($port) }}</x-port-link></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
