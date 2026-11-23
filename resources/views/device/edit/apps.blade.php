@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
        <x-device.edit-tabs :device="$device" tab="apps" />

        <div x-data="deviceApps(@js([
                'apps' => $applications,
                'deviceId' => $device->device_id,
                'canUpdate' => $canUpdate,
            ]))">
            <x-panel class="tw:mb-0 tw:max-w-5xl tw:mx-auto">
                <x-slot name="heading" class="tw:flex tw:flex-wrap tw:items-center tw:justify-between tw:gap-3">
                    <div>
                        <h3 class="panel-title">{{ __('Applications') }}</h3>
                        <p class="tw:m-0 tw:mt-1 tw:text-base tw:text-gray-500 tw:dark:text-dark-white-400">
                            {{ __('Applications can be enabled manually or automatically during discovery.') }}
                        </p>
                    </div>
                    <div class="tw:flex tw:flex-wrap tw:items-center tw:gap-3">
                        <input type="search"
                               x-model="search"
                               placeholder="{{ __('Filter applications') }}"
                               aria-label="{{ __('Filter applications') }}"
                               class="form-control tw:w-48">
                        <x-toggle x-model="enabledOnly">
                            <span x-text="'{{ __('Enabled only') }} (' + enabledCount + ')'"></span>
                        </x-toggle>
                    </div>
                </x-slot>

                <div class="tw:columns-1 tw:gap-2 tw:sm:columns-2 tw:lg:columns-3">
                    <template x-for="app in visibleApplications" :key="app.type">
                        <div class="tw:mb-2 tw:flex tw:items-center tw:gap-3 tw:break-inside-avoid">
                            <x-toggle ::checked="app.enabled"
                                      ::disabled="app.managed || ! canUpdate"
                                      x-on:change="toggle($event, app.type)">
                                <span x-text="app.name"></span>
                                <template x-if="app.enabled && app.discovered">
                                    <span class="text-success">({{ __('Discovered') }})</span>
                                </template>
                                <template x-if="app.enabled && ! app.discovered">
                                    <span class="text-blue-600 tw:dark:text-blue-400">({{ __('Manual') }})</span>
                                </template>
                            </x-toggle>
                            <a x-if="app.docs" target="_blank" :href="app.docs" class="tw:ml-auto tw:shrink-0">{{ __('Docs') }} <i class="fa fa-book"></i></a>
                        </div>
                    </template>
                </div>
                <div x-show="visibleApplications.length === 0" x-cloak class="tw:px-4 tw:py-8 tw:text-center tw:text-gray-500 tw:dark:text-dark-white-400">{{ __('No applications match') }}</div>
            </x-panel>
        </div>
    </x-device.page>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('deviceApps', (config) => ({
                apps: config.apps,
                canUpdate: config.canUpdate,
                search: '',
                enabledOnly: false,

                get enabledCount() {
                    return this.apps.filter((app) => app.enabled).length;
                },

                get visibleApplications() {
                    const search = this.search.trim().toLowerCase();

                    return this.apps.filter((app) =>
                        (! this.enabledOnly || app.enabled)
                        && (search === '' || app.name.toLowerCase().includes(search) || app.type.includes(search))
                    );
                },

                toggle(event, type) {
                    const enabled = event.target.checked;
                    const app = this.apps.find((app) => app.type === type);

                    axios.put(route('device.edit.apps.update', {device: config.deviceId, type}), {enabled})
                        .then((response) => {
                            app.enabled = enabled;
                            toastr.success(response.data.message);
                        })
                        .catch((error) => {
                            event.target.checked = ! enabled;
                            toastr.error(error.response?.data?.message ?? '{{ __('Failed to update application') }}');
                        });
                },
            }));
        });
    </script>
@endpush
