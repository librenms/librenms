@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
        <x-device.edit-tabs :device="$device" tab="ports" />

        <x-selectable-table component="devicePorts"
                            filter-name="device.edit-ports"
                            :filter-fields="$filter_fields"
                            row-label="row.label"
                            :page-selected="__('port.settings.page_selected', ['count' => '__count__'])"
                            :select-all-matching="__('port.settings.select_all_matching', ['count' => '__count__'])"
                            :all-matching-selected="__('port.settings.all_matching_selected', ['count' => '__count__'])"
                            :config="[
                'settings' => $settings,
                'summary' => $summary,
                'filter' => $filter,
                'canCreateGroup' => $can_create_group,
                'rowKey' => 'port_id',
                'sort' => 'ifIndex',
                'listUrl' => route('device.edit.ports.list', $device),
                'updateUrl' => route('device.edit.ports.update', [$device, '__port__']),
                'settingsUrl' => route('device.edit.ports.settings', $device),
                'bulkUrl' => route('device.edit.ports.bulk', $device),
                'resetStateUrl' => route('device.edit.ports.reset-state', $device),
                'portUrl' => url('port'),
                'portGroupStoreUrl' => route('port-groups.store'),
                'lang' => [
                    'no_group' => __('No Group'),
                    'select_group' => __('port.port_group'),
                    'create_group' => __('port.settings.create_group'),
                    'unknown' => __('Unknown'),
                    'none' => __('None'),
                    'after_poll' => __('port.settings.speed.after_poll'),
                    'custom_speed' => __('port.settings.speed.custom'),
                    'circuit' => __('port.settings.speed.circuit_short'),
                    'out' => __('out'),
                    'in' => __('in'),
                    'bulk_confirm' => __('port.settings.bulk.confirm', ['count' => '__count__']),
                ],
                'bulkTitles' => [
                    'disable' => __('port.settings.bulk.disable'),
                    'enable' => __('port.settings.bulk.enable'),
                    'ignore' => __('port.settings.bulk.ignore'),
                    'unignore' => __('port.settings.bulk.unignore'),
                    'add_group' => __('port.settings.bulk.add_group'),
                    'remove_group' => __('port.settings.bulk.remove_group'),
                ],
                'pollingLabels' => [
                    'polled' => __('port.settings.polling.polled'),
                    'down' => __('port.settings.polling.down'),
                    'admin_down' => __('port.settings.polling.admin_down'),
                    'disabled' => __('port.settings.polling.disabled'),
                    'deleted' => __('Deleted'),
                ],
                'pollingTitles' => [
                    'polled' => __('port.settings.polling_help.polled'),
                    'down' => __('port.settings.polling_help.down'),
                    'admin_down' => __('port.settings.polling_help.admin_down'),
                    'disabled' => __('port.settings.polling_help.disabled'),
                    'deleted' => __('port.settings.polling_help.deleted'),
                ],
        ]">
            <x-slot:before>
                @unless ($ports_module->isEnabled())
                    <div class="tw:flex tw:flex-wrap tw:items-center tw:gap-2 tw:mb-4 tw:px-4 tw:py-3 tw:rounded-lg tw:border tw:border-amber-300 tw:bg-amber-50 tw:text-amber-800 tw:dark:border-amber-700 tw:dark:bg-amber-950/40 tw:dark:text-amber-200">
                        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                        {{ __('port.settings.module_disabled') }}
                        <a href="{{ route('device.edit.modules', $device) }}" class="tw:underline">{{ __('Edit modules') }}</a>
                    </div>
                @endunless

                <x-panel class="tw:mb-4">
                    <div class="tw:flex tw:flex-wrap tw:items-start tw:justify-between tw:gap-6">
                        <div class="tw:flex tw:flex-col tw:gap-4 tw:max-w-3xl">
                            @foreach (['selected_ports' => 'selected_polling', 'rrd_tune' => 'rrd_tune'] as $setting => $langKey)
                                <div class="tw:flex tw:items-start tw:gap-4">
                                    <x-toggle ::checked="settingEnabled('{{ $setting }}')"
                                              aria-label="{{ __('port.settings.' . $langKey) }}"
                                              x-on:change="toggleSetting('{{ $setting }}', $event.target)" />
                                    <div>
                                        <x-help-heading :id="$setting" :label="__('port.settings.' . $langKey)">{{ __('port.settings.' . $langKey . '_help') }}</x-help-heading>
                                        <x-setting-source setting="settings.{{ $setting }}" reset="clearSetting('{{ $setting }}')" class="tw:mt-1" />
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="tw:flex tw:flex-wrap tw:gap-2" role="group" aria-label="{{ __('port.settings.summary') }}">
                            @foreach ([
                                'polled' => [__('port.settings.polling.polled'), 'tw:text-green-700 tw:dark:text-green-400', ['polling' => ['eq' => 'polled']]],
                                'skipped' => [__('port.settings.polling.skipped'), 'tw:text-amber-600 tw:dark:text-amber-400', ['polling' => ['eq' => 'skipped']]],
                                'disabled' => [__('port.settings.polling.disabled'), 'tw:text-gray-700 tw:dark:text-dark-white-200', ['disabled' => ['eq' => 1], 'deleted' => ['eq' => 0]]],
                                'deleted' => [__('Deleted'), 'tw:text-gray-700 tw:dark:text-dark-white-200', ['deleted' => ['eq' => 1]]],
                                'ignored' => [__('Ignored'), 'tw:text-gray-700 tw:dark:text-dark-white-200', ['ignore' => ['eq' => 1], 'deleted' => ['eq' => 0]]],
                            ] as $key => [$label, $color, $chipFilter])
                                <a href="{{ route('device.edit.ports', ['device' => $device, 'filter' => $chipFilter]) }}"
                                   @if ($key === 'skipped') x-show="settingEnabled('selected_ports')" @endif
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
            </x-slot:before>

            <x-slot:heading>
                <button type="button"
                        class="lnms-btn lnms-btn-default"
                        x-on:click="confirmReset = true"
                        title="{{ __('port.settings.reset_state.title') }}">
                    <i class="fa fa-recycle" aria-hidden="true"></i> {{ __('port.settings.reset_state.button') }}
                </button>
            </x-slot:heading>

            <x-slot:actions>
                <button type="button" class="lnms-btn lnms-btn-default" x-on:click="bulk('disable')">{{ __('port.settings.bulk.disable') }}</button>
                <button type="button" class="lnms-btn lnms-btn-default" x-on:click="bulk('enable')">{{ __('port.settings.bulk.enable') }}</button>
                <button type="button" class="lnms-btn lnms-btn-default" x-on:click="bulk('ignore')">{{ __('port.settings.bulk.ignore') }}</button>
                <button type="button" class="lnms-btn lnms-btn-default" x-on:click="bulk('unignore')">{{ __('port.settings.bulk.unignore') }}</button>
                <div class="tw:flex tw:flex-wrap tw:items-center tw:gap-2">
                    <select x-init="initBulkGroup($el)" aria-label="{{ __('port.port_group') }}"></select>
                    <button type="button" class="lnms-btn lnms-btn-default tw:disabled:opacity-50" x-on:click="bulk('add_group')" :disabled="! bulkGroupId">{{ __('port.settings.bulk.add_group') }}</button>
                    <button type="button" class="lnms-btn lnms-btn-default tw:disabled:opacity-50" x-on:click="bulk('remove_group')" :disabled="! bulkGroupId">{{ __('port.settings.bulk.remove_group') }}</button>
                </div>
            </x-slot:actions>

            <x-slot:columns>
                @foreach (['ifName' => __('Port'), 'ifOperStatus' => __('Status')] as $sortKey => $label)
                    <th @class(['tw:max-md:hidden' => $sortKey !== 'ifName'])>
                        <x-selectable-table.sort-header :key="$sortKey" :label="$label" />
                    </th>
                @endforeach
                <th class="tw:max-md:hidden">{{ __('Polling') }}</th>
                <th class="tw:max-md:hidden" title="{{ __('port.settings.disable_help') }}">{{ __('Disable') }}</th>
                <th class="tw:max-md:hidden" title="{{ __('port.settings.ignore_help') }}">{{ __('Ignore') }}</th>
                @foreach (['ifSpeed' => __('Speed'), 'ifAlias' => __('Description')] as $sortKey => $label)
                    <th class="tw:max-md:hidden">
                        <x-selectable-table.sort-header :key="$sortKey" :label="$label" />
                    </th>
                @endforeach
                <th class="tw:max-md:hidden">{{ __('Port Groups') }}</th>
                <th class="tw:max-md:hidden" title="{{ __('port.settings.rrd_tune_help') }}">{{ __('RRD Tune') }}</th>
            </x-slot:columns>

            <x-slot:row>
                <td class="tw:max-md:col-span-2">
                    <a :href="row.url"
                       class="tw:font-semibold"
                       :class="row.link_class + (row.deleted ? ' tw:line-through' : '')"
                       x-data="portLink({port_id: row.port_id})"
                       x-text="row.label"></a>
                    <div class="tw:text-gray-500 tw:dark:text-dark-white-400 tw:whitespace-nowrap">
                        {{ __('Index') }} <span x-text="row.ifIndex"></span><span x-show="row.ifDescr && row.ifDescr !== row.label" x-text="' · ' + row.ifDescr"></span>
                    </div>
                </td>
                <td class="tw:whitespace-nowrap" data-label="{{ __('Status') }}">
                    <x-port.status-badge port="row" />
                </td>
                <td class="tw:whitespace-nowrap" data-label="{{ __('Polling') }}">
                    <span class="tw:inline-flex tw:items-center tw:gap-1.5 tw:font-medium" :class="pollingClass(row)" :title="config.pollingTitles[row.polling]">
                        <i class="fa" :class="pollingIcon(row)" aria-hidden="true"></i>
                        <span x-text="config.pollingLabels[row.polling]"></span>
                    </span>
                </td>
                <td data-label="{{ __('port.settings.bulk.disable') }}">
                    <x-toggle ::checked="row.disabled" ::aria-label="'{{ __('port.settings.bulk.disable') }} ' + row.label" x-on:change="save(row, {disabled: $event.target.checked}, $event.target)" />
                </td>
                <td data-label="{{ __('Ignore') }}">
                    <x-toggle ::checked="row.ignore" ::aria-label="'{{ __('Ignore') }} ' + row.label" x-on:change="save(row, {ignore: $event.target.checked}, $event.target)" />
                </td>
                <td class="tw:max-md:col-span-2" data-label="{{ __('Speed') }}">
                    <button type="button"
                            class="tw:group tw:inline-flex tw:items-center tw:gap-2 tw:p-0 tw:border-0 tw:bg-transparent tw:text-left tw:whitespace-nowrap"
                            x-on:click="editSpeed(row)"
                            :aria-label="'{{ __('port.settings.speed.edit') }} ' + row.label">
                        <span :class="row.ifSpeed_override && 'tw:text-amber-600 tw:dark:text-amber-400'"
                              :title="row.ifSpeed_override ? lang.custom_speed : ''"
                              x-text="speedText(row.ifSpeed) || lang.unknown"></span>
                        <span x-show="row.circuit_speed"
                              class="tw:px-1.5 tw:rounded-[3px] tw:border"
                              :class="row.circuit_speed_override ? 'tw:border-amber-500 tw:text-amber-600 tw:dark:text-amber-400' : 'tw:border-gray-300 tw:text-gray-500 tw:dark:border-dark-gray-100 tw:dark:text-dark-white-400'"
                              :title="circuitText(row.circuit_speed)"
                              x-text="lang.circuit + ' ' + circuitText(row.circuit_speed, true)"></span>
                        <i class="fa fa-pen tw:text-gray-400 tw:group-hover:text-blue-600 tw:dark:group-hover:text-blue-400" aria-hidden="true"></i>
                    </button>
                </td>
                <td class="tw:max-md:col-span-2" data-label="{{ __('Description') }}">
                    <div class="tw:relative tw:min-w-48">
                        <input type="text"
                               class="lnms-input"
                               :class="row.ifAlias_override && 'tw:pr-8'"
                               :value="row.ifAlias"
                               :aria-label="'{{ __('Description') }} ' + row.label"
                               x-on:keydown.enter="$event.target.blur()"
                               x-on:keydown.escape="$event.target.value = row.ifAlias; $event.target.blur()"
                               x-on:change="save(row, {ifAlias: $event.target.value}, $event.target)">
                        <button type="button"
                                x-show="row.ifAlias_override"
                                class="tw:absolute tw:right-1 tw:top-1 tw:h-[22px] tw:w-6 tw:p-0 tw:border-0 tw:rounded-[3px] tw:bg-transparent tw:text-amber-600 tw:hover:bg-amber-50"
                                x-on:click="save(row, {ifAlias: ''}, $el.previousElementSibling)"
                                title="{{ __('port.settings.description.custom') }}"
                                aria-label="{{ __('port.settings.description.use_device') }}">
                            <i class="fa fa-rotate-left" aria-hidden="true"></i>
                        </button>
                    </div>
                </td>
                <td class="tw:max-md:col-span-2 tw:min-w-40" data-label="{{ __('Port Groups') }}">
                    {{-- select2 is created on demand, creating one per row is slow on large pages, this uses the select2 markup so both look the same --}}
                    <template x-if="! row.editingGroups">
                        <span class="select2 select2-container select2-container--bootstrap tw:cursor-text"
                              style="width: 100%"
                              role="button"
                              tabindex="0"
                              x-on:click="row.editingGroups = true"
                              x-on:keydown.enter.prevent="row.editingGroups = true"
                              :aria-label="'{{ __('port.settings.edit_groups') }} ' + row.label">
                            <span class="selection">
                                <span class="select2-selection select2-selection--multiple input-sm">
                                    <ul class="select2-selection__rendered">
                                        <template x-for="group in row.groups" :key="group.id">
                                            <li class="select2-selection__choice" :title="group.text">
                                                <span class="select2-selection__choice__remove" role="button" x-on:click.stop="removeGroup(row, group)" :aria-label="'{{ __('Remove') }} ' + group.text">×</span><span x-text="group.text"></span>
                                            </li>
                                        </template>
                                        <li class="select2-search select2-search--inline" x-show="row.groups.length === 0">
                                            <input class="select2-search__field" :placeholder="lang.no_group" readonly tabindex="-1" style="width: 100%">
                                        </li>
                                    </ul>
                                </span>
                            </span>
                        </span>
                    </template>
                    <template x-if="row.editingGroups">
                        <div>
                            <select class="input-sm" multiple x-init="initGroups($el, row)" :aria-label="'{{ __('Port Groups') }} ' + row.label"></select>
                        </div>
                    </template>
                </td>
                <td data-label="{{ __('RRD Tune') }}">
                    <span class="tw:inline-flex tw:items-center tw:gap-2">
                        <x-toggle ::checked="row.rrd_tune" ::aria-label="'{{ __('RRD Tune') }} ' + row.label" x-on:change="save(row, {rrd_tune: $event.target.checked}, $event.target)" />
                        <i x-show="row.rrd_tune_override" class="fa fa-circle tw:text-[0.5rem] tw:text-amber-600 tw:dark:text-amber-400" title="{{ __('port.settings.rrd_tune_port_override') }}" aria-hidden="true"></i>
                    </span>
                </td>
            </x-slot:row>

            <x-slot:empty>{{ __('port.settings.no_ports') }}</x-slot:empty>

            <x-modal show="speedPort" maxWidth="xl">
                <x-slot name="heading">
                    <h4 class="tw:m-0 tw:text-base tw:font-semibold">{{ __('Speed') }} · <span x-text="speedPort?.label"></span></h4>
                </x-slot>
                <template x-if="speedPort">
                    <div class="tw:flex tw:flex-col tw:gap-5">
                        <div role="radiogroup" aria-labelledby="speed-interface-heading">
                            <x-help-heading id="speed-interface" :label="__('port.settings.speed.interface')" class="tw:mb-2">{{ __('port.settings.speed.interface_help') }}</x-help-heading>
                            <label class="tw:flex tw:items-center tw:gap-3 tw:mb-2 tw:px-3 tw:py-2 tw:rounded-lg tw:border tw:border-gray-200 tw:dark:border-dark-gray-200 tw:font-normal tw:has-checked:border-blue-500 tw:has-checked:bg-blue-50/50 tw:dark:has-checked:bg-blue-950/30">
                                <input type="radio" value="device" x-model="speedForm.interface" class="tw:m-0">
                                <span class="tw:grow">{{ __('port.settings.speed.reported') }}</span>
                                <span class="tw:font-semibold" x-text="deviceSpeedText(speedPort)"></span>
                            </label>
                            <label class="tw:flex tw:items-center tw:gap-3 tw:px-3 tw:py-2 tw:rounded-lg tw:border tw:border-gray-200 tw:dark:border-dark-gray-200 tw:font-normal tw:has-checked:border-blue-500 tw:has-checked:bg-blue-50/50 tw:dark:has-checked:bg-blue-950/30">
                                <input type="radio" value="custom" x-model="speedForm.interface" class="tw:m-0">
                                <span class="tw:grow">{{ __('Custom') }}</span>
                                <input type="text"
                                       class="lnms-input tw:w-36"
                                       :class="speedForm.interface === 'custom' && isNaN(parseSpeed(speedForm.ifSpeed)) && 'tw:border-red-500 tw:ring-1 tw:ring-red-500'"
                                       x-model="speedForm.ifSpeed"
                                       x-on:focus="speedForm.interface = 'custom'"
                                       placeholder="{{ __('port.settings.speed.interface_example') }}"
                                       aria-label="{{ __('port.settings.speed.custom_interface') }}">
                            </label>
                            <div x-show="speedPort.ifSpeed_override" class="tw:flex tw:flex-wrap tw:items-center tw:gap-2 tw:mt-2">
                                <button type="button"
                                        x-show="speedForm.interface === 'custom'"
                                        x-on:click="speedForm.interface = 'device'"
                                        class="tw:p-0 tw:border-0 tw:bg-transparent tw:text-red-600 tw:hover:underline tw:dark:text-red-400">
                                    <i class="fa fa-xmark" aria-hidden="true"></i> {{ __('port.settings.speed.remove_custom') }}
                                </button>
                                <span x-show="speedForm.interface !== 'custom'" class="tw:text-amber-600 tw:dark:text-amber-400">{{ __('port.settings.speed.custom_removed') }}</span>
                                <button type="button"
                                        x-show="speedForm.interface !== 'custom'"
                                        x-on:click="restoreSpeedForm('interface')"
                                        class="tw:p-0 tw:border-0 tw:bg-transparent tw:text-blue-600 tw:hover:underline tw:dark:text-blue-400">{{ __('Undo') }}</button>
                            </div>
                        </div>

                        <div role="radiogroup" aria-labelledby="speed-circuit-heading">
                            <x-help-heading id="speed-circuit" :label="__('port.settings.speed.circuit')" class="tw:mb-2">{{ __('port.settings.speed.circuit_help') }}</x-help-heading>
                            <label class="tw:flex tw:items-center tw:gap-3 tw:mb-2 tw:px-3 tw:py-2 tw:rounded-lg tw:border tw:border-gray-200 tw:dark:border-dark-gray-200 tw:font-normal tw:has-checked:border-blue-500 tw:has-checked:bg-blue-50/50 tw:dark:has-checked:bg-blue-950/30">
                                <input type="radio" value="description" x-model="speedForm.circuit" class="tw:m-0">
                                <span class="tw:grow">{{ __('port.settings.speed.from_description') }}</span>
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
                                           placeholder="{{ __('port.settings.speed.circuit_example') }}"
                                           aria-label="{{ __('port.settings.speed.circuit_out') }}">
                                    <span class="tw:w-6 tw:text-right">{{ __('In') }}</span>
                                    <input type="text"
                                           class="lnms-input tw:w-28"
                                           :class="speedForm.circuit === 'custom' && isNaN(parseSpeed(speedForm.in)) && 'tw:border-red-500 tw:ring-1 tw:ring-red-500'"
                                           x-model="speedForm.in"
                                           x-on:focus="speedForm.circuit = 'custom'"
                                           placeholder="{{ __('port.settings.speed.same_as_out') }}"
                                           aria-label="{{ __('port.settings.speed.circuit_in') }}">
                                </span>
                            </label>
                            <div x-show="speedPort.circuit_speed_override" class="tw:flex tw:flex-wrap tw:items-center tw:gap-2 tw:mt-2">
                                <button type="button"
                                        x-show="speedForm.circuit === 'custom'"
                                        x-on:click="speedForm.circuit = 'description'"
                                        class="tw:p-0 tw:border-0 tw:bg-transparent tw:text-red-600 tw:hover:underline tw:dark:text-red-400">
                                    <i class="fa fa-xmark" aria-hidden="true"></i> {{ __('port.settings.speed.remove_custom_circuit') }}
                                </button>
                                <span x-show="speedForm.circuit !== 'custom'" class="tw:text-amber-600 tw:dark:text-amber-400">{{ __('port.settings.speed.custom_circuit_removed') }}</span>
                                <button type="button"
                                        x-show="speedForm.circuit !== 'custom'"
                                        x-on:click="restoreSpeedForm('circuit')"
                                        class="tw:p-0 tw:border-0 tw:bg-transparent tw:text-blue-600 tw:hover:underline tw:dark:text-blue-400">{{ __('Undo') }}</button>
                            </div>
                        </div>

                        <div class="tw:px-3 tw:py-2 tw:rounded-lg tw:bg-gray-100 tw:dark:bg-dark-gray-400">
                            {{ __('port.settings.speed.result') }}
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
                    <h4 class="tw:m-0 tw:text-base tw:font-semibold">{{ __('port.settings.reset_state.confirm_title') }}</h4>
                </x-slot>
                <p class="tw:m-0">{{ __('port.settings.reset_state.confirm') }}</p>
                <x-slot name="footer">
                    <button type="button" class="lnms-btn lnms-btn-default tw:px-3 tw:py-1.5" x-on:click="confirmReset = false">{{ __('Cancel') }}</button>
                    <button type="button" class="lnms-btn lnms-btn-danger tw:px-3 tw:py-1.5" x-on:click="resetState()" :disabled="isPending('reset')">{{ __('Reset') }}</button>
                </x-slot>
            </x-modal>
        </x-selectable-table>
    </x-device.page>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('devicePorts', (config) => LibreNMS.selectableTable(config, {
                settings: config.settings,
                summary: config.summary,
                bulkGroupId: null,
                confirmBulk: null,
                confirmReset: false,
                speedPort: null,
                speedForm: {},

                loaded(data) {
                    this.summary = data.summary;
                },

                settingEnabled(key) {
                    const setting = this.settings[key];

                    return setting.device ?? setting.os ?? setting.global;
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

                    const data = {action: action, ...this.selectionData()};
                    if (action === 'add_group' || action === 'remove_group') {
                        data.group_id = this.bulkGroupId;
                    }

                    this.request('bulk', 'POST', config.bulkUrl, data)
                        .then((response) => {
                            this.confirmBulk = null;
                            this.clearSelection();
                            this.notify(response.message);
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
                    this.speedForm = this.initialSpeedForm(port);
                    this.speedPort = port;
                },

                initialSpeedForm(port) {
                    const circuit = port.circuit_speed_override ? port.circuit_speed : null;

                    return {
                        interface: port.ifSpeed_override ? 'custom' : 'device',
                        ifSpeed: port.ifSpeed_override ? this.speedText(port.ifSpeed) : '',
                        circuit: circuit ? 'custom' : 'description',
                        out: circuit ? this.speedText(circuit[0]) : '',
                        in: circuit && circuit[1] !== circuit[0] ? this.speedText(circuit[1]) : '',
                    };
                },

                // undo removing a custom speed, restores the saved values of one section
                restoreSpeedForm(section) {
                    const initial = this.initialSpeedForm(this.speedPort);
                    const fields = section === 'interface' ? ['interface', 'ifSpeed'] : ['circuit', 'out', 'in'];
                    fields.forEach((field) => this.speedForm[field] = initial[field]);
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

                    return this.speedForm.interface === 'custom'
                        ? this.speedText(this.parseSpeed(this.speedForm.ifSpeed))
                        : this.deviceSpeedText(this.speedPort);
                },

                deviceSpeedText(port) {
                    // a custom speed replaces the device speed until the next poll, ports that were never up may have no speed
                    return this.speedText(port.ifSpeed_device) || (port.ifSpeed_override ? this.lang.after_poll : this.lang.unknown);
                },

                saveSpeed() {
                    if (! this.speedFormValid()) return;

                    const port = this.speedPort;
                    const data = {};

                    // only send what changed, so unchanged custom speeds are not logged again
                    if (this.speedForm.interface === 'custom') {
                        const speed = this.parseSpeed(this.speedForm.ifSpeed);
                        if (! port.ifSpeed_override || speed !== port.ifSpeed) {
                            data.ifSpeed = speed;
                        }
                    } else if (port.ifSpeed_override) {
                        data.ifSpeed = null;
                    }

                    if (this.speedForm.circuit === 'custom') {
                        const [out, inbound] = this.formCircuit();
                        if (! port.circuit_speed_override || out !== port.circuit_speed[0] || inbound !== port.circuit_speed[1]) {
                            data.port_descr_speed = {out: out, in: inbound};
                        }
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
                clearSetting(key) {
                    this.updateSetting(key, 'clear').catch(() => {});
                },

                toggleSetting(key, input) {
                    this.updateSetting(key, input.checked ? 'true' : 'false')
                        .catch(() => input.checked = ! input.checked);
                },

                updateSetting(key, value) {
                    return this.request('settings', 'PUT', config.settingsUrl, {[key]: value})
                        .then((response) => {
                            this.settings = response.settings;
                            this.summary = response.summary;
                            // polling states and port rrd tune defaults depend on these
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
                pollingIcon(port) {
                    if (port.polling === 'polled') return 'fa-circle-check';
                    return port.polling === 'down' || port.polling === 'admin_down' ? 'fa-circle-pause' : 'fa-circle-minus';
                },

                pollingClass(port) {
                    if (port.polling === 'polled') return 'tw:text-green-700 tw:dark:text-green-400';
                    if (port.polling === 'down' || port.polling === 'admin_down') return 'tw:text-amber-600 tw:dark:text-amber-400';
                    return 'tw:text-gray-500 tw:dark:text-dark-white-400';
                },
            }));
        });
    </script>
@endpush
