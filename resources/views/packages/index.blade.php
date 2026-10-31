@extends('layouts.librenmsv1')

@section('title', __('search.packages'))

@section('content')
    <div class="container-fluid">
        <x-panel>
            <x-slot name="title">
                <i class="fa fa-archive fa-fw fa-lg" aria-hidden="true"></i> {{ __('search.packages') }}
            </x-slot>

            <form method="get" action="{{ route('packages.index') }}" class="tw:flex tw:flex-wrap tw:items-end tw:gap-3">
                <div class="form-group tw:mb-0">
                    <label for="package-name" class="control-label">{{ __('Package') }}</label>
                    <input type="search" id="package-name" name="name" value="{{ $filters['name'] ?? '' }}" class="form-control" placeholder="{{ __('Any') }}">
                </div>
                <div class="form-group tw:mb-0">
                    <label for="package-version" class="control-label">{{ __('Version') }}</label>
                    <input type="text" id="package-version" name="version" value="{{ $filters['version'] ?? '' }}" class="form-control"
                           placeholder="{{ __('Any') }}" title="{{ __('Text to match, or compare with an operator: >= 1.2.3') }}">
                </div>
                <div class="form-group tw:mb-0">
                    <label for="package-arch" class="control-label">{{ __('Arch') }}</label>
                    <input type="text" id="package-arch" name="arch" value="{{ $filters['arch'] ?? '' }}" class="form-control tw:w-32" placeholder="{{ __('Any') }}">
                </div>
                <div class="tw:w-64">
                    <x-select2 name="device_id" type="device" :label="__('Device')" :placeholder="__('search.all_devices')">
                        <option value=""></option>
                        @if($device)
                            <option value="{{ $device->device_id }}" selected>{{ $device->displayName() }}</option>
                        @endif
                    </x-select2>
                </div>
                <button type="submit" class="btn btn-primary">{{ __('search.search') }}</button>
                @if(array_filter($filters))
                    <a href="{{ route('packages.index') }}" class="btn btn-default">{{ __('Reset') }}</a>
                @endif
            </form>
        </x-panel>

        <x-panel>
            <x-slot name="title">
                {{ trans_choice(':count package|:count packages', $names->total()) }}
            </x-slot>
            <x-slot name="table">
                <div class="table-responsive">
                    <table class="table table-condensed table-hover">
                        <thead>
                        <tr>
                            <th>{{ __('Package') }}</th>
                            <th>{{ __('Versions') }}</th>
                            <th>{{ __('Arch') }}</th>
                            <th>{{ __('Manager') }}</th>
                            <th class="tw:text-right">{{ __('Devices') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($packages as $name => $package)
                            <tr>
                                <td class="tw:font-semibold">
                                    <a href="{{ route('packages.index', ['name' => $name]) }}">{{ $name }}</a>
                                </td>
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
                        @empty
                            <tr>
                                <td colspan="5" class="tw:text-center tw:p-5"><em>{{ __('No packages found') }}</em></td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </x-slot>
            @if($names->hasPages())
                <x-slot name="footer">
                    {{ $names->links() }}
                </x-slot>
            @endif
        </x-panel>
    </div>
@endsection
