@extends('layouts.librenmsv1')

@section('title', __('Device Dependencies'))

@php
    $btn = 'tw:inline-flex tw:items-center tw:gap-1.5 tw:px-3 tw:py-1.5 tw:rounded-md tw:text-sm tw:font-medium tw:transition-colors tw:disabled:opacity-50 tw:disabled:cursor-not-allowed';
    $btnPrimary = $btn . ' tw:bg-blue-600 tw:text-white tw:hover:bg-blue-700 tw:dark:hover:bg-blue-500';
    $btnDanger = $btn . ' tw:bg-red-600 tw:text-white tw:hover:bg-red-700 tw:dark:hover:bg-red-500';
    $btnSecondary = $btn . ' tw:bg-gray-100 tw:text-gray-700 tw:hover:bg-gray-200 tw:dark:bg-dark-gray-300 tw:dark:text-dark-white-100 tw:dark:hover:bg-dark-gray-200';
    $tab = 'tw:px-4 tw:py-2 tw:-mb-px tw:text-sm tw:font-medium tw:border-b-2';
@endphp

@section('content')
<div class="container-fluid" x-data="deviceDependencies">
    <x-panel>
        <x-slot:heading class="tw:flex tw:items-center tw:justify-between">
            <h3 class="panel-title">{{ __('Device Dependencies') }}</h3>
            <button type="button" class="{{ $btnPrimary }}" x-on:click="openManage()">
                <i class="fa fa-sitemap" aria-hidden="true"></i> {{ __('Manage Device Dependencies') }}
            </button>
        </x-slot:heading>
        <x-slot:table>
            <div class="table-responsive">
                <table id="hostdeps" class="table table-hover table-condensed table-striped">
                    <thead>
                        <tr>
                            <th data-column-id="device_id" data-type="numeric" data-width="80px">{{ __('Id') }}</th>
                            <th data-column-id="hostname">{{ __('Hostname') }}</th>
                            <th data-column-id="parents" data-sortable="false">{{ __('Parent Device(s)') }}</th>
                            <th data-column-id="actions" data-sortable="false" data-searchable="false" data-formatter="actions" data-width="100px">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </x-slot:table>
    </x-panel>

    <x-modal show="editOpen" :title="__('Edit Dependency')" maxWidth="xl">
        <p>{{ __('Parent devices of') }} <strong x-text="device.name"></strong></p>
        <select multiple x-ref="editParents" style="width: 100%"></select>
        <x-slot:footer>
            <button type="button" class="{{ $btnSecondary }}" x-on:click="editOpen = false">{{ __('Cancel') }}</button>
            <button type="button" class="{{ $btnPrimary }}" x-on:click="saveEdit()" :disabled="saving">{{ __('Save') }}</button>
        </x-slot:footer>
    </x-modal>

    <x-modal show="deleteOpen" :title="__('Confirm Delete')" maxWidth="md">
        <p>{{ __('Remove all parent devices from') }} <strong x-text="device.name"></strong>?</p>
        <x-slot:footer>
            <button type="button" class="{{ $btnSecondary }}" x-on:click="deleteOpen = false">{{ __('Cancel') }}</button>
            <button type="button" class="{{ $btnDanger }}" x-on:click="confirmDelete()" :disabled="saving">{{ __('Delete') }}</button>
        </x-slot:footer>
    </x-modal>

    <x-modal show="manageOpen" :title="__('Device Dependency for Multiple Devices')" maxWidth="xl">
        <div class="tw:flex tw:border-b tw:border-gray-200 tw:dark:border-dark-gray-200" role="tablist">
            <button type="button" role="tab" class="{{ $tab }}" x-on:click="manageTab = 'add'" :aria-selected="manageTab === 'add'"
                    :class="manageTab === 'add' ? 'tw:border-blue-600 tw:text-blue-600 tw:dark:text-blue-400' : 'tw:border-transparent tw:text-gray-500 tw:hover:text-gray-700 tw:dark:hover:text-dark-white-100'">
                {{ __('Bulk Add') }}
            </button>
            <button type="button" role="tab" class="{{ $tab }}" x-on:click="manageTab = 'clear'" :aria-selected="manageTab === 'clear'"
                    :class="manageTab === 'clear' ? 'tw:border-blue-600 tw:text-blue-600 tw:dark:text-blue-400' : 'tw:border-transparent tw:text-gray-500 tw:hover:text-gray-700 tw:dark:hover:text-dark-white-100'">
                {{ __('Clear All') }}
            </button>
        </div>
        <div x-show="manageTab === 'add'" class="tw:space-y-4">
            <p>{{ __('Set the parent devices of the selected child devices. Leaving the parent empty will clear their dependencies.') }}</p>
            <div>
                <label class="tw:block tw:font-medium tw:mb-1">{{ __('Parent Hosts') }}</label>
                <select multiple x-ref="bulkParents" style="width: 100%"></select>
            </div>
            <div>
                <label class="tw:block tw:font-medium tw:mb-1">{{ __('Child Hosts') }}</label>
                <select multiple x-ref="bulkChildren" style="width: 100%"></select>
            </div>
        </div>
        <div x-show="manageTab === 'clear'" x-cloak class="tw:space-y-4">
            <p>{{ __('Select parent devices to remove all of their child dependencies.') }}</p>
            <div>
                <label class="tw:block tw:font-medium tw:mb-1">{{ __('Parent Hosts') }}</label>
                <select multiple x-ref="clearParents" style="width: 100%"></select>
            </div>
        </div>
        <x-slot:footer>
            <button type="button" class="{{ $btnSecondary }}" x-on:click="manageOpen = false">{{ __('Cancel') }}</button>
            <button type="button" x-show="manageTab === 'add'" class="{{ $btnPrimary }}" x-on:click="saveBulk()" :disabled="saving">{{ __('Save') }}</button>
            <button type="button" x-show="manageTab === 'clear'" x-cloak class="{{ $btnDanger }}" x-on:click="clearChildren()" :disabled="saving">{{ __('Clear') }}</button>
        </x-slot:footer>
    </x-modal>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('deviceDependencies', () => ({
            editOpen: false,
            deleteOpen: false,
            manageOpen: false,
            manageTab: 'add',
            saving: false,
            device: {id: null, name: ''},

            init() {
                const select = (ref, placeholder, data = {}) => init_select2(this.$refs[ref], 'device', data, null, placeholder, {
                    dropdownParent: this.$refs[ref].parentElement,
                    width: '100%',
                });
                select('editParents', @js(__('None')), (params) => ({term: params.term, page: params.page || 1, exclude: this.device.id}));
                select('bulkParents', @js(__('None')));
                select('bulkChildren', '');
                select('clearParents', '');
            },

            openEdit(button) {
                this.device = {id: Number(button.dataset.deviceId), name: button.dataset.name};
                this.setSelected('editParents', JSON.parse(button.dataset.parents));
                this.editOpen = true;
            },

            openDelete(button) {
                this.device = {id: Number(button.dataset.deviceId), name: button.dataset.name};
                this.deleteOpen = true;
            },

            openManage() {
                ['bulkParents', 'bulkChildren', 'clearParents'].forEach((ref) => this.setSelected(ref, []));
                this.manageTab = 'add';
                this.manageOpen = true;
            },

            setSelected(ref, items) {
                const select = this.$refs[ref];
                select.replaceChildren(...items.map((item) => new Option(item.text, item.id, true, true)));
                select.dispatchEvent(new Event('change'));
            },

            selected(ref) {
                return Array.from(this.$refs[ref].selectedOptions, (option) => option.value);
            },

            async saveEdit() {
                if (await this.setParents([this.device.id], this.selected('editParents'))) {
                    this.editOpen = false;
                }
            },

            async confirmDelete() {
                if (await this.setParents([this.device.id], [])) {
                    this.deleteOpen = false;
                }
            },

            async saveBulk() {
                if (await this.setParents(this.selected('bulkChildren'), this.selected('bulkParents'))) {
                    this.manageOpen = false;
                }
            },

            async clearChildren() {
                if (await this.send('delete', @js(route('device-dependencies.destroy')), {parent_ids: this.selected('clearParents')})) {
                    this.manageOpen = false;
                }
            },

            setParents(device_ids, parent_ids) {
                return this.send('put', @js(route('device-dependencies.update')), {device_ids, parent_ids});
            },

            async send(method, url, data) {
                this.saving = true;
                try {
                    const response = await axios({method, url, data});
                    toastr.success(response.data.message);
                    $('#hostdeps').bootgrid('reload');

                    return true;
                } catch (error) {
                    toastr.error(this.errorMessage(error));

                    return false;
                } finally {
                    this.saving = false;
                }
            },

            errorMessage(error) {
                const data = error.response?.data;
                const messages = data?.errors ? Object.values(data.errors).flat() : [data?.message || @js(__('The device dependency could not be saved.'))];
                const escape = (text) => Object.assign(document.createElement('div'), {textContent: text}).innerHTML;

                return messages.map(escape).join('<br />');
            },
        }));
    });

    $('#hostdeps').bootgrid({
        rowCount: [50, 100, 250, -1],
        ajax: true,
        url: @js(route('table.device-dependencies')),
        formatters: {
            actions: function (column, row) {
                const button = (icon, label, action, classes) => {
                    const el = document.createElement('button');
                    el.type = 'button';
                    el.className = 'tw:px-2 tw:py-1 tw:rounded-md tw:text-white tw:disabled:opacity-40 tw:disabled:cursor-not-allowed ' + classes;
                    el.setAttribute('aria-label', label);
                    el.setAttribute('title', label);
                    el.setAttribute('x-on:click', action + '($el)');
                    el.dataset.deviceId = row.device_id;
                    el.dataset.name = row.display_name;
                    el.innerHTML = '<i class="fa fa-' + icon + '" aria-hidden="true"></i>';

                    return el;
                };
                const edit = button('pencil', @js(__('Edit')), 'openEdit', 'tw:bg-blue-600 tw:hover:bg-blue-700');
                edit.dataset.parents = row.parents_json;
                const remove = button('trash', @js(__('Delete')), 'openDelete', 'tw:bg-red-600 tw:hover:bg-red-700');
                remove.disabled = row.parents_json === '[]';

                return '<div class="tw:flex tw:gap-1.5">' + edit.outerHTML + remove.outerHTML + '</div>';
            }
        }
    });
</script>
@endpush
