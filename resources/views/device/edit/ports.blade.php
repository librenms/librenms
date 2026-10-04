@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
        <x-device.edit-tabs :device="$device" tab="ports" />

        <div x-data="devicePorts(@js([
                'selectedPorts' => $selected_ports,
                'summary' => $summary,
                'filter' => $filter,
                'canCreateGroup' => $can_create_group,
                'listUrl' => route('device.edit.ports.list', $device),
                'updateUrl' => route('device.edit.ports.update', [$device, '__port__']),
                'settingsUrl' => route('device.edit.ports.settings', $device),
                'bulkUrl' => route('device.edit.ports.bulk', $device),
                'resetStateUrl' => route('device.edit.ports.reset-state', $device),
                'portUrl' => url('port'),
                'portGroupStoreUrl' => route('port-groups.store'),
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
                    'global' => __('Global'),
                    'os' => __('OS'),
                    'device' => __('Device'),
                    'no_group' => __('No Group'),
                    'select_group' => __('Port group'),
                    'create_group' => __('Create group'),
                    'next_poll' => __('Set on next poll'),
                    'none' => __('None'),
                    'after_poll' => __('Known after the next poll'),
                    'custom_speed' => __('Custom speed'),
                    'circuit' => __('Circuit'),
                    'out' => __('out'),
                    'in' => __('in'),
                    'selected_count' => __(':count selected', ['count' => '__count__']),
                    'page_selected' => __('All :count ports on this page are selected.', ['count' => '__count__']),
                    'select_all_matching' => __('Select all :count matching ports', ['count' => '__count__']),
                    'all_matching_selected' => __('All :count matching ports are selected.', ['count' => '__count__']),
                    'bulk_confirm' => __('This applies to all :count ports matching the current filter, including ports on other pages.', ['count' => '__count__']),
                    'showing' => __('Showing :first–:last of :total', ['first' => '__first__', 'last' => '__last__', 'total' => '__total__']),
                ],
                'bulkTitles' => [
                    'disable' => __('Disable polling'),
                    'enable' => __('Enable polling'),
                    'ignore' => __('Ignore alerts'),
                    'unignore' => __('Stop ignoring alerts'),
                    'add_group' => __('Add to group'),
                    'remove_group' => __('Remove from group'),
                ],
                'pollingLabels' => [
                    'polled' => __('Polled'),
                    'down' => __('Skipped (down)'),
                    'admin_down' => __('Skipped (admin down)'),
                    'disabled' => __('Polling disabled'),
                    'deleted' => __('Deleted'),
                ],
                'pollingTitles' => [
                    'polled' => __('Statistics are collected for this port'),
                    'down' => __('Selected port polling skips ports that are down'),
                    'admin_down' => __('Selected port polling skips ports that are admin down'),
                    'disabled' => __('Polling is disabled for this port'),
                    'deleted' => __('This port was not found on the device during the last discovery'),
                ],
            ]))"
             x-on:filter:apply.window="if ($event.detail.name === 'device.edit-ports') applyFilter($event.detail.filters)">

            @unless ($ports_module->isEnabled())
                <div class="tw:flex tw:flex-wrap tw:items-center tw:gap-2 tw:mb-4 tw:px-4 tw:py-3 tw:rounded-lg tw:border tw:border-amber-300 tw:bg-amber-50 tw:text-amber-800 tw:dark:border-amber-700 tw:dark:bg-amber-950/40 tw:dark:text-amber-200">
                    <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                    {{ __('The ports polling module is disabled for this device, no ports will be polled.') }}
                    <a href="{{ route('device.edit.modules', $device) }}" class="tw:underline">{{ __('Edit modules') }}</a>
                </div>
            @endunless

            <x-panel class="tw:mb-4">
                <div class="tw:flex tw:flex-wrap tw:items-start tw:justify-between tw:gap-6">
                    <div class="tw:flex tw:items-start tw:gap-4 tw:max-w-2xl">
                        <x-toggle ::checked="selectedPollingEnabled()"
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
                            'polled' => [__('Polled'), 'tw:text-green-700 tw:dark:text-green-400', ['polling' => ['eq' => 'polled']]],
                            'skipped' => [__('Skipped while down'), 'tw:text-amber-600 tw:dark:text-amber-400', ['polling' => ['eq' => 'skipped']]],
                            'disabled' => [__('Polling disabled'), 'tw:text-gray-700 tw:dark:text-dark-white-200', ['disabled' => ['eq' => 1], 'deleted' => ['eq' => 0]]],
                            'deleted' => [__('Deleted'), 'tw:text-gray-700 tw:dark:text-dark-white-200', ['deleted' => ['eq' => 1]]],
                            'ignored' => [__('Ignored'), 'tw:text-gray-700 tw:dark:text-dark-white-200', ['ignore' => ['eq' => 1], 'deleted' => ['eq' => 0]]],
                        ] as $key => [$label, $color, $chipFilter])
                            <a href="{{ route('device.edit.ports', ['device' => $device, 'filter' => $chipFilter]) }}"
                               @if ($key === 'skipped') x-show="selectedPollingEnabled()" @endif
                               @class([
                                   'tw:flex tw:flex-col tw:items-start tw:min-w-24 tw:px-3 tw:py-2 tw:rounded-lg tw:border tw:no-underline tw:hover:no-underline tw:hover:bg-gray-50 tw:dark:hover:bg-dark-gray-400 tw:transition-colors',
                                   'tw:border-blue-500 tw:dark:border-blue-400' => $filter == $chipFilter,
                                   'tw:border-gray-200 tw:dark:border-dark-gray-200' => $filter != $chipFilter,
                               ])>
                                <span class="tw:text-xl tw:font-semibold {{ $color }}" x-text="summary.{{ $key }}"></span>
                                <span class="tw:text-gray-500 tw:dark:text-dark-white-400">{{ $label }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </x-panel>

            <x-panel class="tw:mb-0">
                <x-slot name="heading" class="tw:flex tw:flex-wrap tw:items-center tw:justify-between tw:gap-3">
                    <x-filter name="device.edit-ports" :fields="$filter_fields" :initial="$filter" />
                    <button type="button"
                            class="lnms-btn lnms-btn-default"
                            x-on:click="confirmReset = true"
                            title="{{ __('Reset interface speed, admin up/down, and link up/down history, clearing associated alarms') }}">
                        <i class="fa fa-recycle" aria-hidden="true"></i> {{ __('Reset port state') }}
                    </button>
                </x-slot>

                <x-slot name="table">
                    <div x-show="selectionCount > 0" x-cloak
                         class="tw:flex tw:flex-wrap tw:items-center tw:gap-2 tw:px-4 tw:py-2 tw:border-b tw:border-blue-200 tw:dark:border-blue-900 tw:bg-blue-50 tw:dark:bg-blue-950/40">
                        <span class="tw:font-semibold tw:mr-2" x-text="lang.selected_count.replace('__count__', selectionCount)"></span>
                        <button type="button" class="lnms-btn lnms-btn-default" x-on:click="bulk('disable')">{{ __('Disable polling') }}</button>
                        <button type="button" class="lnms-btn lnms-btn-default" x-on:click="bulk('enable')">{{ __('Enable polling') }}</button>
                        <button type="button" class="lnms-btn lnms-btn-default" x-on:click="bulk('ignore')">{{ __('Ignore alerts') }}</button>
                        <button type="button" class="lnms-btn lnms-btn-default" x-on:click="bulk('unignore')">{{ __('Stop ignoring alerts') }}</button>
                        <div class="tw:flex tw:flex-wrap tw:items-center tw:gap-2">
                            <select x-init="initBulkGroup($el)" aria-label="{{ __('Port group') }}"></select>
                            <button type="button" class="lnms-btn lnms-btn-default tw:disabled:opacity-50" x-on:click="bulk('add_group')" :disabled="! bulkGroupId">{{ __('Add to group') }}</button>
                            <button type="button" class="lnms-btn lnms-btn-default tw:disabled:opacity-50" x-on:click="bulk('remove_group')" :disabled="! bulkGroupId">{{ __('Remove from group') }}</button>
                        </div>
                        <button type="button" class="tw:ml-auto tw:p-0 tw:border-0 tw:bg-transparent tw:text-blue-600 tw:hover:underline tw:dark:text-blue-400" x-on:click="clearSelection()">{{ __('Clear selection') }}</button>
                    </div>

                    <div x-show="pageSelected && total > ports.length" x-cloak
                         class="tw:px-4 tw:py-2 tw:text-center tw:border-b tw:border-gray-200 tw:dark:border-dark-gray-200 tw:bg-gray-50 tw:dark:bg-dark-gray-400">
                        <span x-text="(allMatching ? lang.all_matching_selected : lang.page_selected).replace('__count__', allMatching ? total : ports.length)"></span>
                        <button type="button"
                                class="tw:p-0 tw:border-0 tw:bg-transparent tw:text-blue-600 tw:hover:underline tw:dark:text-blue-400"
                                x-on:click="allMatching ? clearSelection() : allMatching = true"
                                x-text="allMatching ? @js(__('Clear selection')) : lang.select_all_matching.replace('__count__', total)"></button>
                    </div>

                    <div class="tw:overflow-x-auto" :class="loading && 'tw:opacity-60'">
                        <table class="tw:w-full tw:max-md:block">
                            <thead class="tw:max-md:block">
                            <tr class="tw:max-md:flex tw:*:px-3 tw:*:py-2 tw:*:font-semibold tw:*:whitespace-nowrap tw:border-b tw:border-gray-200 tw:dark:border-dark-gray-200 tw:text-left tw:uppercase tw:tracking-wider tw:text-gray-500 tw:dark:text-dark-white-400">
                                <th class="tw:w-8">
                                    <input type="checkbox"
                                           class="tw:m-0"
                                           :checked="pageSelected"
                                           x-effect="$el.indeterminate = ! pageSelected && pageHasSelection"
                                           x-on:change="selectPage($event.target.checked)"
                                           aria-label="{{ __('Select all ports on this page') }}">
                                </th>
                                @foreach (['ifName' => __('Port'), 'ifOperStatus' => __('Status')] as $sortKey => $label)
                                    <th @class(['tw:max-md:hidden' => $sortKey !== 'ifName'])>
                                        <button type="button" x-on:click="sortBy('{{ $sortKey }}')" class="tw:p-0 tw:bg-transparent tw:border-0 tw:uppercase tw:font-semibold">
                                            {{ $label }} <i class="fa" :class="sortIcon('{{ $sortKey }}')" aria-hidden="true"></i>
                                        </button>
                                    </th>
                                @endforeach
                                <th class="tw:max-md:hidden">{{ __('Polling') }}</th>
                                <th class="tw:max-md:hidden" title="{{ __('Stop polling this port') }}">{{ __('Disable') }}</th>
                                <th class="tw:max-md:hidden" title="{{ __('Ignore this port in alerts') }}">{{ __('Ignore') }}</th>
                                @foreach (['ifSpeed' => __('Speed'), 'ifAlias' => __('Description')] as $sortKey => $label)
                                    <th class="tw:max-md:hidden">
                                        <button type="button" x-on:click="sortBy('{{ $sortKey }}')" class="tw:p-0 tw:bg-transparent tw:border-0 tw:uppercase tw:font-semibold">
                                            {{ $label }} <i class="fa" :class="sortIcon('{{ $sortKey }}')" aria-hidden="true"></i>
                                        </button>
                                    </th>
                                @endforeach
                                <th class="tw:max-md:hidden">{{ __('Port Groups') }}</th>
                                <th class="tw:max-md:hidden" title="{{ __('Tune the RRD max value to the port speed') }}">{{ __('RRD Tune') }}</th>
                            </tr>
                            </thead>
                            <tbody class="tw:max-md:block tw:divide-y tw:divide-gray-100 tw:dark:divide-dark-gray-300">
                            <template x-for="port in ports" :key="port.port_id">
                                {{-- below md each row is a card: the checkbox column spans all 7 card rows and each cell shows its data-label --}}
                                <tr class="tw:*:px-3 tw:*:py-2 tw:max-md:grid tw:max-md:grid-cols-[auto_1fr_1fr] tw:max-md:gap-3 tw:max-md:px-4 tw:max-md:py-3 tw:max-md:*:p-0
                                           tw:max-md:[&>[data-label]]:before:block tw:max-md:[&>[data-label]]:before:mb-1 tw:max-md:[&>[data-label]]:before:content-[attr(data-label)] tw:max-md:[&>[data-label]]:before:uppercase tw:max-md:[&>[data-label]]:before:tracking-wider tw:max-md:[&>[data-label]]:before:text-gray-500"
                                    :class="isSelected(port) ? 'tw:bg-blue-50/60 tw:dark:bg-blue-950/30' : 'tw:hover:bg-gray-50 tw:dark:hover:bg-dark-gray-400'">
                                    <td class="tw:max-md:row-span-7 tw:max-md:pt-0.5">
                                        <input type="checkbox"
                                               class="tw:m-0"
                                               :checked="isSelected(port)"
                                               x-on:change="toggleSelected(port, $event.target.checked)"
                                               :aria-label="'{{ __('Select') }} ' + port.label">
                                    </td>
                                    <td class="tw:max-md:col-span-2">
                                        <a :href="port.url" class="tw:font-semibold" :class="port.deleted && 'tw:line-through'" x-text="port.label"></a>
                                        <div class="tw:text-gray-500 tw:dark:text-dark-white-400 tw:whitespace-nowrap">
                                            {{ __('Index') }} <span x-text="port.ifIndex"></span><span x-show="port.ifDescr && port.ifDescr !== port.label" x-text="' · ' + port.ifDescr"></span>
                                        </div>
                                    </td>
                                    <td class="tw:whitespace-nowrap" data-label="{{ __('Status') }}">
                                        <span class="tw:inline-block tw:px-1.5 tw:rounded-[3px] tw:font-semibold tw:text-white" :class="statusClass(port)" x-text="statusText(port)"></span>
                                    </td>
                                    <td class="tw:whitespace-nowrap" data-label="{{ __('Polling') }}">
                                        <span class="tw:inline-flex tw:items-center tw:gap-1.5 tw:font-medium" :class="pollingClass(port)" :title="config.pollingTitles[port.polling]">
                                            <i class="fa" :class="pollingIcon(port)" aria-hidden="true"></i>
                                            <span x-text="config.pollingLabels[port.polling]"></span>
                                        </span>
                                    </td>
                                    <td data-label="{{ __('Disable polling') }}">
                                        <x-toggle ::checked="port.disabled" ::aria-label="'{{ __('Disable polling') }} ' + port.label" x-on:change="save(port, {disabled: $event.target.checked}, $event.target)" />
                                    </td>
                                    <td data-label="{{ __('Ignore') }}">
                                        <x-toggle ::checked="port.ignore" ::aria-label="'{{ __('Ignore') }} ' + port.label" x-on:change="save(port, {ignore: $event.target.checked}, $event.target)" />
                                    </td>
                                    <td class="tw:max-md:col-span-2" data-label="{{ __('Speed') }}">
                                        <button type="button"
                                                class="tw:group tw:inline-flex tw:items-center tw:gap-2 tw:p-0 tw:border-0 tw:bg-transparent tw:text-left tw:whitespace-nowrap"
                                                x-on:click="editSpeed(port)"
                                                :aria-label="'{{ __('Edit speed') }} ' + port.label">
                                            <span :class="port.ifSpeed_override && 'tw:text-amber-600 tw:dark:text-amber-400'"
                                                  :title="port.ifSpeed_override ? lang.custom_speed : ''"
                                                  x-text="speedText(port.ifSpeed) || lang.next_poll"></span>
                                            <span x-show="port.circuit_speed"
                                                  class="tw:px-1.5 tw:rounded-[3px] tw:border"
                                                  :class="port.circuit_speed_override ? 'tw:border-amber-500 tw:text-amber-600 tw:dark:text-amber-400' : 'tw:border-gray-300 tw:text-gray-500 tw:dark:border-dark-gray-100 tw:dark:text-dark-white-400'"
                                                  :title="circuitText(port.circuit_speed)"
                                                  x-text="lang.circuit + ' ' + circuitText(port.circuit_speed, true)"></span>
                                            <i class="fa fa-pen tw:text-gray-400 tw:group-hover:text-blue-600 tw:dark:group-hover:text-blue-400" aria-hidden="true"></i>
                                        </button>
                                    </td>
                                    <td class="tw:max-md:col-span-2" data-label="{{ __('Description') }}">
                                        <div class="tw:relative tw:min-w-48">
                                            <input type="text"
                                                   class="lnms-input"
                                                   :class="port.ifAlias_override && 'tw:pr-8'"
                                                   :value="port.ifAlias"
                                                   :aria-label="'{{ __('Description') }} ' + port.label"
                                                   x-on:keydown.enter="$event.target.blur()"
                                                   x-on:keydown.escape="$event.target.value = port.ifAlias; $event.target.blur()"
                                                   x-on:change="save(port, {ifAlias: $event.target.value}, $event.target)">
                                            <button type="button"
                                                    x-show="port.ifAlias_override"
                                                    class="tw:absolute tw:right-1 tw:top-1 tw:h-[22px] tw:w-6 tw:p-0 tw:border-0 tw:rounded-[3px] tw:bg-transparent tw:text-amber-600 tw:hover:bg-amber-50"
                                                    x-on:click="save(port, {ifAlias: ''}, $el.previousElementSibling)"
                                                    title="{{ __('Custom description, click to use the description reported by the device') }}"
                                                    aria-label="{{ __('Use the description reported by the device') }}">
                                                <i class="fa fa-rotate-left" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="tw:max-md:col-span-2 tw:min-w-40" data-label="{{ __('Port Groups') }}">
                                        {{-- select2 is created on demand, creating one per row is slow on large pages, this uses the select2 markup so both look the same --}}
                                        <template x-if="! port.editingGroups">
                                            <span class="select2 select2-container select2-container--bootstrap tw:cursor-text"
                                                  style="width: 100%"
                                                  role="button"
                                                  tabindex="0"
                                                  x-on:click="port.editingGroups = true"
                                                  x-on:keydown.enter.prevent="port.editingGroups = true"
                                                  :aria-label="'{{ __('Edit port groups') }} ' + port.label">
                                                <span class="selection">
                                                    <span class="select2-selection select2-selection--multiple input-sm">
                                                        <ul class="select2-selection__rendered">
                                                            <template x-for="group in port.groups" :key="group.id">
                                                                <li class="select2-selection__choice" :title="group.text">
                                                                    <span class="select2-selection__choice__remove" role="button" x-on:click.stop="removeGroup(port, group)" :aria-label="'{{ __('Remove') }} ' + group.text">×</span><span x-text="group.text"></span>
                                                                </li>
                                                            </template>
                                                            <li class="select2-search select2-search--inline" x-show="port.groups.length === 0">
                                                                <input class="select2-search__field" :placeholder="lang.no_group" readonly tabindex="-1" style="width: 100%">
                                                            </li>
                                                        </ul>
                                                    </span>
                                                </span>
                                            </span>
                                        </template>
                                        <template x-if="port.editingGroups">
                                            <div>
                                                <select class="input-sm" multiple x-init="initGroups($el, port)" :aria-label="'{{ __('Port Groups') }} ' + port.label"></select>
                                            </div>
                                        </template>
                                    </td>
                                    <td data-label="{{ __('RRD Tune') }}">
                                        <x-toggle ::checked="port.rrd_tune" ::aria-label="'{{ __('RRD Tune') }} ' + port.label" x-on:change="save(port, {rrd_tune: $event.target.checked}, $event.target)" />
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="! loading && ports.length === 0" x-cloak class="tw:max-md:block">
                                <td colspan="10" class="tw:max-md:block tw:px-4 tw:py-8 tw:text-center tw:text-gray-500 tw:dark:text-dark-white-400">{{ __('No ports match') }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </x-slot>

                <x-slot name="footer" class="tw:flex tw:flex-wrap tw:items-center tw:justify-between tw:gap-3">
                    <div class="tw:text-gray-500 tw:dark:text-dark-white-400" x-show="total > 0"
                         x-text="lang.showing.replace('__first__', firstItem).replace('__last__', lastItem).replace('__total__', total)"></div>
                    <div class="tw:flex tw:items-center tw:gap-2 tw:ml-auto">
                        <select x-model.number="perPage" class="lnms-input tw:w-auto" aria-label="{{ __('Ports per page') }}">
                            @foreach ([25, 50, 100, 250] as $count)
                                <option value="{{ $count }}">{{ $count }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="lnms-btn lnms-btn-default tw:disabled:opacity-50" x-on:click="goTo(page - 1)" :disabled="page <= 1" aria-label="{{ __('Previous page') }}"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>
                        <span class="tw:whitespace-nowrap" x-text="page + ' / ' + lastPage"></span>
                        <button type="button" class="lnms-btn lnms-btn-default tw:disabled:opacity-50" x-on:click="goTo(page + 1)" :disabled="page >= lastPage" aria-label="{{ __('Next page') }}"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>
                    </div>
                </x-slot>
            </x-panel>

            <x-modal show="speedPort" maxWidth="xl">
                <x-slot name="heading">
                    <h4 class="tw:m-0 tw:text-base tw:font-semibold">{{ __('Speed') }} · <span x-text="speedPort?.label"></span></h4>
                </x-slot>
                <template x-if="speedPort">
                    <div class="tw:flex tw:flex-col tw:gap-5">
                        <div role="radiogroup" aria-labelledby="speed-interface-heading">
                            <div id="speed-interface-heading" class="tw:font-semibold">{{ __('Interface speed') }}</div>
                            <p class="tw:m-0 tw:mb-2 tw:text-gray-500 tw:dark:text-dark-white-400">{{ __('The line rate of the port, used for utilization alerts and RRD tuning.') }}</p>
                            <label class="tw:flex tw:items-center tw:gap-3 tw:mb-2 tw:px-3 tw:py-2 tw:rounded-lg tw:border tw:border-gray-200 tw:dark:border-dark-gray-200 tw:font-normal tw:has-checked:border-blue-500 tw:has-checked:bg-blue-50/50 tw:dark:has-checked:bg-blue-950/30">
                                <input type="radio" value="device" x-model="speedForm.interface" class="tw:m-0">
                                <span class="tw:grow">{{ __('Reported by the device') }}</span>
                                <span class="tw:font-semibold" x-text="speedText(speedPort.ifSpeed_device) || lang.after_poll"></span>
                            </label>
                            <label class="tw:flex tw:items-center tw:gap-3 tw:px-3 tw:py-2 tw:rounded-lg tw:border tw:border-gray-200 tw:dark:border-dark-gray-200 tw:font-normal tw:has-checked:border-blue-500 tw:has-checked:bg-blue-50/50 tw:dark:has-checked:bg-blue-950/30">
                                <input type="radio" value="custom" x-model="speedForm.interface" class="tw:m-0">
                                <span class="tw:grow">{{ __('Custom') }}</span>
                                <input type="text"
                                       class="lnms-input tw:w-36"
                                       :class="speedForm.interface === 'custom' && isNaN(parseSpeed(speedForm.ifSpeed)) && 'tw:border-red-500 tw:ring-1 tw:ring-red-500'"
                                       x-model="speedForm.ifSpeed"
                                       x-on:focus="speedForm.interface = 'custom'"
                                       placeholder="{{ __('e.g. 10G') }}"
                                       aria-label="{{ __('Custom interface speed') }}">
                            </label>
                        </div>

                        <div role="radiogroup" aria-labelledby="speed-circuit-heading">
                            <div id="speed-circuit-heading" class="tw:font-semibold">{{ __('Circuit speed') }}</div>
                            <p class="tw:m-0 tw:mb-2 tw:text-gray-500 tw:dark:text-dark-white-400">{{ __('Optional. Set it when the service is slower than the port, for example a 100 Mbps circuit on a 1 Gbps port. Graphs and custom maps then measure utilization against it, and each direction can be different.') }}</p>
                            <label class="tw:flex tw:items-center tw:gap-3 tw:mb-2 tw:px-3 tw:py-2 tw:rounded-lg tw:border tw:border-gray-200 tw:dark:border-dark-gray-200 tw:font-normal tw:has-checked:border-blue-500 tw:has-checked:bg-blue-50/50 tw:dark:has-checked:bg-blue-950/30">
                                <input type="radio" value="description" x-model="speedForm.circuit" class="tw:m-0">
                                <span class="tw:grow">{{ __('From the port description') }}</span>
                                <span class="tw:font-semibold" x-text="describedCircuitText()"></span>
                            </label>
                            <label class="tw:flex tw:flex-wrap tw:items-center tw:gap-3 tw:px-3 tw:py-2 tw:rounded-lg tw:border tw:border-gray-200 tw:dark:border-dark-gray-200 tw:font-normal tw:has-checked:border-blue-500 tw:has-checked:bg-blue-50/50 tw:dark:has-checked:bg-blue-950/30">
                                <input type="radio" value="custom" x-model="speedForm.circuit" class="tw:m-0">
                                <span class="tw:grow">{{ __('Custom') }}</span>
                                <span class="tw:flex tw:items-center tw:gap-2">
                                    <span class="tw:w-8 tw:text-right">{{ __('Out') }}</span>
                                    <input type="text"
                                           class="lnms-input tw:w-28"
                                           :class="speedForm.circuit === 'custom' && ! (parseSpeed(speedForm.out) > 0) && 'tw:border-red-500 tw:ring-1 tw:ring-red-500'"
                                           x-model="speedForm.out"
                                           x-on:focus="speedForm.circuit = 'custom'"
                                           placeholder="{{ __('e.g. 100M') }}"
                                           aria-label="{{ __('Circuit speed out') }}">
                                    <span class="tw:w-6 tw:text-right">{{ __('In') }}</span>
                                    <input type="text"
                                           class="lnms-input tw:w-28"
                                           :class="speedForm.circuit === 'custom' && isNaN(parseSpeed(speedForm.in)) && 'tw:border-red-500 tw:ring-1 tw:ring-red-500'"
                                           x-model="speedForm.in"
                                           x-on:focus="speedForm.circuit = 'custom'"
                                           placeholder="{{ __('Same as out') }}"
                                           aria-label="{{ __('Circuit speed in') }}">
                                </span>
                            </label>
                        </div>

                        <div class="tw:px-3 tw:py-2 tw:rounded-lg tw:bg-gray-100 tw:dark:bg-dark-gray-400">
                            {{ __('Graphs and maps measure utilization against') }}
                            <span class="tw:font-semibold" x-text="speedResultText()"></span>
                        </div>
                    </div>
                </template>
                <x-slot name="footer">
                    <button type="button" class="lnms-btn lnms-btn-default tw:px-3 tw:py-1.5" x-on:click="speedPort = null">{{ __('Cancel') }}</button>
                    <button type="button" class="lnms-btn lnms-btn-primary tw:px-3 tw:py-1.5 tw:disabled:opacity-50" x-on:click="saveSpeed()" :disabled="! speedFormValid() || isPending('speed')">{{ __('Save') }}</button>
                </x-slot>
            </x-modal>

            <x-modal show="confirmBulk" maxWidth="md">
                <x-slot name="heading">
                    <h4 class="tw:m-0 tw:text-base tw:font-semibold" x-text="(config.bulkTitles[confirmBulk] ?? '') + '?'"></h4>
                </x-slot>
                <p class="tw:m-0" x-text="lang.bulk_confirm.replace('__count__', total)"></p>
                <x-slot name="footer">
                    <button type="button" class="lnms-btn lnms-btn-default tw:px-3 tw:py-1.5" x-on:click="confirmBulk = null">{{ __('Cancel') }}</button>
                    <button type="button" class="lnms-btn lnms-btn-primary tw:px-3 tw:py-1.5" x-on:click="applyBulk(confirmBulk)" :disabled="isPending('bulk')">{{ __('Apply') }}</button>
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
                config: config,
                lang: config.lang,
                selectedPorts: config.selectedPorts,
                summary: config.summary,
                filter: config.filter,
                ports: [],
                sort: 'ifIndex',
                order: 'asc',
                page: 1,
                lastPage: 1,
                perPage: 50,
                total: 0,
                loading: false,
                loadId: 0,
                selected: {},
                allMatching: false,
                bulkGroupId: null,
                confirmBulk: null,
                confirmReset: false,
                speedPort: null,
                speedForm: {},
                pending: {},

                init() {
                    this.$watch('perPage', () => this.goTo(1));
                    this.load();
                },

                get firstItem() {
                    return (this.page - 1) * this.perPage + 1;
                },

                get lastItem() {
                    return Math.min(this.page * this.perPage, this.total);
                },

                get selectionCount() {
                    return this.allMatching ? this.total : Object.keys(this.selected).length;
                },

                get pageSelected() {
                    return this.ports.length > 0 && this.ports.every((port) => this.isSelected(port));
                },

                get pageHasSelection() {
                    return this.ports.some((port) => this.isSelected(port));
                },

                selectedPollingEnabled() {
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

                load() {
                    const url = LibreNMS.Url.applyNestedParamsToUrl(new URL(config.listUrl), 'filter', this.filter);
                    url.searchParams.set('sort', this.sort);
                    url.searchParams.set('order', this.order);
                    url.searchParams.set('page', this.page);
                    url.searchParams.set('per_page', this.perPage);

                    const loadId = ++this.loadId;
                    this.loading = true;
                    this.request('load', 'GET', url)
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

                applyFilter(filter) {
                    if (JSON.stringify(filter) === JSON.stringify(this.filter)) return;
                    this.filter = filter;
                    this.clearSelection();
                    this.goTo(1);
                },

                goTo(page) {
                    this.page = Math.max(1, Math.min(page, this.lastPage));
                    this.load();
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

                // --- Selection ---
                isSelected(port) {
                    return this.allMatching || this.selected[port.port_id] === true;
                },

                toggleSelected(port, checked) {
                    if (this.allMatching) {
                        // drop back to selecting the ports that are visible
                        this.allMatching = false;
                        this.ports.forEach((p) => this.selected[p.port_id] = true);
                    }

                    if (checked) {
                        this.selected[port.port_id] = true;
                    } else {
                        delete this.selected[port.port_id];
                    }
                },

                selectPage(checked) {
                    if (this.allMatching) {
                        this.clearSelection();
                        return;
                    }

                    this.ports.forEach((port) => checked ? this.selected[port.port_id] = true : delete this.selected[port.port_id]);
                },

                clearSelection() {
                    this.selected = {};
                    this.allMatching = false;
                },

                bulk(action) {
                    if (this.allMatching) {
                        this.confirmBulk = action;
                        return;
                    }

                    this.applyBulk(action);
                },

                applyBulk(action) {
                    if (this.isPending('bulk')) return;

                    const data = {action: action};
                    if (action === 'add_group' || action === 'remove_group') {
                        data.group_id = this.bulkGroupId;
                    }
                    if (this.allMatching) {
                        data.all = true;
                        data.filter = this.filter;
                    } else {
                        data.ports = Object.keys(this.selected).map(Number);
                    }

                    this.request('bulk', 'POST', config.bulkUrl, data)
                        .then((response) => {
                            this.confirmBulk = null;
                            toastr.success(response.message);
                            this.load();
                        })
                        .catch(() => {});
                },

                // --- Saving ---
                save(port, data, input) {
                    const field = Object.keys(data)[0];
                    const key = 'port' + port.port_id + field;
                    const revert = () => {
                        if (input.type === 'checkbox') {
                            input.checked = ! input.checked;
                        } else {
                            input.value = port[field] ?? '';
                        }
                    };
                    if (this.isPending(key)) {
                        revert();
                        return Promise.resolve();
                    }

                    return this.request(key, 'PATCH', config.updateUrl.replace('__port__', port.port_id), data)
                        .then((response) => {
                            Object.assign(port, response.port);
                            this.summary = response.summary;
                            if (input.type !== 'checkbox') {
                                input.value = port[field] ?? '';
                                this.flashSaved(input);
                            }
                        })
                        .catch(revert);
                },

                // inline confirmation, toasts for every field get noisy
                flashSaved(input) {
                    const classes = ['tw:ring-2', 'tw:ring-green-500'];
                    input.classList.add(...classes);
                    setTimeout(() => input.classList.remove(...classes), 1500);
                },

                parseSpeed(value) {
                    value = String(value ?? '').trim();
                    if (value === '') return null;

                    // nobody means milli bits, so accept lowercase prefixes: 100m or 10g
                    const speed = LibreNMS.Number.toBytes(value.replace(/^([\d.]+\s?)([a-z])/i, (all, number, prefix) => number + prefix.toUpperCase()));
                    return Number.isFinite(speed) ? Math.round(speed) : NaN;
                },

                // lossless, so the displayed text can be saved back unchanged. short is the port description style: 100M
                speedText(bps, short = false) {
                    if (bps === null || bps === undefined) return '';
                    for (const [prefix, size] of [['T', 1e12], ['G', 1e9], ['M', 1e6], ['k', 1e3]]) {
                        if (bps >= size && bps % (size / 1000) === 0) {
                            return Number((bps / size).toFixed(3)) + (short ? prefix : ' ' + prefix + 'bps');
                        }
                    }

                    return bps + (short ? '' : ' bps');
                },

                // speeds are [out, in]
                circuitText(speeds, short = false) {
                    if (! speeds) return '';
                    if (speeds[0] === speeds[1]) return this.speedText(speeds[0], short);
                    if (short) return this.speedText(speeds[0], true) + '/' + this.speedText(speeds[1], true);

                    return this.speedText(speeds[0]) + ' ' + this.lang.out + ' · ' + this.speedText(speeds[1]) + ' ' + this.lang.in;
                },

                // --- Speed dialog ---
                editSpeed(port) {
                    const circuit = port.circuit_speed_override ? port.circuit_speed : null;
                    this.speedForm = {
                        interface: port.ifSpeed_override ? 'custom' : 'device',
                        ifSpeed: port.ifSpeed_override ? this.speedText(port.ifSpeed) : '',
                        circuit: circuit ? 'custom' : 'description',
                        out: circuit ? this.speedText(circuit[0]) : '',
                        in: circuit && circuit[1] !== circuit[0] ? this.speedText(circuit[1]) : '',
                    };
                    this.speedPort = port;
                },

                // the custom circuit speed as [out, in], in defaults to out
                formCircuit() {
                    const out = this.parseSpeed(this.speedForm.out);
                    const inbound = this.parseSpeed(this.speedForm.in) ?? out;

                    return [out, inbound];
                },

                speedFormValid() {
                    if (this.speedForm.interface === 'custom' && ! (this.parseSpeed(this.speedForm.ifSpeed) > 0)) return false;
                    if (this.speedForm.circuit === 'custom' && ! this.formCircuit().every((speed) => speed > 0)) return false;

                    return true;
                },

                describedCircuitText() {
                    // an override replaced the described speed, the next poll reads the description again
                    if (this.speedPort.circuit_speed_override) return this.lang.after_poll;

                    return this.circuitText(this.speedPort.circuit_speed) || this.lang.none;
                },

                speedResultText() {
                    if (! this.speedFormValid()) return '…';

                    if (this.speedForm.circuit === 'custom') return this.circuitText(this.formCircuit());
                    if (this.speedPort.circuit_speed && ! this.speedPort.circuit_speed_override) return this.circuitText(this.speedPort.circuit_speed);

                    const speed = this.speedForm.interface === 'custom' ? this.parseSpeed(this.speedForm.ifSpeed) : this.speedPort.ifSpeed_device;
                    return this.speedText(speed) || this.lang.after_poll;
                },

                saveSpeed() {
                    if (! this.speedFormValid()) return;

                    const port = this.speedPort;
                    const data = {};

                    if (this.speedForm.interface === 'custom') {
                        data.ifSpeed = this.parseSpeed(this.speedForm.ifSpeed);
                    } else if (port.ifSpeed_override) {
                        data.ifSpeed = null;
                    }

                    if (this.speedForm.circuit === 'custom') {
                        const [out, inbound] = this.formCircuit();
                        data.port_descr_speed = {out: out, in: inbound};
                    } else if (port.circuit_speed_override) {
                        data.port_descr_speed = null;
                    }

                    if (Object.keys(data).length === 0) {
                        this.speedPort = null;
                        return;
                    }

                    this.request('speed', 'PATCH', config.updateUrl.replace('__port__', port.port_id), data)
                        .then((response) => {
                            Object.assign(port, response.port);
                            this.summary = response.summary;
                            this.speedPort = null;
                        })
                        .catch(() => {});
                },

                // --- Port groups ---
                groupSelect2Config(extra = {}) {
                    if (! config.canCreateGroup) return extra;

                    return {
                        ...extra,
                        tags: true,
                        createTag: (params) => {
                            const term = params.term.trim();
                            return term === '' ? null : {id: '__new__' + term, text: term, newGroup: true};
                        },
                        templateResult: (item) => item.newGroup ? this.lang.create_group + ': ' + item.text : item.text,
                    };
                },

                // create a group typed into a select2 tag field, then swap in the real group id
                createGroup(el, item) {
                    return this.request('create-group', 'POST', config.portGroupStoreUrl, {name: item.text})
                        .then((group) => {
                            $(el).find('option').filter((i, option) => option.value === item.id).remove();
                            el.add(new Option(group.text, group.id, true, true));

                            return group;
                        })
                        .catch((error) => {
                            $(el).find('option').filter((i, option) => option.value === item.id).remove();
                            $(el).trigger('change');
                            throw error;
                        });
                },

                initGroups(el, port) {
                    port.groups.forEach((group) => el.add(new Option(group.text, group.id, true, true)));
                    init_select2(el, 'port-group', {}, null, this.lang.no_group, this.groupSelect2Config({width: '100%', allowClear: false}));
                    // rows are replaced on every page load, free select2 with them
                    Alpine.onElRemoved(el, () => $(el).off().select2('destroy'));

                    // select2 events are unreliable for created tags, so react to the resulting value instead
                    let saved = JSON.stringify(port.groups.map((group) => String(group.id)));
                    $(el).on('change', () => {
                        const groups = $(el).select2('data');
                        const newGroup = groups.find((group) => group.newGroup);
                        if (newGroup) {
                            if (! this.isPending('create-group')) {
                                this.createGroup(el, newGroup).then(() => $(el).trigger('change')).catch(() => {});
                            }
                            return;
                        }

                        const ids = JSON.stringify(groups.map((group) => String(group.id)));
                        if (ids === saved) return;
                        saved = ids;

                        this.saveGroups(port, groups).catch(() => {});
                    });

                    this.$nextTick(() => $(el).select2('open'));
                },

                saveGroups(port, groups) {
                    return this.request('groups' + port.port_id, 'PUT', config.portUrl + '/' + port.port_id, {groups: groups.map((group) => group.id)})
                        .then(() => port.groups = groups.map((group) => ({id: Number(group.id), text: group.text})));
                },

                removeGroup(port, group) {
                    this.saveGroups(port, port.groups.filter((g) => g.id !== group.id)).catch(() => {});
                },

                initBulkGroup(el) {
                    init_select2(el, 'port-group', {}, null, this.lang.select_group, this.groupSelect2Config({width: '16em'}));
                    $(el).on('change', () => {
                        const group = $(el).select2('data')[0];
                        this.bulkGroupId = group && ! group.newGroup ? group.id : null;

                        if (group?.newGroup && ! this.isPending('create-group')) {
                            this.createGroup(el, group).then(() => $(el).trigger('change')).catch(() => {});
                        }
                    });
                },

                // --- Settings ---
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
                            this.load();
                        });
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

                // --- Display ---
                statusText(port) {
                    if (port.ifAdminStatus === 'down') return 'admin down';
                    return port.ifOperStatus ?? 'unknown';
                },

                statusClass(port) {
                    if (port.ifAdminStatus === 'down') return 'tw:bg-gray-500';
                    return port.ifOperStatus === 'up' ? 'tw:bg-green-600' : 'tw:bg-red-600';
                },

                pollingIcon(port) {
                    if (port.polling === 'polled') return 'fa-circle-check';
                    return port.polling === 'down' || port.polling === 'admin_down' ? 'fa-circle-pause' : 'fa-circle-minus';
                },

                pollingClass(port) {
                    if (port.polling === 'polled') return 'tw:text-green-700 tw:dark:text-green-400';
                    if (port.polling === 'down' || port.polling === 'admin_down') return 'tw:text-amber-600 tw:dark:text-amber-400';
                    return 'tw:text-gray-500 tw:dark:text-dark-white-400';
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
