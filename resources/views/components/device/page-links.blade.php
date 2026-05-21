<div class="btn-group pull-right" role="group">
    @if($device->alerts()->where('open', 1)->count())
        <a href="{{ route('device', [$device->device_id, 'alerts']) }}"
           class="tw:inline-flex tw:items-center tw:gap-2 tw:rounded-xl tw:border tw:border-gray-300 tw:bg-white tw:px-4 tw:py-2 tw:font-medium tw:text-gray-700 tw:no-underline tw:shadow-sm tw:hover:bg-gray-50 tw:transition-colors tw:dark:border-dark-gray-200 tw:dark:bg-dark-gray-400 tw:dark:text-dark-white-200 tw:dark:hover:bg-dark-gray-300 tw:mr-2"
           title="{{ __('Alerts') }}"
        ><i class="fa fa-bell fa-fw tw:text-red-500" aria-hidden="true"></i> {{ __('Alerts') }}
        </a>
    @endif
    <a href="{{ $primaryDeviceLink['url'] }}"
       class="tw:inline-flex tw:items-center tw:gap-2 tw:rounded-xl tw:border tw:border-gray-300 tw:bg-white tw:px-4 tw:py-2 tw:font-medium tw:text-gray-700 tw:no-underline tw:shadow-sm tw:hover:bg-gray-50 tw:transition-colors tw:dark:border-dark-gray-200 tw:dark:bg-dark-gray-400 tw:dark:text-dark-white-200 tw:dark:hover:bg-dark-gray-300 tw:mr-2"
       type="button"
       @if(isset($primaryDeviceLink['onclick']))onclick="{{ $primaryDeviceLink['onclick'] }}" @endif
       @if($primaryDeviceLink['external'])target="_blank" rel="noopener" @endif
       title="{{ $primaryDeviceLink['title'] }}"
    ><i class="fa {{ $primaryDeviceLink['icon'] }} fa-lg icon-theme"></i> Edit
    </a>
    <button type="button" class="tw:inline-flex tw:items-center tw:gap-2 tw:text-center tw:rounded-xl tw:pb-5tw:border tw:border-gray-300 tw:bg-white tw:px-4 tw:py-2 tw:font-medium tw:text-gray-700 tw:no-underline tw:shadow-sm tw:hover:bg-gray-50 tw:transition-colors tw:dark:border-dark-gray-200 tw:dark:bg-dark-gray-400 tw:dark:text-dark-white-200 tw:dark:hover:bg-dark-gray-300 tw:pl-7" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fa fa-ellipsis fa-lg fa-fw icon-theme"></i>&nbsp;
        </button>
        <ul class="dropdown-menu dropdown-menu-right">
            @foreach($deviceLinks as $link)
                <li><a href="{{ $link['url'] }}"
                       @if(isset($link['onclick']))onclick="{{ $link['onclick'] }}" @endif
                       @if($link['external'])target="_blank" rel="noopener" @endif
                    ><i class="fa {{ $link['icon'] }} fa-lg fa-fw icon-theme" aria-hidden="true"></i> {{ $link['title'] }}</a></li>
            @endforeach
            @if($dropdownLinks)
                <li role="presentation" class="divider"></li>
                @foreach($dropdownLinks as $link)
                    <li><a href="{{ $link['url'] }}"
                           @if(isset($link['onclick']))onclick="{{ $link['onclick'] }}" @endif
                           @if($link['external'])target="_blank" rel="noopener" @endif
                        ><i class="fa {{ $link['icon'] }} fa-lg fa-fw icon-theme" aria-hidden="true"></i> {{ $link['title'] }}</a></li>
                @endforeach
            @endif
        </ul>
    </div>
</div>