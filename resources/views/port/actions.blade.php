<div class="container-fluid">
    <div class="row">
        @if(request('deleted') === 'yes')
            <div class="col-xs-1"><a href="ports/deleted=1/purge={{ $port->port_id }}" title="Delete port"><i class="fa fa-times fa-lg icon-theme"></i></a></div>
        @else
            <div class="col-xs-1"><a href="@deviceUrl($port->device, ['tab' => 'alerts'])" title="View alerts"><i class="fa fa-exclamation-circle fa-lg icon-theme" aria-hidden="true"></i></a></div>
            @admin
                <div class="col-xs-1"><a href="@deviceUrl($port->device, ['tab' => 'edit', 'section' => 'ports'])" title="Edit ports"><i class="fa fa-pencil fa-lg icon-theme" aria-hidden="true"></i></a></div>
            @endadmin
        @endif
    </div>
</div>
