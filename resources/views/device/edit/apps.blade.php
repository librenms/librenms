@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
        <x-device.edit-tabs :device="$device" tab="apps" />

        <div x-data="deviceApps(@js([
                'apps' => $applications,
                'deviceId' => $device->device_id,
                'canUpdate' => $canUpdate,
            ]))">
            <x-panel>
                <x-slot name="heading" class="tw:flex tw:flex-wrap tw:items-center tw:justify-between tw:gap-3">
                    <h3 class="panel-title">{{ __('Applications') }}</h3>
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
                        <div class="tw:mb-2 tw:flex tw:items-center tw:gap-3 tw:break-inside-avoid tw:border tw:rounded-lg tw:px-3 tw:py-2 tw:border-gray-200 tw:dark:border-dark-gray-400 tw:bg-white tw:dark:bg-dark-gray-500">
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
                            <template x-if="app.docs">
                                <a target="_blank" :href="app.docs" title="{{ __('Docs') }}" aria-label="{{ __('Docs') }}" class="tw:ml-auto tw:shrink-0"><i class="fa fa-book"></i></a>
                            </template>
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
                pending: {},

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

                isPending(key) {
                    return this.pending[key] === true;
                },

                toggle(event, type) {
                    const enabled = event.target.checked;
                    const app = this.apps.find((app) => app.type === type);
                    const key = 'toggle.' + type;
                    if (this.isPending(key)) {
                        event.target.checked = ! enabled;
                        return;
                    }

                    this.pending[key] = true;

                    axios.put(route('device.edit.apps.update', {device: config.deviceId, type}), {enabled})
                        .then((response) => {
                            app.enabled = enabled;
                            toastr.success(response.data.message);
                        })
                        .catch((error) => {
                            event.target.checked = ! enabled;
                            toastr.error(error.response?.data?.message ?? '{{ __('Failed to update application') }}');
                        })
                        .finally(() => delete this.pending[key]);
                },
            }));
        });
    </script>
@endpush
