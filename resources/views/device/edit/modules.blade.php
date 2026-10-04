@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
        <x-device.edit-tabs :device="$device" tab="modules" />

        <div x-data="deviceModules(@js([
                'modules' => $modules,
                'updateUrl' => route('device.edit.modules.update', [$device, '__module__']),
                'deleteUrl' => route('device.edit.modules.delete', [$device, '__module__']),
            ]))">
            <x-panel class="tw:mb-0 tw:max-w-5xl tw:mx-auto">
                <x-slot name="heading" class="tw:flex tw:flex-wrap tw:items-center tw:justify-between tw:gap-3">
                    <div>
                        <h3 class="panel-title">{{ __('Discovery & Polling Modules') }}</h3>
                        <p class="tw:m-0 tw:mt-1 tw:text-base tw:text-gray-500 tw:dark:text-dark-white-400">
                            {{ __('Each module uses the first setting found:') }}
                            <span class="tw:inline-flex tw:items-center tw:gap-1 tw:whitespace-nowrap">
                                <span class="tw:font-semibold tw:text-amber-600 tw:dark:text-amber-400">{{ __('Device override') }}</span>
                                <i class="fa fa-angle-right" aria-hidden="true"></i>
                                <span class="tw:font-semibold tw:text-gray-700 tw:dark:text-dark-white-200">{{ __('OS default') }}</span>
                                <i class="fa fa-angle-right" aria-hidden="true"></i>
                                <span class="tw:font-semibold tw:text-gray-700 tw:dark:text-dark-white-200">{{ __('Global default') }}</span>
                            </span>
                        </p>
                    </div>
                    <div class="tw:flex tw:flex-wrap tw:items-center tw:gap-3">
                        <input type="search"
                               x-model="search"
                               placeholder="{{ __('Filter modules') }}"
                               aria-label="{{ __('Filter modules') }}"
                               class="form-control input-sm tw:w-48">
                        <x-toggle size="sm" x-model="overriddenOnly">
                            <span x-text="'{{ __('Overrides only') }} (' + overrideCount + ')'"></span>
                        </x-toggle>
                    </div>
                </x-slot>

                <x-slot name="table">
                    <div class="tw:overflow-x-auto">
                        <table class="tw:w-full tw:text-base">
                            <thead>
                            <tr class="tw:border-b tw:border-gray-200 tw:dark:border-dark-gray-200 tw:text-left tw:text-sm tw:uppercase tw:tracking-wider tw:text-gray-500 tw:dark:text-dark-white-400">
                                <th class="tw:px-4 tw:py-2 tw:font-semibold">{{ __('Module') }}</th>
                                <th class="tw:px-4 tw:py-2 tw:font-semibold tw:w-56">{{ __('Discovery') }}</th>
                                <th class="tw:px-4 tw:py-2 tw:font-semibold tw:w-56">{{ __('Polling') }}</th>
                                <th class="tw:px-4 tw:py-2 tw:w-16"><span class="tw:sr-only">{{ __('Data') }}</span></th>
                            </tr>
                            </thead>
                            <tbody class="tw:divide-y tw:divide-gray-100 tw:dark:divide-dark-gray-300">
                            <template x-for="module in visibleModules" :key="module.module">
                                <tr class="tw:hover:bg-gray-50 tw:dark:hover:bg-dark-gray-400">
                                    <td class="tw:px-4 tw:py-2.5">
                                        <div class="tw:font-semibold tw:text-gray-900 tw:dark:text-dark-white-100" x-text="module.name"></div>
                                        <div class="tw:text-sm tw:font-mono tw:text-gray-500 tw:dark:text-dark-white-400" x-show="module.name !== module.module" x-text="module.module"></div>
                                    </td>
                                    @foreach (['discovery', 'polling'] as $type)
                                        <td class="tw:px-4 tw:py-2.5">
                                            <template x-if="module.{{ $type }}">
                                                <div class="tw:flex tw:items-center tw:gap-3">
                                                    <x-toggle size="sm"
                                                              ::checked="enabled(module.{{ $type }})"
                                                              ::aria-label="module.name + ' {{ $type }}'"
                                                              x-on:change="setOverride(module, '{{ $type }}', $event.target)" />
                                                    <div class="tw:flex tw:flex-col tw:leading-tight">
                                                        <span class="tw:text-sm tw:font-medium"
                                                              :class="module.{{ $type }}.device === null ? 'tw:text-gray-500 tw:dark:text-dark-white-400' : 'tw:text-amber-600 tw:dark:text-amber-400'"
                                                              :title="sourceDetails(module.{{ $type }})"
                                                              x-text="sourceLabel(module.{{ $type }})"></span>
                                                        <button type="button"
                                                                x-show="module.{{ $type }}.device !== null"
                                                                x-on:click="clearOverride(module, '{{ $type }}')"
                                                                class="tw:p-0 tw:text-left tw:text-sm tw:text-blue-600 tw:dark:text-blue-400 tw:hover:underline tw:bg-transparent tw:border-0">
                                                            <i class="fa fa-rotate-left" aria-hidden="true"></i> <span x-text="resetLabel(module.{{ $type }})"></span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>
                                            <template x-if="! module.{{ $type }}">
                                                <span class="tw:text-sm tw:text-gray-400 tw:dark:text-dark-gray-100">{{ __('Not applicable') }}</span>
                                            </template>
                                        </td>
                                    @endforeach
                                    <td class="tw:px-4 tw:py-2.5 tw:text-right">
                                        <button type="button"
                                                x-show="module.has_data"
                                                x-on:click="deleteTarget = module"
                                                title="{{ __('Delete module data') }}"
                                                aria-label="{{ __('Delete module data') }}"
                                                class="tw:h-8 tw:w-8 tw:inline-flex tw:items-center tw:justify-center tw:rounded-md tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:bg-transparent tw:text-red-600 tw:dark:text-red-400 tw:hover:bg-red-50 tw:dark:hover:bg-red-950/40 tw:transition-colors">
                                            <i class="fa fa-trash" aria-hidden="true"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="visibleModules.length === 0" x-cloak>
                                <td colspan="4" class="tw:px-4 tw:py-8 tw:text-center tw:text-gray-500 tw:dark:text-dark-white-400">{{ __('No modules match') }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </x-slot>
            </x-panel>

            <x-modal show="deleteTarget" maxWidth="md">
                <x-slot name="heading">
                    <h4 class="tw:m-0 tw:text-base tw:font-semibold">
                        {{ __('Delete data for') }} <span x-text="deleteTarget?.name"></span>?
                    </h4>
                </x-slot>
                <p class="tw:m-0">{{ __('This removes all data stored for this module on this device.') }}</p>
                <p class="tw:m-0">{{ __('Data will not repopulate until discovery and/or polling is run again.') }}</p>
                <x-slot name="footer">
                    <button type="button" class="lnms-btn lnms-btn-default tw:px-3 tw:py-1.5" x-on:click="deleteTarget = null">{{ __('Cancel') }}</button>
                    <button type="button" class="lnms-btn lnms-btn-danger tw:px-3 tw:py-1.5" x-on:click="deleteData()" :disabled="isPending('delete')">{{ __('Delete') }}</button>
                </x-slot>
            </x-modal>
        </div>
    </x-device.page>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('deviceModules', (config) => ({
                modules: config.modules,
                search: '',
                overriddenOnly: false,
                deleteTarget: null,
                pending: {},

                get overrideCount() {
                    return this.modules.filter((module) => this.isOverridden(module)).length;
                },

                get visibleModules() {
                    const search = this.search.trim().toLowerCase();

                    return this.modules.filter((module) =>
                        (! this.overriddenOnly || this.isOverridden(module))
                        && (search === '' || module.name.toLowerCase().includes(search) || module.module.includes(search))
                    );
                },

                isOverridden(module) {
                    return module.discovery?.device != null || module.polling?.device != null;
                },

                enabled(setting) {
                    return setting.device ?? setting.os ?? setting.global;
                },

                sourceLabel(setting) {
                    if (setting.device !== null) return '{{ __('Device override') }}';
                    if (setting.os !== null) return '{{ __('OS default') }}';
                    return '{{ __('Global default') }}';
                },

                resetLabel(setting) {
                    return (setting.os ?? setting.global) ? '{{ __('Use default (on)') }}' : '{{ __('Use default (off)') }}';
                },

                sourceDetails(setting) {
                    const state = (value) => value === null ? '{{ __('Unset') }}' : (value ? '{{ __('Enabled') }}' : '{{ __('Disabled') }}');

                    return '{{ __('Global') }}: ' + state(setting.global) + '\n{{ __('OS') }}: ' + state(setting.os) + '\n{{ __('Device') }}: ' + state(setting.device);
                },

                isPending(key) {
                    return this.pending[key] === true;
                },

                request(key, method, url, data = {}) {
                    this.pending[key] = true;

                    return fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify(data),
                    }).then(async (response) => {
                        const body = await response.json().catch(() => ({}));
                        if (! response.ok) {
                            throw new Error(body.message ?? '{{ __('Request failed') }}');
                        }

                        return body;
                    }).catch((error) => {
                        toastr.error(error.message);
                        throw error;
                    }).finally(() => delete this.pending[key]);
                },

                setOverride(module, type, input) {
                    const enabled = input.checked;
                    const key = module.module + '.' + type;
                    if (this.isPending(key)) {
                        input.checked = ! enabled;
                        return;
                    }

                    this.request(key, 'PUT', config.updateUrl.replace('__module__', module.module), {[type]: enabled ? 'true' : 'false'})
                        .then(() => module[type].device = enabled)
                        .catch(() => input.checked = ! enabled);
                },

                clearOverride(module, type) {
                    const key = module.module + '.' + type;
                    if (this.isPending(key)) return;

                    this.request(key, 'PUT', config.updateUrl.replace('__module__', module.module), {[type]: 'clear'})
                        .then(() => module[type].device = null)
                        .catch(() => {});
                },

                deleteData() {
                    const module = this.deleteTarget;
                    if (this.isPending('delete')) return;

                    this.request('delete', 'DELETE', config.deleteUrl.replace('__module__', module.module))
                        .then(() => {
                            module.has_data = false;
                            this.deleteTarget = null;
                            toastr.success('{{ __('Module data deleted') }}');
                        })
                        .catch(() => {});
                },
            }));
        });
    </script>
@endpush
