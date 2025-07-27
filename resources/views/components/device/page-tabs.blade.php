<ul class="nav nav-tabs">
    @foreach($tabs as $tab)
        @if($tab->visible($device))
            <li role="presentation" @if( $currentTab == $tab->slug() ) class="active" @endif>
                <a href="{{ route('device', [$device->device_id, $tab->slug()]) }}" class="tw:whitespace-nowrap">
                    <i class="fa {{ $tab->icon() }} fa-lg icon-theme" aria-hidden="true"></i>
                    {{ $tab->name() }}
                </a>
            </li>
        @endif
    @endforeach
    <x-device.page-links :device="$device" :current-tab="$currentTab" />
</ul>
