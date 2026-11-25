<div class="container-fluid">
    <div class="row">
        @if($port->deleted)
            @can('delete', $port)
            <div class="col-xs-1" x-data="portPurge()"><a href="#" @click.prevent="purge({{ $port->port_id }})" title="{{ __('port.purge_port') }}"><i class="fa fa-lg icon-theme" :class="loading ? 'fa-refresh fa-spin' : 'fa-times'" aria-hidden="true"></i></a></div>
            @endcan
        @elseif($port->device)
            <div class="col-xs-1"><a href="{{ route('device.faults', $port->device_id) }}" title="View alerts"><i class="fa fa-exclamation-circle fa-lg icon-theme" aria-hidden="true"></i></a></div>
            @can('port.update')
                <div class="col-xs-1"><a href="{{ route('device.edit.ports', $port->device_id) }}" title="Edit ports"><i class="fa fa-pencil fa-lg icon-theme" aria-hidden="true"></i></a></div>
            @endcan
        @endif
    </div>
</div>
