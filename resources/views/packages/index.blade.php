@extends('layouts.librenmsv1')

@section('title', __('Packages'))

@section('content')
    <div class="container-fluid">
        <x-packages.table filter-name="packages" :filter-fields="$filterFields" :filter="$filter" :count="$names->total()"
                          :last-column="__('Devices')" :empty-message="__('No packages found')">
            @foreach($packages as $name => $package)
                <tr>
                    <x-packages.name :name="$name" />
                    <td>
                        @foreach($package['versions'] as $version => $devices)
                            <details class="tw:mb-1">
                                <summary class="tw:cursor-pointer">
                                    <span class="tw:font-mono">{{ $version }}</span>
                                    <x-search.badge>{{ trans_choice(':count device|:count devices', $devices->count()) }}</x-search.badge>
                                </summary>
                                <div class="tw:flex tw:flex-wrap tw:gap-x-3 tw:pl-4 tw:pt-1">
                                    @foreach($devices as $packageDevice)
                                        {!! \LibreNMS\Util\Url::modernDeviceLink($packageDevice) !!}
                                    @endforeach
                                </div>
                            </details>
                        @endforeach
                    </td>
                    <td>{{ $package['arch']->implode(', ') }}</td>
                    <td>{{ $package['managers']->implode(', ') }}</td>
                    <td class="tw:text-right">{{ $package['device_count'] }}</td>
                </tr>
            @endforeach
            @if($names->hasPages())
                <x-slot name="footer">
                    {{ $names->links() }}
                </x-slot>
            @endif
        </x-packages.table>
    </div>
@endsection
