<div class="panel panel-default" id="overlays">
    <div class="panel-heading">
        <h3 class="panel-title">{{ __("Overlay's & Adjacencies") }}</h3>
    </div>
    <div class="panel list-group">
    @forelse($data['overlays'] as $overlay)
        @php
            $isNormal = ($overlay['status'] ?? 0) == 0;
            $gli = $isNormal ? '' : 'list-group-item-danger';
        @endphp
        <a class="list-group-item {{ $gli }}" data-toggle="collapse" data-target="#overlay_{{ $overlay['index'] }}" data-parent="#overlays">
            {{ $overlay['label'] }} - {{ $overlay['transport'] ?? '' }}
            @if($isNormal)
                <span class="text-success pull-right">{{ __('Normal') }}</span>
            @else
                <span class="pull-right">{{ $overlay['error'] ?? '' }} - <span class="text-danger">{{ __('Alert') }}</span></span>
            @endif
        </a>
        <div id="overlay_{{ $overlay['index'] }}" class="sublinks collapse">
        @foreach($overlay['adjacencies'] as $adjacency)
            @php
                $adjNormal = ($adjacency['status'] ?? 0) == 0;
                $adjGli = $adjNormal ? '' : 'list-group-item-danger';
            @endphp
            <a class="list-group-item {{ $adjGli }} small">
                <i class="fa fa-chevron-right" aria-hidden="true"></i> {{ $adjacency['label'] }} - {{ $adjacency['endpoint'] ?? '' }}
                @if($adjNormal)
                    <span class="text-success pull-right">{{ __('Normal') }}</span>
                @else
                    <span class="pull-right">{{ $adjacency['error'] ?? '' }} - <span class="text-danger">{{ __('Alert') }}</span></span>
                @endif
            </a>
        @endforeach
        </div>
    @empty
        <div class="list-group-item text-muted">{{ __('No OTV overlays found.') }}</div>
    @endforelse
    </div>
</div>

<div class="panel panel-default" id="vlanperoverlay">
    <div class="panel-heading">
        <h3 class="panel-title">{{ __('AED Enabled VLANs') }}</h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <x-graph-row :device="$device" :type="'device_cisco-otv-vlan'" />
        </div>
    </div>
</div>

<div class="panel panel-default" id="macperendpoint">
    <div class="panel-heading">
        <h3 class="panel-title">{{ __('MAC Addresses') }}</h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <x-graph-row :device="$device" :type="'device_cisco-otv-mac'" />
        </div>
    </div>
</div>
