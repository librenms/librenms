<table class="table table-condensed table-hover tw:mb-0">
    <thead>
    <tr>
        <th>{{ __('search.columns.mac') }}</th>
        <th>{{ __('search.columns.device') }} / {{ __('search.columns.interface') }}</th>
        <th>{{ __('search.columns.description') }}</th>
        <th>{{ __('search.columns.status') }}</th>
    </tr>
    </thead>
    <tbody>
    @foreach($results as $port)
        <tr>
            <td>@include('search.partials.mac', ['mac' => $port->getRawOriginal('ifPhysAddress')])</td>
            <td>@include('search.partials.port', ['port' => $port])</td>
            <td>{{ $port->ifAlias != $port->getLabel() ? $port->ifAlias : '' }}</td>
            <td>
                @if($port->ifAdminStatus == 'down')
                    <x-search.badge>{{ __('admin down') }}</x-search.badge>
                @elseif($port->ifOperStatus == 'up')
                    <x-search.badge color="green">{{ __('up') }}</x-search.badge>
                @else
                    <x-search.badge color="red">{{ $port->ifOperStatus ?: __('unknown') }}</x-search.badge>
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
