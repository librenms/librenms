<h4>Top {{ $interface_count }} errored interfaces polled within {{ $time_interval }} minutes</h4>
<div class="table-responsive">
    <table class="table table-hover table-condensed table-striped bootgrid-table">
        <thead>
            <tr>
                <th class="text-left">{{ __('Device') }}</th>
                <th class="text-left">{{ __('Interface') }}</th>
                <th class="text-left">{{ __('Error Rate') }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach($ports as $port)
            <tr>
                <td class="text-left"><x-device-link :device="$port->device">{{$port->device->shortDisplayName() }}</x-device-link></td>
                <td class="text-left"><x-port-link :port="$port">{{ $port->getShortLabel() }}</x-port-link></td>
                <td class="text-left"><x-port-link :port="$port"><x-graph :port="$port" type="port_bits" width="150" height="21"></x-graph></x-port-link></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
