<li class="{{ !empty($node['children']) ? 'liOpen' : 'liBullet' }}">
    <i class="fa {{ $node['icon'] }} fa-lg icon-theme" aria-hidden="true"></i>

    @if($node['entity']->entPhysicalParentRelPos > -1)
        <strong>{{ $node['entity']->entPhysicalParentRelPos }}.</strong>
    @endif

    @if($node['port'])
        <strong><x-port-link :port="$node['port']" /></strong>
    @elseif($node['label'])
        <strong>{{ $node['label'] }}</strong>
    @endif

    @foreach($node['states'] as $state)
        <span class="label label-{{ $state['color'] }}" data-toggle="tooltip" title="{{ $state['name'] }} ({{ $state['value'] }})">
            {{ $state['text'] }}
        </span>
    @endforeach

    @if(!empty($node['alarms']))
        <br>
        <span class="tw:ml-5">{{ __('Alarms') }}:
            @foreach($node['alarms'] as $alarm)
                <span class="label label-{{ $alarm['color'] }}">{{ $alarm['text'] }}</span>
            @endforeach
        </span>
    @endif

    <br>
    <div class="interface-desc tw:ml-5">
        {{ $node['entity']->entPhysicalDescr }}
        @if($node['entity']->entPhysicalAlias && $node['entity']->entPhysicalAssetID)
            <br>{{ __('Alias') }}: {{ $node['entity']->entPhysicalAlias }} - {{ __('AssetID') }}: {{ $node['entity']->entPhysicalAssetID }}
        @elseif($node['entity']->entPhysicalAlias)
            <br>{{ __('Alias') }}: {{ $node['entity']->entPhysicalAlias }}
        @elseif($node['entity']->entPhysicalAssetID)
            <br>{{ __('AssetID') }}: {{ $node['entity']->entPhysicalAssetID }}
        @endif

        @if($node['entity']->entPhysicalSerialNum)
            <br><span class="text-info">{{ __('Serial No.') }} {{ $node['entity']->entPhysicalSerialNum }}</span>
        @endif

        @if(!empty($node['sensors']))
            <br>{{ __('Sensors') }}:
            <div class="interface-desc tw:ml-5">
                @foreach($node['sensors'] as $sensor)
                    <x-popup>
                        <a href="{{ $sensor['graph_url'] }}">
                            <span class="text-info">{{ $sensor['description'] }}</span>
                            <x-label :status="$sensor['status']">{{ $sensor['value'] }}</x-label>
                        </a>
                        <x-slot name="title">{{ $sensor['popup_title'] }}</x-slot>
                        <x-slot name="body">
                            <x-graph-row loading="lazy" :type="$sensor['graph_type']" :vars="$sensor['graph_vars']" />
                        </x-slot>
                    </x-popup>
                    <br>
                @endforeach
            </div>
        @endif
    </div>

    @if(!empty($node['children']))
        <ul>
            @foreach($node['children'] as $child)
                @include('device.tabs.inventory.entphysical-node', ['node' => $child])
            @endforeach
        </ul>
    @endif
</li>
