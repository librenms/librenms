<table class="table table-condensed table-hover tw:mb-0">
    <thead>
    <tr>
        <th>{{ __('search.columns.device') }}</th>
        <th>{{ __('search.columns.ip') }}</th>
        <th>{{ __('search.columns.os') }}</th>
        <th>{{ __('search.columns.hardware') }}</th>
        <th>{{ __('search.columns.location') }}</th>
        <th>{{ __('search.columns.status') }}</th>
    </tr>
    </thead>
    <tbody>
    @foreach($results as $device)
        @php($status = $device->getDeviceStatus())
        <tr>
            <td class="tw:border-l-4! {{ match ($status) {
                \LibreNMS\Enum\DeviceStatus::Up, \LibreNMS\Enum\DeviceStatus::IgnoredUp => 'tw:border-l-green-600!',
                \LibreNMS\Enum\DeviceStatus::Down, \LibreNMS\Enum\DeviceStatus::IgnoredDown => 'tw:border-l-red-600!',
                \LibreNMS\Enum\DeviceStatus::Disabled => 'tw:border-l-black!',
                \LibreNMS\Enum\DeviceStatus::NeverPolled => 'tw:border-l-gray-400!',
            } }}">
                <div class="tw:flex tw:items-center tw:gap-2">
                    <img src="{{ asset($device->icon) }}" alt="" class="tw:h-6 tw:w-6 tw:shrink-0 tw:object-contain tw:dark:bg-gray-50 tw:dark:rounded tw:dark:p-0.5">
                    <div class="tw:min-w-0">
                        {!! \LibreNMS\Util\Url::modernDeviceLink($device) !!}
                        @if($device->name() != $device->display)
                            <div class="tw:text-xs tw:text-gray-500 tw:dark:text-dark-white-400">{{ $device->name() }}</div>
                        @endif
                    </div>
                </div>
            </td>
            <td>
                @if($device->overwrite_ip ?: $device->ip)
                    @include('search.partials.ip', ['ip' => $device->overwrite_ip ?: $device->ip])
                @endif
            </td>
            <td>{{ \App\Facades\LibrenmsConfig::getOsSetting($device->os, 'text') }} <span class="tw:text-gray-500">{{ $device->version }}</span></td>
            <td>
                {{ $device->hardware }}
                @if($device->serial)
                    <div class="tw:text-xs tw:text-gray-500 tw:dark:text-dark-white-400">{{ __('search.columns.serial') }}: {{ $device->serial }}</div>
                @endif
            </td>
            <td>{{ $device->location?->display() }}</td>
            <td class="tw:whitespace-nowrap">
                @if($status == \LibreNMS\Enum\DeviceStatus::Disabled)
                    <x-search.badge>{{ __('Disabled') }}</x-search.badge>
                @elseif($device->status)
                    <x-search.badge color="green">{{ __('Up') }}</x-search.badge>
                @else
                    <x-search.badge color="red">{{ __('Down') }}</x-search.badge>
                @endif
                @if($device->isUnderMaintenance())
                    <x-search.badge color="blue">{{ __('Maintenance') }}</x-search.badge>
                @endif
                <span class="tw:text-xs tw:text-gray-500">{{ $device->formatDownUptime(true) }}</span>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
