@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
        <x-device.edit-tabs :device="$device" tab="apps" />

        <div x-data="{
            toggle(event, type) {
                const enabled = event.target.checked;
                axios.put(route('device.edit.apps.update', {device: @js($device->device_id), type}), {enabled})
                    .then((response) => toastr.success(response.data.message))
                    .catch((error) => {
                        event.target.checked = ! enabled;
                        toastr.error(error.response?.data?.message ?? @js(__('Failed to update application')));
                    });
            }
        }">
            <x-panel>
                <div class="tw:columns-1 tw:gap-2 tw:sm:columns-2 tw:lg:columns-3">
                    @foreach($applications as $app)
                        <div class="tw:mb-2 tw:flex tw:items-center tw:gap-3 tw:break-inside-avoid">
                            <x-toggle :checked="$app['enabled']"
                                      :disabled="$app['discovered'] || ! $canUpdate"
                                      x-on:change="toggle($event, {{ Js::from($app['type']) }})">
                                {{ $app['name'] }}
                                @if($app['discovered'])
                                    <span class="text-success">({{ __('Discovered') }})</span>
                                @endif
                            </x-toggle>
                            @if($app['docs'])
                                <a target="_blank" href="{{ $app['docs'] }}" class="tw:ml-auto tw:shrink-0">{{ __('Docs') }} <i class="fa fa-book"></i></a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </x-panel>
        </div>
    </x-device.page>
@endsection
