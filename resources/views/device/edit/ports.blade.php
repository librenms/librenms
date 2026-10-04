@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
        <x-device.edit-tabs :device="$device" tab="ports" />

        <div x-data="devicePorts(@js([
                'selectedPorts' => $selected_ports,
                'summary' => $summary,
                'listUrl' => route('device.edit.ports.list', $device),
                'updateUrl' => route('device.edit.ports.update', [$device, '__port__']),
                'settingsUrl' => route('device.edit.ports.settings', $device),
                'bulkUrl' => route('device.edit.ports.bulk', $device),
                'resetStateUrl' => route('device.edit.ports.reset-state', $device),
                'portGroupUrl' => url('port'),
                'lang' => [
                    'request_failed' => __('Request failed'),
                    'device_override' => __('Device override'),
                    'os_default' => __('OS default'),
                    'global_default' => __('Global default'),
                    'use_default_on' => __('Use default (on)'),
                    'use_default_off' => __('Use default (off)'),
                    'enabled' => __('Enabled'),
                    'disabled' => __('Disabled'),
                    'unset' => __('Unset'),
                    'no_group' => __('No Group'),
                    'global' => __('Global'),
                    'os' => __('OS'),
                    'device' => __('Device'),
                ],
            ]))">

            @unless ($ports_module->isEnabled())
                <div class="alert alert-warning">
                    <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                    {{ __('The ports polling module is disabled for this device, no ports will be polled.') }}
                    <a href="{{ route('device.edit.modules', $device) }}">{{ __('Edit modules') }}</a>
                </div>
            @endunless

            <x-panel class="tw:mb-4">
                <div class="tw:flex tw:flex-wrap tw:items-start tw:justify-between tw:gap-6">
                    <div class="tw:flex tw:items-start tw:gap-4 tw:max-w-2xl">
                        <x-toggle ::checked="selectedPorts.device ?? selectedPorts.os ?? selectedPorts.global"
                                  aria-label="{{ __('Selected port polling') }}"
                                  x-on:change="setSelectedPorts($event.target)" />
                        <div>
                            <div class="tw:font-semibold tw:text-gray-900 tw:dark:text-dark-white-100">{{ __('Selected port polling') }}</div>
                            <p class="tw:m-0 tw:mt-1 tw:text-gray-500 tw:dark:text-dark-white-400">
                                {{ __('Only fetch statistics for ports that are up. Ports that are down or admin down are skipped, which speeds up polling devices with many unused ports.') }}
                            </p>
                            <div class="tw:flex tw:flex-wrap tw:items-center tw:gap-3 tw:mt-1">
                                <span class="tw:font-medium"
                                      :class="selectedPorts.device === null ? 'tw:text-gray-500 tw:dark:text-dark-white-400' : 'tw:text-amber-600 tw:dark:text-amber-400'"
                                      :title="sourceDetails(selectedPorts)"
                                      x-text="sourceLabel(selectedPorts)"></span>
                                <button type="button"
                                        x-show="selectedPorts.device !== null"
                                        x-on:click="clearSelectedPorts()"
                                        class="tw:p-0 tw:text-blue-600 tw:dark:text-blue-400 tw:hover:underline tw:bg-transparent tw:border-0">
                                    <i class="fa fa-rotate-left" aria-hidden="true"></i> <span x-text="resetLabel(selectedPorts)"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="tw:flex tw:flex-wrap tw:gap-2" role="group" aria-label="{{ __('Port polling summary') }}">
                        @foreach ([
                            'polled' => [__('Polled'), 'tw:text-green-700 tw:dark:text-green-400'],
                            'skipped' => [__('Skipped while down'), 'tw:text-amber-600 tw:dark:text-amber-400'],
                            'disabled' => [__('Polling disabled'), 'tw:text-gray-700 tw:dark:text-dark-white-200'],
                            'deleted' => [__('Deleted'), 'tw:text-gray-700 tw:dark:text-dark-white-200'],
                            'ignored' => [__('Ignored'), 'tw:text-gray-700 tw:dark:text-dark-white-200'],
                        ] as $key => [$label, $color])
                            <button type="button"
                                    @if ($key === 'skipped') x-show="isSelected()" @endif
                                    x-on:click="setFilter('{{ $key }}')"
                                    :class="filter === '{{ $key }}' ? 'tw:border-blue-500 tw:dark:border-blue-400' : 'tw:border-gray-200 tw:dark:border-dark-gray-200'"
                                    class="tw:flex tw:flex-col tw:items-start tw:min-w-24 tw:px-3 tw:py-2 tw:rounded-lg tw:border tw:bg-transparent tw:hover:bg-gray-50 tw:dark:hover:bg-dark-gray-400 tw:transition-colors">
                                <span class="tw:text-xl tw:font-semibold {{ $color }}" x-text="summary.{{ $key }}"></span>
                                <span class="tw:text-gray-500 tw:dark:text-dark-white-400">{{ $label }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </x-panel>

            <x-panel class="tw:mb-0">
                <x-slot name="heading" class="tw:flex tw:flex-wrap tw:items-center tw:justify-between tw:gap-3">
                    <div class="tw:flex tw:flex-wrap tw:items-center tw:gap-3">
                        <input type="search"
                               x-model.debounce.300ms="search"
                               placeholder="{{ __('Search name, description or index') }}"
                               aria-label="{{ __('Search ports') }}"
                               class="form-control input-sm tw:w-64">
                        <select x-model="filter" class="form-control input-sm tw:w-auto" aria-label="{{ __('Filter ports') }}">
                            @foreach ([
                                'all' => __('All ports'),
                                'polled' => __('Polled'),
                                'not_polled' => __('Not polled'),
                                'skipped' => __('Skipped while down'),
                                'up' => __('Up'),
                                'down' => __('Down'),
                                'admin_down' => __('Admin down'),
                                'disabled' => __('Polling disabled'),
                                'ignored' => __('Ignored'),
                                'deleted' => __('Deleted'),
                            ] as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="tw:flex tw:flex-wrap tw:items-center tw:gap-2">
                        <div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" :disabled="total === 0">
                                <span x-text="@js(__('Change :count ports', ['count' => '__count__'])).replace('__count__', total)"></span> <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li><a href="#" x-on:click.prevent="bulkAction = 'disable'">{{ __('Disable polling') }}</a></li>
                                <li><a href="#" x-on:click.prevent="bulkAction = 'enable'">{{ __('Enable polling') }}</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="#" x-on:click.prevent="bulkAction = 'ignore'">{{ __('Ignore alerts') }}</a></li>
                                <li><a href="#" x-on:click.prevent="bulkAction = 'unignore'">{{ __('Stop ignoring alerts') }}</a></li>
                            </ul>
                        </div>
                        <button type="button"
                                class="btn btn-default btn-sm"
                                x-on:click="confirmReset = true"
                                title="{{ __('Reset interface speed, admin up/down, and link up/down history, clearing associated alarms') }}">
                            <i class="fa fa-recycle" aria-hidden="true"></i> {{ __('Reset port state') }}
                        </button>
                    </div>
                </x-slot>

                <x-slot name="table">
                    <div class="tw:overflow-x-auto" :class="loading && 'tw:opacity-60'">
                        <table class="tw:w-full">
                            <thead>
                            <tr class="tw:border-b tw:border-gray-200 tw:dark:border-dark-gray-200 tw:text-left tw:uppercase tw:tracking-wider tw:text-gray-500 tw:dark:text-dark-white-400">
                                @foreach (['ifName' => __('Port'), 'ifOperStatus' => __('Status')] as $sortKey => $label)
                                    <th class="tw:px-3 tw:py-2 tw:font-semibold tw:whitespace-nowrap">
                                        <button type="button" x-on:click="sortBy('{{ $sortKey }}')" class="tw:p-0 tw:bg-transparent tw:border-0 tw:uppercase tw:font-semibold">
                                            {{ $label }} <i class="fa" :class="sortIcon('{{ $sortKey }}')" aria-hidden="true"></i>
                                        </button>
                                    </th>
                                @endforeach
                                <th class="tw:px-3 tw:py-2 tw:font-semibold tw:whitespace-nowrap">{{ __('Polling') }}</th>
                                <th class="tw:px-3 tw:py-2 tw:font-semibold tw:whitespace-nowrap" title="{{ __('Stop polling this port') }}">{{ __('Disable') }}</th>
                                <th class="tw:px-3 tw:py-2 tw:font-semibold tw:whitespace-nowrap" title="{{ __('Ignore this port in alerts') }}">{{ __('Ignore') }}</th>
                                <th class="tw:px-3 tw:py-2 tw:font-semibold tw:whitespace-nowrap">
                                    <button type="button" x-on:click="sortBy('ifSpeed')" class="tw:p-0 tw:bg-transparent tw:border-0 tw:uppercase tw:font-semibold">
                                        {{ __('Speed (bit/s)') }} <i class="fa" :class="sortIcon('ifSpeed')" aria-hidden="true"></i>
                                    </button>
                                </th>
                                <th class="tw:px-3 tw:py-2 tw:font-semibold">
                                    <button type="button" x-on:click="sortBy('ifAlias')" class="tw:p-0 tw:bg-transparent tw:border-0 tw:uppercase tw:font-semibold">
                                        {{ __('Description') }} <i class="fa" :class="sortIcon('ifAlias')" aria-hidden="true"></i>
                                    </button>
                                </th>
                                <th class="tw:px-3 tw:py-2 tw:font-semibold">{{ __('Port Groups') }}</th>
                                <th class="tw:px-3 tw:py-2 tw:font-semibold tw:whitespace-nowrap" title="{{ __('Tune the RRD max value to the port speed') }}">{{ __('RRD Tune') }}</th>
                            </tr>
                            </thead>
                            <tbody class="tw:divide-y tw:divide-gray-100 tw:dark:divide-dark-gray-300">
                            <template x-for="port in ports" :key="port.port_id">
                                <tr class="tw:hover:bg-gray-50 tw:dark:hover:bg-dark-gray-400">
                                    <td class="tw:px-3 tw:py-2">
                                        <a :href="port.url" class="tw:font-semibold" :class="port.deleted && 'tw:line-through'" x-text="port.label"></a>
                                        <div class="tw:text-gray-500 tw:dark:text-dark-white-400 tw:whitespace-nowrap">
                                            {{ __('Index') }} <span x-text="port.ifIndex"></span><span x-show="port.ifDescr && port.ifDescr !== port.label" x-text="' · ' + port.ifDescr"></span>
                                        </div>
                                    </td>
                                    <td class="tw:px-3 tw:py-2 tw:whitespace-nowrap">
                                        <span class="label" :class="statusClass(port)" x-text="statusText(port)"></span>
                                    </td>
                                    <td class="tw:px-3 tw:py-2 tw:whitespace-nowrap">
                                        <span class="tw:inline-flex tw:items-center tw:gap-1.5 tw:font-medium" :class="pollingClass(port)" :title="pollingTitle(port)">
                                            <i class="fa" :class="port.polling === 'polled' ? 'fa-circle-check' : (port.polling === 'down' || port.polling === 'admin_down' ? 'fa-circle-pause' : 'fa-circle-minus')" aria-hidden="true"></i>
                                            <span x-text="pollingText(port)"></span>
                                        </span>
                                    </td>
                                    <td class="tw:px-3 tw:py-2">
                                        <x-toggle size="sm" ::checked="port.disabled" ::aria-label="'{{ __('Disable polling') }} ' + port.label" x-on:change="save(port, {disabled: $event.target.checked}, $event.target)" />
                                    </td>
                                    <td class="tw:px-3 tw:py-2">
                                        <x-toggle size="sm" ::checked="port.ignore" ::aria-label="'{{ __('Ignore') }} ' + port.label" x-on:change="save(port, {ignore: $event.target.checked}, $event.target)" />
                                    </td>
                                    <td class="tw:px-3 tw:py-2">
                                        <div class="tw:relative tw:w-36">
                                            <input type="text" inputmode="numeric" pattern="[0-9]*"
                                                   class="form-control input-sm"
                                                   :class="port.ifSpeed_override && 'tw:pr-7'"
                                                   :value="port.ifSpeed"
                                                   :aria-label="'{{ __('Speed') }} ' + port.label"
                                                   x-on:keydown.enter="$event.target.blur()"
                                                   x-on:change="saveSpeed(port, $event.target)">
                                            <i x-show="port.ifSpeed_override" class="fa fa-pencil tw:absolute tw:right-2 tw:top-2 tw:text-amber-600 tw:dark:text-amber-400" title="{{ __('Manually set, clear to use the speed reported by the device') }}" aria-hidden="true"></i>
                                        </div>
                                        <div class="tw:text-gray-500 tw:dark:text-dark-white-400" x-text="formatSpeed(port.ifSpeed)"></div>
                                    </td>
                                    <td class="tw:px-3 tw:py-2">
                                        <div class="tw:relative tw:min-w-48">
                                            <input type="text"
                                                   class="form-control input-sm"
                                                   :class="port.ifAlias_override && 'tw:pr-7'"
                                                   :value="port.ifAlias"
                                                   :aria-label="'{{ __('Description') }} ' + port.label"
                                                   x-on:keydown.enter="$event.target.blur()"
                                                   x-on:change="save(port, {ifAlias: $event.target.value}, $event.target)">
                                            <i x-show="port.ifAlias_override" class="fa fa-pencil tw:absolute tw:right-2 tw:top-2 tw:text-amber-600 tw:dark:text-amber-400" title="{{ __('Manually set, clear to use the description reported by the device') }}" aria-hidden="true"></i>
                                        </div>
                                    </td>
                                    <td class="tw:px-3 tw:py-2 tw:min-w-40">
                                        <select class="input-sm" multiple x-init="initGroups($el, port)" :aria-label="'{{ __('Port Groups') }} ' + port.label"></select>
                                    </td>
                                    <td class="tw:px-3 tw:py-2">
                                        <x-toggle size="sm" ::checked="port.rrd_tune" ::aria-label="'{{ __('RRD Tune') }} ' + port.label" x-on:change="save(port, {rrd_tune: $event.target.checked}, $event.target)" />
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="! loading && ports.length === 0" x-cloak>
                                <td colspan="9" class="tw:px-4 tw:py-8 tw:text-center tw:text-gray-500 tw:dark:text-dark-white-400">{{ __('No ports match') }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </x-slot>

                <x-slot name="footer" class="tw:flex tw:flex-wrap tw:items-center tw:justify-between tw:gap-3">
                    <div class="tw:text-gray-500 tw:dark:text-dark-white-400" x-show="total > 0" x-text="'{{ __('Showing') }} ' + firstItem + '–' + lastItem + ' {{ __('of') }} ' + total"></div>
                    <div class="tw:flex tw:items-center tw:gap-2 tw:ml-auto">
                        <select x-model.number="perPage" class="form-control input-sm tw:w-auto" aria-label="{{ __('Ports per page') }}">
                            @foreach ([25, 50, 100, 250, 1000] as $count)
                                <option value="{{ $count }}">{{ $count }}</option>
                            @endforeach
                        </select>
                        <div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm" x-on:click="goTo(page - 1)" :disabled="page <= 1" aria-label="{{ __('Previous page') }}"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>
                            <span class="btn btn-default btn-sm disabled" x-text="page + ' / ' + lastPage"></span>
                            <button type="button" class="btn btn-default btn-sm" x-on:click="goTo(page + 1)" :disabled="page >= lastPage" aria-label="{{ __('Next page') }}"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>
                        </div>
                    </div>
                </x-slot>
            </x-panel>

            <x-modal show="bulkAction" maxWidth="md">
                <x-slot name="heading">
                    <h4 class="tw:m-0 tw:text-base tw:font-semibold" x-text="bulkTitle()"></h4>
                </x-slot>
                <p class="tw:m-0">
                    <span x-text="@js(__('This applies to all :count ports matching the current search and filter, including ports on other pages.', ['count' => '__count__'])).replace('__count__', total)"></span>
                </p>
                <x-slot name="footer">
                    <button type="button" class="lnms-btn lnms-btn-default tw:px-3 tw:py-1.5" x-on:click="bulkAction = null">{{ __('Cancel') }}</button>
                    <button type="button" class="lnms-btn lnms-btn-primary tw:px-3 tw:py-1.5" x-on:click="applyBulk()" :disabled="isPending('bulk')">{{ __('Apply') }}</button>
                </x-slot>
            </x-modal>

            <x-modal show="confirmReset" maxWidth="md">
                <x-slot name="heading">
                    <h4 class="tw:m-0 tw:text-base tw:font-semibold">{{ __('Reset port state?') }}</h4>
                </x-slot>
                <p class="tw:m-0">{{ __('This clears the previous speed, admin status and link status of all ports on this device, which clears alerts caused by port changes.') }}</p>
                <x-slot name="footer">
                    <button type="button" class="lnms-btn lnms-btn-default tw:px-3 tw:py-1.5" x-on:click="confirmReset = false">{{ __('Cancel') }}</button>
                    <button type="button" class="lnms-btn lnms-btn-danger tw:px-3 tw:py-1.5" x-on:click="resetState()" :disabled="isPending('reset')">{{ __('Reset') }}</button>
                </x-slot>
            </x-modal>
        </div>
    </x-device.page>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('devicePorts', (config) => ({
                selectedPorts: config.selectedPorts,
                summary: config.summary,
                lang: config.lang,
                ports: [],
                search: '',
                filter: 'all',
                sort: 'ifIndex',
                order: 'asc',
                page: 1,
                lastPage: 1,
                perPage: 50,
                total: 0,
                loading: false,
                loadId: 0,
                bulkAction: null,
                confirmReset: false,
                pending: {},
                bulkTitles: {
                    disable: @js(__('Disable polling')),
                    enable: @js(__('Enable polling')),
                    ignore: @js(__('Ignore alerts')),
                    unignore: @js(__('Stop ignoring alerts')),
                },
                pollingLabels: {
                    polled: @js(__('Polled')),
                    down: @js(__('Skipped (down)')),
                    admin_down: @js(__('Skipped (admin down)')),
                    disabled: @js(__('Polling disabled')),
                    deleted: @js(__('Deleted')),
                },
                pollingTitles: {
                    polled: @js(__('Statistics are collected for this port')),
                    down: @js(__('Selected port polling skips ports that are down')),
                    admin_down: @js(__('Selected port polling skips ports that are admin down')),
                    disabled: @js(__('Polling is disabled for this port')),
                    deleted: @js(__('This port was not found on the device during the last discovery')),
                },

                init() {
                    this.$watch('search', () => this.goTo(1));
                    this.$watch('filter', () => this.goTo(1));
                    this.$watch('perPage', () => this.goTo(1));
                    this.load();
                },

                get firstItem() {
                    return (this.page - 1) * this.perPage + 1;
                },

                get lastItem() {
                    return Math.min(this.page * this.perPage, this.total);
                },

                isSelected() {
                    return this.selectedPorts.device ?? this.selectedPorts.os ?? this.selectedPorts.global;
                },

                isPending(key) {
                    return this.pending[key] === true;
                },

                request(key, method, url, data = null) {
                    this.pending[key] = true;

                    return fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: data === null ? undefined : JSON.stringify(data),
                    }).then(async (response) => {
                        const body = await response.json().catch(() => ({}));
                        if (! response.ok) {
                            throw new Error(body.message ?? this.lang.request_failed);
                        }

                        return body;
                    }).catch((error) => {
                        toastr.error(error.message);
                        throw error;
                    }).finally(() => delete this.pending[key]);
                },

                filterParams() {
                    return {search: this.search, filter: this.filter};
                },

                load() {
                    const params = new URLSearchParams({
                        ...this.filterParams(),
                        sort: this.sort,
                        order: this.order,
                        page: this.page,
                        per_page: this.perPage,
                    });

                    const loadId = ++this.loadId;
                    this.loading = true;
                    this.request('load', 'GET', config.listUrl + '?' + params)
                        .then((data) => {
                            // a newer request was started, ignore this response
                            if (loadId !== this.loadId) return;
                            this.ports = data.ports;
                            this.page = data.page;
                            this.lastPage = data.last_page;
                            this.total = data.total;
                            this.summary = data.summary;
                        })
                        .catch(() => {})
                        .finally(() => this.loading = loadId !== this.loadId);
                },

                goTo(page) {
                    this.page = Math.max(1, Math.min(page, this.lastPage));
                    this.load();
                },

                setFilter(filter) {
                    this.filter = this.filter === filter ? 'all' : filter;
                },

                sortBy(column) {
                    this.order = this.sort === column && this.order === 'asc' ? 'desc' : 'asc';
                    this.sort = column;
                    this.goTo(1);
                },

                sortIcon(column) {
                    if (this.sort !== column) return 'fa-sort tw:opacity-30';
                    return this.order === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
                },

                save(port, data, input) {
                    const key = 'port' + port.port_id + Object.keys(data)[0];
                    const revert = () => {
                        if (input.type === 'checkbox') {
                            input.checked = ! input.checked;
                        } else {
                            input.value = port[Object.keys(data)[0]] ?? '';
                        }
                    };
                    if (this.isPending(key)) {
                        revert();
                        return;
                    }

                    this.request(key, 'PATCH', config.updateUrl.replace('__port__', port.port_id), data)
                        .then((response) => {
                            Object.assign(port, response.port);
                            this.summary = response.summary;
                            toastr.success(response.message);
                        })
                        .catch(revert);
                },

                saveSpeed(port, input) {
                    input.value = input.value.replace(/[^0-9]/g, '');
                    this.save(port, {ifSpeed: input.value === '' ? null : Number(input.value)}, input);
                },

                setSelectedPorts(input) {
                    this.updateSelectedPorts(input.checked ? 'true' : 'false')
                        .catch(() => input.checked = ! input.checked);
                },

                clearSelectedPorts() {
                    this.updateSelectedPorts('clear').catch(() => {});
                },

                updateSelectedPorts(value) {
                    return this.request('settings', 'PUT', config.settingsUrl, {selected_ports: value})
                        .then((response) => {
                            this.selectedPorts = response.selected_ports;
                            this.summary = response.summary;
                            toastr.success(response.message);
                            this.load();
                        });
                },

                applyBulk() {
                    if (this.isPending('bulk')) return;

                    this.request('bulk', 'POST', config.bulkUrl, {...this.filterParams(), action: this.bulkAction})
                        .then((response) => {
                            this.bulkAction = null;
                            toastr.success(response.message);
                            this.load();
                        })
                        .catch(() => {});
                },

                bulkTitle() {
                    return (this.bulkTitles[this.bulkAction] ?? '') + '?';
                },

                resetState() {
                    if (this.isPending('reset')) return;

                    this.request('reset', 'POST', config.resetStateUrl)
                        .then((response) => {
                            this.confirmReset = false;
                            toastr.success(response.message);
                        })
                        .catch(() => {});
                },

                initGroups(el, port) {
                    port.groups.forEach((group) => el.add(new Option(group.text, group.id, true, true)));
                    init_select2(el, 'port-group', {}, null, this.lang.no_group);

                    let last = JSON.stringify(port.groups.map((group) => String(group.id)));
                    $(el).on('change', () => {
                        const groups = $(el).val() ?? [];
                        // select2 may fire change multiple times
                        if (JSON.stringify(groups) === last) return;
                        last = JSON.stringify(groups);

                        this.request('groups' + port.port_id, 'PUT', config.portGroupUrl + '/' + port.port_id, {groups: groups})
                            .then((response) => toastr.success(response.message))
                            .catch(() => {});
                    });
                },

                statusText(port) {
                    if (port.ifAdminStatus === 'down') return 'admin down';
                    return port.ifOperStatus ?? 'unknown';
                },

                statusClass(port) {
                    if (port.ifAdminStatus === 'down') return 'label-default';
                    return port.ifOperStatus === 'up' ? 'label-success' : 'label-danger';
                },

                pollingText(port) {
                    return this.pollingLabels[port.polling];
                },

                pollingTitle(port) {
                    return this.pollingTitles[port.polling];
                },

                pollingClass(port) {
                    if (port.polling === 'polled') return 'tw:text-green-700 tw:dark:text-green-400';
                    if (port.polling === 'down' || port.polling === 'admin_down') return 'tw:text-amber-600 tw:dark:text-amber-400';
                    return 'tw:text-gray-500 tw:dark:text-dark-white-400';
                },

                formatSpeed(bps) {
                    if (! bps) return '';
                    const units = ['bps', 'Kbps', 'Mbps', 'Gbps', 'Tbps'];
                    let value = bps;
                    let unit = 0;
                    while (value >= 1000 && unit < units.length - 1) {
                        value /= 1000;
                        unit++;
                    }

                    return Math.round(value * 100) / 100 + ' ' + units[unit];
                },

                sourceLabel(setting) {
                    if (setting.device !== null) return this.lang.device_override;
                    if (setting.os !== null) return this.lang.os_default;
                    return this.lang.global_default;
                },

                resetLabel(setting) {
                    return (setting.os ?? setting.global) ? this.lang.use_default_on : this.lang.use_default_off;
                },

                sourceDetails(setting) {
                    const state = (value) => value === null ? this.lang.unset : (value ? this.lang.enabled : this.lang.disabled);

                    return this.lang.global + ': ' + state(setting.global) + '\n' + this.lang.os + ': ' + state(setting.os) + '\n' + this.lang.device + ': ' + state(setting.device);
                },
            }));
        });
    </script>
@endpush
