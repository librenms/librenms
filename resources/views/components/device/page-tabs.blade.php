<ul role="tablist" class="tw:flex tw:flex-wrap tw:list-none tw:pl-6 tw:mt-6 tw:border-b tw:border-gray-200 tw:dark:border-dark-gray-200 tw:mb-0">
    @foreach($tabs as $tab)
        @if($tab->visible($device))
            <li role="presentation" class="tw:-mb-px">
                <a href="{{ route('device', [$device->device_id, $tab->slug()]) }}"
                   @class([
                       'tw:inline-block tw:p-3 tw:border-b-2 tw:rounded-t-lg tw:font-medium tw:whitespace-nowrap tw:no-underline tw:transition-colors tw:duration-75',
                       'tw:border-green-600 tw:text-gray-800 tw:dark:text-blue-500 tw:dark:border-green-600' => $currentTab === $tab->slug(),
                       'tw:border-transparent tw:text-gray-600 tw:hover:border-gray-400 tw:hover:text-gray-800 tw:dark:text-dark-white-300 tw:dark:hover:border-gray-500' => $currentTab !== $tab->slug(),
                   ])
                >
                    <i class="fa {{ $tab->icon() }} fa-lg icon-theme" aria-hidden="true"></i>
                    {{ $tab->name() }}
                </a>
            </li>
        @endif
    @endforeach
    <x-device.page-links :device="$device" :dropdown-links="$dropdownLinks"/>
</ul>
