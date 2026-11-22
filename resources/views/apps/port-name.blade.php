@if($port && Gate::allows('view', $port))
    <x-port-link :port="$port" />
@else
    {{ $name }}
@endif
