@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
        <x-device.edit-tabs :device="$device" tab="component" />

        <div x-data="deviceComponents(@js($components))">
            <x-panel>
                <x-slot:heading class="tw:flex tw:flex-wrap tw:items-center tw:justify-between tw:gap-2">
                    <input type="search" class="form-control tw:w-64" x-model="search" placeholder="{{ __('Filter components') }}" aria-label="{{ __('Filter components') }}">
                    @if($canUpdate)
                        <div class="tw:flex tw:flex-wrap tw:gap-2">
                            <button type="button" class="btn btn-default btn-sm" x-on:click="ignoreStatus(1)" title="{{ __('Ignore alerts on all components in a warning state') }}">{{ __('Ignore Warning') }}</button>
                            <button type="button" class="btn btn-default btn-sm" x-on:click="ignoreStatus(2)" title="{{ __('Ignore alerts on all components in a critical state') }}">{{ __('Ignore Critical') }}</button>
                            <button type="button" class="btn btn-default btn-sm" x-on:click="reset()" x-bind:disabled="! changed">{{ __('Reset') }}</button>
                            <button type="button" class="btn btn-primary btn-sm" x-on:click="save()" x-bind:disabled="! changed || saving">{{ __('Save') }}</button>
                        </div>
                    @endif
                </x-slot:heading>
                <x-slot:table>
                    <table class="table table-condensed table-striped table-hover">
                        <thead>
                        <tr>
                            <th>{{ __('ID') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Label') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>
                                {{ __('Disable') }}
                                @if($canUpdate)
                                    <input type="checkbox" x-on:change="setAll('disabled', $event.target.checked)" title="{{ __('Disable polling on all shown components') }}">
                                @endif
                            </th>
                            <th>
                                {{ __('Ignore alert tag') }}
                                @if($canUpdate)
                                    <input type="checkbox" x-on:change="setAll('ignore', $event.target.checked)" title="{{ __('Ignore alerts on all shown components') }}">
                                @endif
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <template x-for="component in shown" :key="component.id">
                            <tr>
                                <td x-text="component.id"></td>
                                <td x-text="component.type"></td>
                                <td x-text="component.label"></td>
                                <td>
                                    <span class="label" x-bind:class="['label-success', 'label-warning', 'label-danger'][component.status] ?? 'label-default'"
                                          x-text="[@js(__('Ok')), @js(__('Warning')), @js(__('Critical'))][component.status] ?? component.status"></span>
                                </td>
                                <td><input type="checkbox" x-model="component.disabled" @disabled(! $canUpdate)></td>
                                <td><input type="checkbox" x-model="component.ignore" @disabled(! $canUpdate)></td>
                            </tr>
                        </template>
                        <tr x-show="shown.length === 0"><td colspan="6"><em>{{ __('No components found') }}</em></td></tr>
                        </tbody>
                    </table>
                </x-slot:table>
            </x-panel>
        </div>
    </x-device.page>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('deviceComponents', (components) => ({
                components: structuredClone(components),
                saved: structuredClone(components),
                search: '',
                saving: false,

                get shown() {
                    const search = this.search.toLowerCase();
                    return this.components.filter((c) => ! search || `${c.type} ${c.label}`.toLowerCase().includes(search));
                },

                get changes() {
                    return Object.fromEntries(this.components
                        .filter((c, i) => c.disabled !== this.saved[i].disabled || c.ignore !== this.saved[i].ignore)
                        .map((c) => [c.id, {disabled: c.disabled, ignore: c.ignore}]));
                },

                get changed() {
                    return Object.keys(this.changes).length > 0;
                },

                setAll(field, value) {
                    this.shown.forEach((c) => c[field] = value);
                },

                ignoreStatus(status) {
                    this.components.filter((c) => c.status === status).forEach((c) => c.ignore = true);
                },

                reset() {
                    this.components = structuredClone(this.saved);
                },

                save() {
                    this.saving = true;
                    axios.put(@js(route('device.edit.components.update', $device)), {components: this.changes})
                        .then((response) => {
                            this.saved = structuredClone(this.components);
                            toastr.success(response.data.message);
                        })
                        .catch((error) => toastr.error(error.response?.data?.message ?? @js(__('Failed to save components'))))
                        .finally(() => this.saving = false);
                },
            }));
        });
    </script>
@endpush
