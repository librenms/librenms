@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
        <x-device.edit-tabs :device="$device" tab="apps" />

        <div x-data="{
            toggle(event, type) {
                const enabled = event.target.checked;
                axios.put(@js(route('device.edit.apps.update', [$device, '__type__'])).replace('__type__', type), {enabled})
                    .then((response) => toastr.success(response.data.message))
                    .catch((error) => {
                        event.target.checked = ! enabled;
                        toastr.error(error.response?.data?.message ?? @js(__('Failed to update application')));
                    });
            }
        }">
            <x-panel>
                <div class="tw:grid tw:grid-cols-1 tw:sm:grid-cols-2 tw:lg:grid-cols-3 tw:gap-2">
                    @foreach($applications as $app)
                        <x-toggle :checked="$app['enabled']"
                                  :disabled="$app['discovered'] || ! $canUpdate"
                                  x-on:change="toggle($event, {{ Js::from($app['type']) }})">
                            {{ $app['name'] }}
                            @if($app['discovered'])
                                <span class="text-success">({{ __('Discovered') }})</span>
                            @endif
                        </x-toggle>
                    @endforeach
                </div>
            </x-panel>
        </div>
    </x-device.page>
@endsection
