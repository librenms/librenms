<ul role="tablist" class="lnms-device-page__tabs">
    @foreach($tabs as $tab)
        @if($tab->visible($device))
            <li role="presentation" class="lnms-device-page__tab">
                <a href="{{ route('device', [$device->device_id, $tab->slug()]) }}"
                   @class([
            'lnms-device-page__tab-link',
            'lnms-device-page__tab-link--active' => $currentTab === $tab->slug(),
        ])
                >
                    <i class="fa {{ $tab->icon() }} fa-lg icon-theme" aria-hidden="true"></i>
                    {{ $tab->name() }}
                </a>
            </li>
        @endif
    @endforeach
    <x-device.page-links :device="$device" :current-tab="$currentTab" :dropdown-links="$dropdownLinks"/>
</ul>
