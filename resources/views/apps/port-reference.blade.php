{{ $label }}@if($port && $port->device && Gate::allows('view', $port->device) && Gate::allows('view', $port))
    (<x-device-link :device="$port->device" />, <x-port-link :port="$port" />)
@endif
