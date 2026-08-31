<li class="{{ !empty($node['children']) ? 'liOpen' : 'liBullet' }}">
    <i class="fa {{ $node['icon'] }} fa-lg icon-theme" aria-hidden="true"></i>

    @if($node['entity']->entPhysicalParentRelPos > -1)
        <strong>{{ $node['entity']->entPhysicalParentRelPos }}.</strong>
    @endif

    @php
        $ent = $node['entity'];
        $displayName = $ent->entPhysicalName;
        $port = $node['port'];
    @endphp

    @if($port)
        <strong><x-port-link :port="$port" /></strong>
    @elseif($ent->entPhysicalModelName && $displayName)
        <strong>{{ $ent->entPhysicalModelName }}</strong> ({{ $displayName }})
    @elseif($ent->entPhysicalModelName)
        <strong>{{ $ent->entPhysicalModelName }}</strong>
    @elseif(is_numeric($displayName) && $ent->entPhysicalVendorType)
        <strong>{{ $displayName }} {{ $ent->entPhysicalVendorType }}</strong>
    @elseif($displayName)
        <strong>{{ $displayName }}</strong>
    @elseif($ent->entPhysicalDescr)
        <strong>{{ $ent->entPhysicalDescr }}</strong>
    @elseif($ent->entPhysicalClass)
        <strong>{{ $ent->entPhysicalClass }}</strong>
    @endif

    @foreach($node['states'] as $state)
        <span class="label label-{{ $state['color'] }}" data-toggle="tooltip" title="{{ $state['name'] }} ({{ $state['value'] }})">
            {{ $state['text'] }}
        </span>
    @endforeach

    @if(!empty($node['alarms']))
        <br>
        <span style="margin-left: 20px;">{{ __('Alarms') }}:
            @foreach($node['alarms'] as $alarm)
                <span class="label label-{{ $alarm['color'] }}">{{ $alarm['text'] }}</span>
            @endforeach
        </span>
    @endif

    <br>
    <div class="interface-desc" style="margin-left: 20px;">
        {{ $ent->entPhysicalDescr }}
        @if($ent->entPhysicalAlias && $ent->entPhysicalAssetID)
            <br>{{ __('Alias') }}: {{ $ent->entPhysicalAlias }} - {{ __('AssetID') }}: {{ $ent->entPhysicalAssetID }}
        @elseif($ent->entPhysicalAlias)
            <br>{{ __('Alias') }}: {{ $ent->entPhysicalAlias }}
        @elseif($ent->entPhysicalAssetID)
            <br>{{ __('AssetID') }}: {{ $ent->entPhysicalAssetID }}
        @endif

        @if($ent->entPhysicalSerialNum)
            <br><span class="text-info">{{ __('Serial No.') }} {{ $ent->entPhysicalSerialNum }}</span>
        @endif

        @if($node['sensors']->isNotEmpty())
            <br>{{ __('Sensors') }}:
            <div class="interface-desc" style="margin-left: 20px;">
                @foreach($node['sensors'] as $sensor)
                    <span class="text-info">{{ $sensor->sensor_descr }} {{ $sensor->sensor_class }}</span>
                    <span class="label label-{{ \LibreNMS\Util\Html::severityToColor($sensor->currentStatus()) }}">
                        {{ $sensor->formatValue() }}
                    </span>
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
