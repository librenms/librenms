@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
        @if($data['error'])
            <x-panel class="tw:mt-4" title="{{ __('Device Configuration') }}">
                {{ $data['error_message'] }}
            </x-panel>
        @else
            <div x-data="configBackups(@js($data))" data-config-backups
                @keydown.window="handleKeyDown($event)"
                class="tw:mt-4 tw:flex tw:flex-col tw:lg:flex-row tw:gap-4 tw:items-start">

                {{-- Backup list (Interactive Timeline Sidebar) --}}
                <x-panel class="tw:w-full tw:lg:w-md tw:lg:shrink-0 tw:overflow-hidden tw:self-start tw:lg:sticky tw:lg:top-4 tw:mb-0!">
                    <x-slot name="heading" class="tw:flex tw:flex-wrap tw:items-center tw:justify-between tw:gap-2">
                        <h3 class="panel-title tw:whitespace-nowrap tw:flex tw:items-center tw:gap-2">
                            {{ __('config_backups.backups') }}
                            <span x-show="!loadingBackups" x-cloak class="tw:font-normal tw:text-gray-500 tw:dark:text-dark-white-400" x-text="'(' + total + ')'"></span>
                            <i x-show="loadingBackups || refreshing" x-cloak class="fa fa-spinner tw:animate-spin tw:text-blue-500 tw:text-xs" aria-hidden="true"></i>
                        </h3>
                        <div class="tw:flex tw:flex-wrap tw:items-center tw:gap-2">
                            <button type="button"
                                    x-show="total > 1" x-cloak
                                    x-on:click="toggleDiffMode()"
                                    class="lnms-btn tw:h-8! tw:px-3 tw:flex tw:items-center tw:gap-1.5 tw:whitespace-nowrap tw:text-sm tw:transition-colors"
                                    :class="{ 'lnms-btn-default': diffMode, 'lnms-btn-primary': !diffMode }">
                                <i class="fa" :class="diffMode ? 'fa-times' : 'fa-exchange'" aria-hidden="true"></i>
                                <span x-text="diffMode ? '{{ __('config_backups.exit_diff') }}' : '{{ __('config_backups.compare') }}'"></span>
                            </button>
                            <button type="button"
                                    x-show="canRefresh" x-cloak
                                    x-on:click="refresh()"
                                    :disabled="refreshing"
                                    title="{{ __('config_backups.refresh') }}"
                                    class="lnms-btn lnms-btn-success tw:h-8! tw:w-8! tw:p-0! tw:flex tw:items-center tw:justify-center tw:text-xs tw:transition-colors tw:disabled:opacity-50 tw:shrink-0"
                                    aria-label="{{ __('config_backups.refresh') }}">
                                <i class="fa fa-refresh" :class="refreshing ? 'tw:animate-spin' : ''" aria-hidden="true"></i>
                            </button>
                            <button type="button"
                                    x-on:click="showHelp = true"
                                    title="{{ __('config_backups.help') }}"
                                    class="lnms-btn lnms-btn-default tw:h-8! tw:w-8! tw:p-0! tw:flex tw:items-center tw:justify-center tw:text-xs tw:transition-colors tw:shrink-0"
                                    aria-label="{{ __('config_backups.help') }}">
                                <i class="fa fa-question" aria-hidden="true"></i>
                            </button>
                        </div>
                    </x-slot>

                    <x-slot name="table">
                        <div x-show="loadingBackups" x-cloak class="tw:py-10 tw:text-center tw:text-gray-500 tw:dark:text-dark-white-400">
                            <i class="fa fa-spinner tw:animate-spin fa-2x"></i>
                        </div>

                        <ul x-show="!loadingBackups"
                            x-ref="timelineList"
                            class="tw:list-none tw:m-0 tw:p-0 tw:divide-y tw:divide-gray-100 tw:dark:divide-dark-gray-300 tw:max-h-80 tw:lg:max-h-[70vh] tw:overflow-y-auto tw:select-none">
                            <template x-for="(backup, index) in backups" :key="backup.id">
                                <li :data-backup-index="index"
                                    :data-backup-id="backup.id"
                                    x-on:click="selectBackup(backup, index, $event)"
                                    class="tw:relative tw:flex tw:items-center tw:gap-3.5 tw:px-4 tw:py-3 tw:min-h-13 tw:transition-colors tw:cursor-pointer"
                                    :class="getRowRangeClass(backup, index)">

                                    {{-- Dedicated Timeline Column (Line & Circle share exact same center axis) --}}
                                    <div class="tw:relative tw:w-7 tw:self-stretch tw:shrink-0 tw:flex tw:items-center tw:justify-center">
                                        {{-- Top connector line --}}
                                        <template x-if="index > 0">
                                            <div class="tw:absolute tw:top-0 tw:h-1/2 tw:left-1/2 tw:-translate-x-1/2 tw:z-0 tw:transition-colors"
                                                 :class="isTopConnectorActive(index) ? 'tw:w-[3px] tw:bg-blue-500 tw:dark:bg-blue-400' : 'tw:w-[2px] tw:bg-gray-300 tw:dark:bg-dark-gray-100'"></div>
                                        </template>

                                        {{-- Bottom connector line --}}
                                        <template x-if="index < backups.length - 1">
                                            <div class="tw:absolute tw:top-1/2 tw:h-1/2 tw:left-1/2 tw:-translate-x-1/2 tw:z-0 tw:transition-colors"
                                                 :class="isBottomConnectorActive(index) ? 'tw:w-[3px] tw:bg-blue-500 tw:dark:bg-blue-400' : 'tw:w-[2px] tw:bg-gray-300 tw:dark:bg-dark-gray-100'"></div>
                                        </template>

                                        {{-- Node Circle / Dot --}}
                                        <div class="tw:relative tw:z-10 tw:flex tw:items-center tw:justify-center">
                                            <template x-if="diffMode && getDiffRole(backup) === 'old'">
                                                <span class="tw:w-6 tw:h-6 tw:rounded-full tw:bg-red-600 tw:text-white tw:shadow-xs tw:ring-4 tw:ring-red-100 tw:dark:ring-red-900/50 tw:flex tw:items-center tw:justify-center" title="{{ __('config_backups.base') }}">
                                                    <i class="fa fa-minus tw:text-[9px]" aria-hidden="true"></i>
                                                </span>
                                            </template>
                                            <template x-if="diffMode && getDiffRole(backup) === 'new'">
                                                <span class="tw:w-6 tw:h-6 tw:rounded-full tw:bg-emerald-600 tw:text-white tw:shadow-xs tw:ring-4 tw:ring-emerald-100 tw:dark:ring-emerald-900/50 tw:flex tw:items-center tw:justify-center" title="{{ __('config_backups.compare') }}">
                                                    <i class="fa fa-plus tw:text-[9px]" aria-hidden="true"></i>
                                                </span>
                                            </template>
                                            <template x-if="diffMode && !getDiffRole(backup) && isIndexInRange(index)">
                                                <span class="tw:w-3 tw:h-3 tw:rounded-full tw:bg-blue-500 tw:ring-4 tw:ring-blue-100 tw:dark:ring-blue-900/50"></span>
                                            </template>
                                            <template x-if="diffMode && !getDiffRole(backup) && !isIndexInRange(index)">
                                                <span class="tw:w-5 tw:h-5 tw:rounded-full tw:border-2 tw:border-gray-300 tw:bg-white tw:dark:bg-dark-gray-400 tw:dark:border-dark-gray-100"></span>
                                            </template>
                                            <template x-if="!diffMode && isSelected(backup)">
                                                <span class="tw:w-6 tw:h-6 tw:rounded-full tw:bg-blue-600 tw:text-white tw:shadow-xs tw:ring-4 tw:ring-blue-100 tw:dark:ring-blue-900/50 tw:flex tw:items-center tw:justify-center">
                                                    <span class="tw:w-2 tw:h-2 tw:rounded-full tw:bg-white"></span>
                                                </span>
                                            </template>
                                            <template x-if="!diffMode && !isSelected(backup)">
                                                <span class="tw:w-5 tw:h-5 tw:rounded-full tw:border-2 tw:border-gray-300 tw:bg-white tw:dark:bg-dark-gray-400 tw:dark:border-dark-gray-100"></span>
                                            </template>
                                        </div>
                                    </div>

                                    {{-- Backup metadata --}}
                                    <div class="tw:flex-1 tw:min-w-0">
                                        <div class="tw:flex tw:items-center tw:gap-2">
                                            <span class="tw:text-sm tw:font-semibold tw:text-gray-900 tw:dark:text-dark-white-100"
                                                  x-text="formatDate(backup.date)"></span>
                                            <template x-if="index === 0">
                                                <span class="tw:text-[11px] tw:font-medium tw:text-gray-500 tw:dark:text-dark-white-400">(Latest)</span>
                                            </template>
                                        </div>
                                        <template x-if="backup.until">
                                            <span class="tw:block tw:text-xs tw:text-gray-500 tw:dark:text-dark-white-400">
                                                {{ __('config_backups.valid_until') }} <span x-text="formatDate(backup.until)"></span>
                                            </span>
                                        </template>
                                    </div>

                                    {{-- Type Badge (non-TEXT only) --}}
                                    <template x-if="backup.type !== 'TEXT'">
                                        <span class="tw:text-xs tw:font-medium tw:rounded tw:px-1.5 tw:py-0.5 tw:bg-gray-200 tw:text-gray-700 tw:dark:bg-dark-gray-200 tw:dark:text-dark-white-300 tw:shrink-0"
                                              x-text="backup.type"></span>
                                    </template>
                                </li>
                            </template>

                            <template x-if="hasMore">
                                <li>
                                    <button type="button"
                                            x-on:click="loadMore()"
                                            :disabled="loadingMore"
                                            class="lnms-btn lnms-btn-default tw:w-full tw:min-h-11 tw:rounded-none! tw:border-0! tw:border-t! tw:border-gray-200 tw:dark:border-dark-gray-200"
                                            x-text="loadingMore ? '{{ __('config_backups.loading') }}' : '{{ __('config_backups.load_more') }}'">
                                    </button>
                                </li>
                            </template>
                        </ul>
                    </x-slot>
                </x-panel>

                {{-- Config / diff pane --}}
                <x-panel class="tw:w-full tw:flex-1 tw:min-w-0 tw:overflow-hidden tw:self-start tw:mb-0!" x-ref="viewerPanel">
                    <x-slot name="heading" class="tw:flex tw:flex-wrap tw:items-center tw:justify-between tw:gap-2">
                        <h3 class="panel-title tw:flex tw:items-center tw:gap-2 tw:flex-wrap">
                            <template x-if="diffMode">
                                <div class="tw:flex tw:items-center tw:gap-2.5 tw:flex-wrap">
                                    <span class="tw:font-semibold">{{ __('config_backups.diff') }}:</span>
                                    <template x-if="activeDiffOrig && activeDiffRev">
                                        <div class="tw:inline-flex tw:items-center tw:gap-2.5 tw:flex-wrap">
                                            {{-- Base (Old) Version Dropdown --}}
                                            <div class="tw:inline-flex tw:items-center tw:gap-1.5">
                                                <span class="tw:w-5 tw:h-5 tw:rounded-full tw:bg-red-600 tw:text-white tw:text-[9px] tw:flex tw:items-center tw:justify-center tw:shrink-0" title="{{ __('config_backups.base') }}">
                                                    <i class="fa fa-minus" aria-hidden="true"></i>
                                                </span>
                                                <div class="tw:relative tw:inline-flex tw:items-center">
                                                    <select :value="activeOrigId"
                                                            x-on:change="onOrigDropdownChange($event.target.value)"
                                                            aria-label="{{ __('config_backups.base') }}"
                                                            class="tw:appearance-none tw:cursor-pointer tw:text-sm tw:font-medium tw:h-8 tw:py-0 tw:pl-2.5 tw:pr-7 tw:rounded-md tw:border tw:border-gray-300 tw:bg-white tw:text-gray-900 tw:dark:bg-dark-gray-400 tw:dark:text-dark-white-100 tw:dark:border-dark-gray-100 focus:tw:ring-2 focus:tw:ring-blue-500 focus:tw:outline-none">
                                                        <template x-for="b in textBackups" :key="'orig-' + b.id">
                                                            <option :value="b.id"
                                                                    :selected="b.id === activeOrigId"
                                                                    x-text="formatDate(b.date) + (b.id === backups[0]?.id ? ' (Latest)' : '')"></option>
                                                        </template>
                                                    </select>
                                                    <i class="fa fa-chevron-down tw:absolute tw:right-2.5 tw:text-[9px] tw:text-gray-500 tw:dark:text-dark-white-300 tw:pointer-events-none" aria-hidden="true"></i>
                                                </div>
                                            </div>

                                            {{-- Interactive direction toggle button --}}
                                            <button type="button"
                                                    x-on:click="toggleDiffDirection()"
                                                    title="{{ __('config_backups.reverse_direction') }}"
                                                    class="lnms-btn lnms-btn-default tw:h-8! tw:w-8! tw:p-0! tw:flex tw:items-center tw:justify-center tw:rounded-full tw:transition-transform tw:shrink-0"
                                                    aria-label="{{ __('config_backups.reverse_direction') }}">
                                                <i class="fa fa-arrow-right tw:transition-transform tw:duration-300"
                                                   :class="diffReversed ? 'tw:rotate-180' : ''"
                                                   aria-hidden="true"></i>
                                            </button>

                                            {{-- Compare (New) Version Dropdown --}}
                                            <div class="tw:inline-flex tw:items-center tw:gap-1.5">
                                                <span class="tw:w-5 tw:h-5 tw:rounded-full tw:bg-emerald-600 tw:text-white tw:text-[9px] tw:flex tw:items-center tw:justify-center tw:shrink-0" title="{{ __('config_backups.compare') }}">
                                                    <i class="fa fa-plus" aria-hidden="true"></i>
                                                </span>
                                                <div class="tw:relative tw:inline-flex tw:items-center">
                                                    <select :value="activeRevId"
                                                            x-on:change="onRevDropdownChange($event.target.value)"
                                                            aria-label="{{ __('config_backups.compare') }}"
                                                            class="tw:appearance-none tw:cursor-pointer tw:text-sm tw:font-medium tw:h-8 tw:py-0 tw:pl-2.5 tw:pr-7 tw:rounded-md tw:border tw:border-gray-300 tw:bg-white tw:text-gray-900 tw:dark:bg-dark-gray-400 tw:dark:text-dark-white-100 tw:dark:border-dark-gray-100 focus:tw:ring-2 focus:tw:ring-blue-500 focus:tw:outline-none">
                                                        <template x-for="b in textBackups" :key="'rev-' + b.id">
                                                            <option :value="b.id"
                                                                    :selected="b.id === activeRevId"
                                                                    x-text="formatDate(b.date) + (b.id === backups[0]?.id ? ' (Latest)' : '')"></option>
                                                        </template>
                                                    </select>
                                                    <i class="fa fa-chevron-down tw:absolute tw:right-2.5 tw:text-[9px] tw:text-gray-500 tw:dark:text-dark-white-300 tw:pointer-events-none" aria-hidden="true"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <template x-if="!diffMode">
                                <span class="tw:flex tw:items-center tw:gap-1.5">
                                    <span>{{ __('config_backups.configuration') }}<span x-show="selectedDisplayDate" x-text="': ' + formatDate(selectedDisplayDate)"></span></span>
                                </span>
                            </template>
                            <i x-show="loading" x-cloak class="fa fa-spinner tw:animate-spin tw:text-blue-500 tw:text-xs" aria-hidden="true"></i>
                        </h3>

                        {{-- Action buttons (Standard 32px icon buttons with tooltips) --}}
                        <div class="tw:flex tw:items-center tw:gap-2" x-cloak>
                            {{-- Single Config Actions --}}
                            <template x-if="showConfigView">
                                <div class="tw:flex tw:items-center tw:gap-2">
                                    <button type="button"
                                            x-on:click="downloadConfig()"
                                            title="{{ __('config_backups.download') }}"
                                            class="lnms-btn lnms-btn-default tw:h-8! tw:w-8! tw:p-0! tw:flex tw:items-center tw:justify-center tw:text-xs tw:transition-colors tw:shrink-0"
                                            aria-label="{{ __('config_backups.download') }}">
                                        <i class="fa fa-download" aria-hidden="true"></i>
                                    </button>
                                    <button type="button"
                                            x-on:click="copyToClipboard()"
                                            :title="copied ? '{{ __('config_backups.copied') }}' : '{{ __('config_backups.copy') }}'"
                                            class="lnms-btn lnms-btn-default tw:h-8! tw:w-8! tw:p-0! tw:flex tw:items-center tw:justify-center tw:text-xs tw:transition-colors tw:shrink-0"
                                            aria-label="{{ __('config_backups.copy') }}">
                                        <i class="fa" :class="copied ? 'fa-check tw:text-green-400' : 'fa-copy'" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </template>

                            {{-- Diff Actions --}}
                            <template x-if="showDiffView">
                                <div class="tw:flex tw:items-center tw:gap-2">
                                    <button type="button"
                                            x-on:click="downloadDiff()"
                                            title="{{ __('config_backups.download_diff') }}"
                                            class="lnms-btn lnms-btn-default tw:h-8! tw:w-8! tw:p-0! tw:flex tw:items-center tw:justify-center tw:text-xs tw:transition-colors tw:shrink-0"
                                            aria-label="{{ __('config_backups.download_diff') }}">
                                        <i class="fa fa-download" aria-hidden="true"></i>
                                    </button>
                                    <button type="button"
                                            x-on:click="copyDiff()"
                                            :title="copiedDiff ? '{{ __('config_backups.copied_diff') }}' : '{{ __('config_backups.copy_diff') }}'"
                                            class="lnms-btn lnms-btn-default tw:h-8! tw:w-8! tw:p-0! tw:flex tw:items-center tw:justify-center tw:text-xs tw:transition-colors tw:shrink-0"
                                            aria-label="{{ __('config_backups.copy_diff') }}">
                                        <i class="fa" :class="copiedDiff ? 'fa-check tw:text-green-400' : 'fa-copy'" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </x-slot>

                    <x-slot name="table">
                        {{-- Sleek top activity line --}}
                        <div x-show="loading" x-cloak class="tw:h-0.5 tw:w-full tw:bg-blue-100 tw:dark:bg-blue-950 tw:overflow-hidden tw:relative">
                            <div class="tw:h-full tw:bg-blue-500 tw:dark:bg-blue-400 tw:animate-pulse tw:w-full"></div>
                        </div>
                        {{-- error --}}
                        <div x-show="error" x-cloak
                             class="tw:m-3 tw:rounded-lg tw:border tw:border-red-300 tw:bg-red-50 tw:text-red-800 tw:dark:border-red-900 tw:dark:bg-red-900/30 tw:dark:text-red-300 tw:px-4 tw:py-3 tw:text-sm"
                             x-text="errorMessage()"></div>

                        {{-- loading when no content --}}
                        <div x-show="showSpinner && !showDiffView && !showConfigView" x-cloak class="tw:py-12 tw:text-center tw:text-gray-500 tw:dark:text-dark-white-400">
                            <i class="fa fa-spinner tw:animate-spin fa-2x"></i>
                        </div>

                        {{-- diff view --}}
                        <template x-if="showDiffView && hasDiffChanges">
                            <div class="tw:overflow-x-auto tw:transition-opacity tw:duration-150"
                                 :class="{ 'tw:opacity-60': loading }">
                                <table class="tw:w-full tw:m-0 tw:font-mono tw:text-xs tw:border-collapse">
                                    <thead class="tw:bg-gray-100/90 tw:dark:bg-dark-gray-400/90 tw:border-b tw:border-gray-200 tw:dark:border-dark-gray-200 tw:text-gray-600 tw:dark:text-dark-white-300 tw:select-none">
                                        <tr>
                                            <th class="tw:w-12 tw:px-2 tw:py-1.5 tw:text-right tw:font-medium tw:text-gray-400 tw:dark:text-dark-white-400 tw:border-r tw:border-gray-200 tw:dark:border-dark-gray-200">#</th>
                                            <th class="tw:w-6 tw:px-1 tw:py-1.5 tw:text-center tw:font-medium tw:text-gray-400 tw:dark:text-dark-white-400">±</th>
                                            <th class="tw:px-2 tw:py-1.5 tw:text-left tw:font-normal">
                                                <div class="tw:flex tw:items-center tw:gap-2">
                                                    <span class="tw:font-medium tw:text-gray-700 tw:dark:text-dark-white-200" x-text="diffRangeSummaryText"></span>
                                                    <span class="tw:text-gray-300 tw:dark:text-dark-gray-100">·</span>
                                                    <template x-if="diffStats">
                                                        <span class="tw:inline-flex tw:items-center tw:gap-1.5">
                                                            <span class="tw:text-green-600 tw:dark:text-green-400" x-text="'+' + diffStats.additions + ' {{ __('config_backups.additions') }}'"></span>
                                                            <span class="tw:text-gray-400 tw:dark:text-dark-white-400">,</span>
                                                            <span class="tw:text-red-600 tw:dark:text-red-400" x-text="'-' + diffStats.deletions + ' {{ __('config_backups.deletions') }}'"></span>
                                                        </span>
                                                    </template>
                                                </div>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="tw:align-text-top">
                                        <template x-for="(row, index) in diffRows" :key="index">
                                            <tr :class="{
                                                    'tw:bg-green-100 tw:dark:bg-green-900/40': row.mode === 'added',
                                                    'tw:bg-red-100 tw:dark:bg-red-900/40': row.mode === 'removed',
                                                }">
                                                <td class="tw:w-12 tw:px-2 tw:py-1 tw:text-right tw:select-none tw:text-gray-400 tw:dark:text-dark-white-400 tw:border-r tw:border-gray-200 tw:dark:border-dark-gray-200"
                                                    x-text="row.line ?? ''"></td>
                                                <td class="tw:w-6 tw:px-1 tw:py-1 tw:text-center tw:font-bold"
                                                    :class="{
                                                        'tw:text-green-700 tw:dark:text-green-400': row.mode === 'added',
                                                        'tw:text-red-700 tw:dark:text-red-400': row.mode === 'removed',
                                                    }"
                                                    x-text="row.mode === 'added' ? '+' : (row.mode === 'removed' ? '-' : '')"></td>
                                                <td class="tw:px-2 tw:py-1 tw:whitespace-pre-wrap tw:text-gray-800 tw:dark:text-dark-white-100" x-text="row.text"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </template>

                        {{-- no configuration changes empty state --}}
                        <div x-show="showDiffView && !hasDiffChanges" x-cloak
                             class="tw:py-16 tw:m-0 tw:text-center tw:space-y-1.5 tw:text-gray-500 tw:dark:text-dark-white-400">
                            <div class="tw:font-semibold tw:text-gray-700 tw:dark:text-dark-white-200">{{ __('config_backups.no_changes_title') }}</div>
                            <div class="tw:text-xs">{{ __('config_backups.no_changes_desc') }}</div>
                        </div>

                        {{-- waiting for diff selection --}}
                        <p x-show="showDiffPrompt" x-cloak
                           class="tw:py-12 tw:m-0 tw:text-center tw:text-gray-500 tw:dark:text-dark-white-400">
                            {{ __('config_backups.select_two_hint') }}
                        </p>

                        {{-- binary backup notice --}}
                        <p x-show="showBinaryNotice" x-cloak
                           class="tw:py-12 tw:m-0 tw:text-center tw:text-gray-500 tw:dark:text-dark-white-400"
                           x-text="messages.binary_not_supported"></p>

                        {{-- config view --}}
                        <template x-if="showConfigView">
                            <pre class="config-highlight line-numbers tw:m-0 tw:p-3 tw:border-0! tw:rounded-none! tw:font-mono tw:whitespace-pre-wrap tw:overflow-x-auto tw:bg-gray-50 tw:text-gray-800 tw:dark:bg-dark-gray-500 tw:dark:text-dark-white-200 tw:transition-opacity tw:duration-150"
                                 :class="{ 'tw:opacity-60': loading }"><code
                                    x-config-highlight="selected.content"
                                    data-os="{{ $data['os'] }}"
                                    data-config-highlighting="{{ $data['config_highlighting'] }}"></code></pre>
                        </template>
                    </x-slot>
                </x-panel>

                {{-- Help & Shortcuts Modal --}}
                <div x-show="showHelp" x-cloak
                     x-on:keydown.escape.window="showHelp = false"
                     class="tw:fixed tw:inset-0 tw:z-50 tw:flex tw:items-center tw:justify-center tw:p-4 tw:bg-black/50 tw:backdrop-blur-xs">
                    <div x-on:click.outside="showHelp = false"
                         class="tw:bg-white tw:dark:bg-dark-gray-500 tw:text-gray-900 tw:dark:text-dark-white-100 tw:rounded-xl tw:shadow-2xl tw:border tw:border-gray-200 tw:dark:border-dark-gray-200 tw:w-full tw:max-w-lg tw:overflow-hidden tw:flex tw:flex-col tw:max-h-[85vh]">

                        {{-- Modal Header --}}
                        <div class="tw:flex tw:items-center tw:justify-between tw:px-5 tw:py-3.5 tw:border-b tw:border-gray-200 tw:dark:border-dark-gray-200">
                            <h4 class="tw:m-0 tw:text-base tw:font-semibold tw:flex tw:items-center tw:gap-2">
                                <i class="fa fa-keyboard-o tw:text-blue-500" aria-hidden="true"></i>
                                {{ __('config_backups.help') }}
                            </h4>
                            <button type="button"
                                    x-on:click="showHelp = false"
                                    class="lnms-btn lnms-btn-default tw:h-7! tw:w-7! tw:p-0! tw:flex tw:items-center tw:justify-center tw:text-xs tw:rounded-full"
                                    aria-label="{{ __('config_backups.shortcut_exit_diff') }}">
                                <i class="fa fa-times" aria-hidden="true"></i>
                            </button>
                        </div>

                        {{-- Modal Body --}}
                        <div class="tw:p-5 tw:overflow-y-auto tw:space-y-5 tw:text-sm">
                            {{-- UI Interactions --}}
                            <div>
                                <h5 class="tw:text-xs tw:font-semibold tw:uppercase tw:tracking-wider tw:text-gray-500 tw:dark:text-dark-white-400 tw:mb-2.5">
                                    {{ __('config_backups.interactions') }}
                                </h5>
                                <ul class="tw:list-none tw:p-0 tw:m-0 tw:space-y-2 tw:text-xs">
                                    <li class="tw:flex tw:items-start tw:gap-2">
                                        <i class="fa fa-check tw:text-blue-500 tw:mt-0.5 tw:text-xs" aria-hidden="true"></i>
                                        <span>{{ __('config_backups.interaction_click') }}</span>
                                    </li>
                                    <li class="tw:flex tw:items-start tw:gap-2">
                                        <i class="fa fa-check tw:text-blue-500 tw:mt-0.5 tw:text-xs" aria-hidden="true"></i>
                                        <span>{{ __('config_backups.interaction_shift_click_range') }}</span>
                                    </li>
                                    <li class="tw:flex tw:items-start tw:gap-2">
                                        <i class="fa fa-check tw:text-blue-500 tw:mt-0.5 tw:text-xs" aria-hidden="true"></i>
                                        <span>{{ __('config_backups.interaction_dropdowns') }}</span>
                                    </li>
                                </ul>
                            </div>

                            {{-- Navigate History --}}
                            <div>
                                <h5 class="tw:text-xs tw:font-semibold tw:uppercase tw:tracking-wider tw:text-gray-500 tw:dark:text-dark-white-400 tw:mb-2.5">
                                    {{ __('config_backups.navigate_history') }}
                                </h5>
                                <div class="tw:grid tw:grid-cols-1 sm:tw:grid-cols-2 tw:gap-2.5 tw:text-xs">
                                    <div class="tw:flex tw:items-center tw:justify-between tw:p-2 tw:rounded-md tw:bg-gray-50 tw:dark:bg-dark-gray-400">
                                        <span class="tw:text-gray-600 tw:dark:text-dark-white-300">{{ __('config_backups.shortcut_older') }}</span>
                                        <div class="tw:flex tw:gap-1">
                                            <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-xs tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">j</kbd>
                                            <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-xs tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">↓</kbd>
                                        </div>
                                    </div>
                                    <div class="tw:flex tw:items-center tw:justify-between tw:p-2 tw:rounded-md tw:bg-gray-50 tw:dark:bg-dark-gray-400">
                                        <span class="tw:text-gray-600 tw:dark:text-dark-white-300">{{ __('config_backups.shortcut_newer') }}</span>
                                        <div class="tw:flex tw:gap-1">
                                            <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-xs tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">k</kbd>
                                            <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-xs tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">↑</kbd>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Compare Revisions --}}
                            <div>
                                <h5 class="tw:text-xs tw:font-semibold tw:uppercase tw:tracking-wider tw:text-gray-500 tw:dark:text-dark-white-400 tw:mb-2.5">
                                    {{ __('config_backups.compare_revisions') }}
                                </h5>
                                <div class="tw:grid tw:grid-cols-1 sm:tw:grid-cols-2 tw:gap-2.5 tw:text-xs">
                                    <div class="tw:flex tw:items-center tw:justify-between tw:p-2 tw:rounded-md tw:bg-gray-50 tw:dark:bg-dark-gray-400">
                                        <span class="tw:text-gray-600 tw:dark:text-dark-white-300">{{ __('config_backups.shortcut_toggle_diff') }}</span>
                                        <div class="tw:flex tw:gap-1">
                                            <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-xs tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">d</kbd>
                                            <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-xs tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">c</kbd>
                                        </div>
                                    </div>
                                    <div class="tw:flex tw:items-center tw:justify-between tw:p-2 tw:rounded-md tw:bg-gray-50 tw:dark:bg-dark-gray-400">
                                        <span class="tw:text-gray-600 tw:dark:text-dark-white-300">{{ __('config_backups.shortcut_reverse_diff') }}</span>
                                        <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-xs tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">r</kbd>
                                    </div>
                                    <div class="tw:flex tw:items-center tw:justify-between tw:p-2 tw:rounded-md tw:bg-gray-50 tw:dark:bg-dark-gray-400">
                                        <span class="tw:text-gray-600 tw:dark:text-dark-white-300">{{ __('config_backups.shortcut_expand_older') }}</span>
                                        <div class="tw:flex tw:items-center tw:gap-1">
                                            <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-xs tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">Shift</kbd>
                                            <span class="tw:text-gray-500 tw:dark:text-dark-white-300">+</span>
                                            <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-xs tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">j/↓</kbd>
                                        </div>
                                    </div>
                                    <div class="tw:flex tw:items-center tw:justify-between tw:p-2 tw:rounded-md tw:bg-gray-50 tw:dark:bg-dark-gray-400">
                                        <span class="tw:text-gray-600 tw:dark:text-dark-white-300">{{ __('config_backups.shortcut_expand_newer') }}</span>
                                        <div class="tw:flex tw:items-center tw:gap-1">
                                            <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-xs tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">Shift</kbd>
                                            <span class="tw:text-gray-500 tw:dark:text-dark-white-300">+</span>
                                            <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-xs tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">k/↑</kbd>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- General --}}
                            <div>
                                <h5 class="tw:text-xs tw:font-semibold tw:uppercase tw:tracking-wider tw:text-gray-500 tw:dark:text-dark-white-400 tw:mb-2.5">
                                    {{ __('config_backups.general') }}
                                </h5>
                                <div class="tw:grid tw:grid-cols-1 sm:tw:grid-cols-2 tw:gap-2.5 tw:text-xs">
                                    <div class="tw:flex tw:items-center tw:justify-between tw:p-2 tw:rounded-md tw:bg-gray-50 tw:dark:bg-dark-gray-400">
                                        <span class="tw:text-gray-600 tw:dark:text-dark-white-300">{{ __('config_backups.shortcut_help') }}</span>
                                        <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-xs tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">?</kbd>
                                    </div>
                                    <div class="tw:flex tw:items-center tw:justify-between tw:p-2 tw:rounded-md tw:bg-gray-50 tw:dark:bg-dark-gray-400">
                                        <span class="tw:text-gray-600 tw:dark:text-dark-white-300">{{ __('config_backups.shortcut_exit_diff') }}</span>
                                        <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-xs tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">Esc</kbd>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </x-device.page>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            window.Alpine.directive('config-highlight', (element, { expression }, { effect, evaluateLater }) => {
                const evaluateContent = evaluateLater(expression);
                let updateId = 0;

                effect(() => {
                    evaluateContent((content) => {
                        const currentUpdateId = ++updateId;

                        window.LibreNMS.loadConfigHighlight().then(({ default: highlightConfig }) => {
                            if (currentUpdateId === updateId) {
                                highlightConfig(element, content);
                            }
                        });
                    });
                });
            });

            window.Alpine.data('configBackups', (config) => ({
                // Data
                backups: [],
                page: 0,
                totalPages: 0,
                total: 0,
                urls: config.urls || {},
                messages: config.messages || {},

                // UI State
                selected: null,
                selectedBackupId: null,
                anchorIndex: null,
                focusIndex: null,
                loading: false,
                loadingMore: false,
                loadingBackups: false,
                showSpinner: false,
                showHelp: false,
                error: null,
                copied: false,
                copiedDiff: false,
                canRefresh: config.can_refresh || false,
                refreshing: false,

                // Diff State
                diffMode: false,
                diffSelection: [],
                diffGroups: null,
                diffReversed: false,

                async init() {
                    await this.loadLatest();
                    this.loadBackupPage(0);
                },

                // --- Loading Logic ---
                beginLoading() {
                    this.loading = true;
                    this.showSpinner = false;
                    return setTimeout(() => {
                        if (this.loading) this.showSpinner = true;
                    }, 300);
                },

                endLoading(timer) {
                    clearTimeout(timer);
                    this.loading = false;
                    this.showSpinner = false;
                },

                // ── Backup list ──────────────────────────────────────────

                async loadBackupPage(page, append = false) {
                    const loadingKey = append ? 'loadingMore' : 'loadingBackups';
                    this[loadingKey] = true;

                    try {
                        const { data } = await window.axios.get(this.urls.backups, { params: { page } });
                        const mapped = data.backups.map((b) => ({ ...b, page }));

                        if (append) {
                            this.backups.push(...mapped);
                        } else {
                            this.backups = mapped;
                        }

                        this.page = data.page;
                        this.totalPages = data.totalPages;
                        this.total = data.total;
                    } catch (error) {
                        if (!this.error) {
                            this.error = this.requestError(error);
                        }
                    } finally {
                        this[loadingKey] = false;
                    }
                },

                loadMore() {
                    this.loadBackupPage(this.page + 1, true);
                },

                get hasMore() {
                    return this.page < this.totalPages - 1;
                },

                // ── Backup content ───────────────────────────────────────

                async loadLatest() {
                    const timer = this.beginLoading();
                    try {
                        const { data } = await window.axios.get(this.urls.backup);
                        if (!this.selected) {
                            this.selected = data;
                            this.selectedBackupId = data.id;
                        }
                    } catch (error) {
                        if (!this.selected) {
                            this.error = this.requestError(error);
                        }
                    } finally {
                        this.endLoading(timer);
                    }
                },

                async loadBackupContent(backup) {
                    const timer = this.beginLoading();
                    try {
                        const { data } = await window.axios.get(this.urls.backup, {
                            params: { backup: backup.id, page: backup.page },
                        });
                        if (this.selectedBackupId === backup.id || (!this.selectedBackupId && this.selected?.id === backup.id)) {
                            this.selected = { ...backup, content: data.content };
                        }
                    } catch (error) {
                        if (this.selectedBackupId === backup.id) {
                            this.error = this.requestError(error);
                        }
                    } finally {
                        this.endLoading(timer);
                    }
                },

                // ── Keyboard Shortcuts ──────────────────────────────────
                handleKeyDown(e) {
                    if (e.target.closest('input, textarea, select')) return;

                    if (e.key === '?' || (e.key === '/' && e.shiftKey)) {
                        e.preventDefault();
                        this.showHelp = !this.showHelp;
                        return;
                    }

                    if (e.key === 'Escape') {
                        if (this.showHelp) {
                            e.preventDefault();
                            this.showHelp = false;
                            return;
                        }
                        if (this.diffMode) {
                            e.preventDefault();
                            this.toggleDiffMode();
                        }
                        return;
                    }

                    if (e.key === 'd' || e.key === 'c') {
                        if (this.total > 1) {
                            e.preventDefault();
                            this.toggleDiffMode();
                        }
                        return;
                    }

                    if (e.key === 'r' && this.diffMode && this.diffReady) {
                        e.preventDefault();
                        this.toggleDiffDirection();
                        return;
                    }

                    if (e.key === 'j' || e.key === 'ArrowDown') {
                        e.preventDefault();
                        this.navigateHistory(1, e.shiftKey);
                        return;
                    }

                    if (e.key === 'k' || e.key === 'ArrowUp') {
                        e.preventDefault();
                        this.navigateHistory(-1, e.shiftKey);
                        return;
                    }
                },

                navigateHistory(step, isShift = false) {
                    if (!this.backups.length) return;

                    if (this.diffMode) {
                        if (isShift) {
                            if (this.anchorIndex === null) {
                                const revIdx = this.activeDiffRev ? this.backups.findIndex(b => b.id === this.activeDiffRev.id) : 0;
                                this.anchorIndex = revIdx !== -1 ? revIdx : 0;
                            }
                            if (this.focusIndex === null) {
                                const origIdx = this.activeDiffOrig ? this.backups.findIndex(b => b.id === this.activeDiffOrig.id) : this.anchorIndex + 1;
                                this.focusIndex = origIdx !== -1 ? origIdx : this.anchorIndex + 1;
                            }

                            let nextFocus = this.focusIndex + step;
                            if (nextFocus === this.anchorIndex) {
                                nextFocus = this.anchorIndex + step;
                            }

                            if (nextFocus < 0 || nextFocus >= this.backups.length) {
                                return; // Boundary reached
                            }

                            this.focusIndex = nextFocus;
                            const minIdx = Math.min(this.anchorIndex, this.focusIndex);
                            const maxIdx = Math.max(this.anchorIndex, this.focusIndex);

                            const older = this.backups[maxIdx];
                            const newer = this.backups[minIdx];

                            if (older && newer && older.type === 'TEXT' && newer.type === 'TEXT') {
                                if (this.diffSelection.length === 2 && this.diffSelection[0].id === older.id && this.diffSelection[1].id === newer.id && !this.diffReversed) {
                                    return;
                                }
                                this.diffReversed = false;
                                this.diffSelection = [older, newer];
                                this.loadDiff();
                                this.scrollToIndex(this.focusIndex);
                            }
                            return;
                        }

                        // Regular Step-Through Diff Navigation (step = +1 / -1)
                        const currentAnchor = this.anchorIndex ?? (this.activeDiffRev ? this.backups.findIndex(b => b.id === this.activeDiffRev.id) : 0);
                        const nextAnchor = currentAnchor + step;
                        if (nextAnchor < 0 || nextAnchor >= this.backups.length) {
                            return; // Boundary reached - do nothing
                        }

                        const nextBackup = this.backups[nextAnchor];
                        if (!nextBackup) return;

                        this.selectBackup(nextBackup, nextAnchor);
                        this.scrollToIndex(nextAnchor);
                        return;
                    }

                    // Single Mode Navigation
                    const curId = this.selectedBackupId ?? this.selected?.id;
                    const currentIndex = this.backups.findIndex(b => b.id === curId);
                    const currentIdx = currentIndex !== -1 ? currentIndex : 0;

                    if (isShift) {
                        const targetIdx = currentIdx + step;
                        if (targetIdx < 0 || targetIdx >= this.backups.length) {
                            return;
                        }
                        this.anchorIndex = currentIdx;
                        this.focusIndex = targetIdx;
                        const minIdx = Math.min(this.anchorIndex, this.focusIndex);
                        const maxIdx = Math.max(this.anchorIndex, this.focusIndex);

                        this.diffMode = true;
                        this.diffReversed = false;
                        this.diffSelection = [this.backups[maxIdx], this.backups[minIdx]];
                        this.loadDiff();
                        this.scrollToIndex(targetIdx);
                        return;
                    }

                    const nextIndex = currentIdx + step;
                    if (nextIndex < 0 || nextIndex >= this.backups.length) {
                        return; // Boundary reached - do nothing
                    }

                    const nextBackup = this.backups[nextIndex];
                    if (!nextBackup) return;

                    this.selectBackup(nextBackup, nextIndex);
                    this.scrollToIndex(nextIndex);
                },

                scrollToIndex(index) {
                    const list = this.$refs.timelineList;
                    if (!list) return;
                    const row = list.querySelector(`[data-backup-index="${index}"]`);
                    row?.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                },

                scrollToTop() {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    const panel = this.$refs.viewerPanel;
                    if (panel) {
                        const scrollable = panel.querySelector('pre, .tw\\:overflow-x-auto');
                        if (scrollable) {
                            scrollable.scrollTo({ top: 0, behavior: 'smooth' });
                        }
                    }
                },

                selectBackup(backup, index, event = null) {
                    if (backup.type !== 'TEXT') {
                        if (!this.diffMode) {
                            this.selected = backup;
                            this.selectedBackupId = backup.id;
                            this.anchorIndex = index;
                            this.focusIndex = index;
                            this.selected.content = null;
                        }
                        return;
                    }

                    // Shift-Click Range Selection (Method C)
                    if (event && event.shiftKey) {
                        if (this.anchorIndex === null) {
                            const curId = this.selectedBackupId ?? this.selected?.id ?? this.activeDiffRev?.id;
                            const found = this.backups.findIndex(b => b.id === curId);
                            this.anchorIndex = found !== -1 ? found : 0;
                        }

                        this.focusIndex = index;
                        const minIdx = Math.min(this.anchorIndex, this.focusIndex);
                        const maxIdx = Math.max(this.anchorIndex, this.focusIndex);

                        const older = this.backups[maxIdx];
                        const newer = this.backups[minIdx];

                        if (older && newer && older.type === 'TEXT' && newer.type === 'TEXT') {
                            if (this.diffMode && this.diffSelection.length === 2 && this.diffSelection[0].id === older.id && this.diffSelection[1].id === newer.id && !this.diffReversed) {
                                this.scrollToTop();
                                return;
                            }
                            this.diffMode = true;
                            this.diffReversed = false;
                            this.diffSelection = [older, newer];
                            this.loadDiff();
                            return;
                        }
                    }

                    if (this.diffMode) {
                        // Step-through diff: clicking row N sets anchor = N, focus = predecessor (N+1)
                        const predecessorIdx = index + 1 < this.backups.length && this.backups[index + 1].type === 'TEXT'
                            ? index + 1
                            : (index - 1 >= 0 && this.backups[index - 1].type === 'TEXT' ? index - 1 : null);

                        if (predecessorIdx !== null) {
                            this.anchorIndex = index;
                            this.focusIndex = predecessorIdx;

                            const minIdx = Math.min(this.anchorIndex, this.focusIndex);
                            const maxIdx = Math.max(this.anchorIndex, this.focusIndex);
                            const older = this.backups[maxIdx];
                            const newer = this.backups[minIdx];

                            if (this.diffSelection.length === 2 && this.diffSelection[0].id === older.id && this.diffSelection[1].id === newer.id && !this.diffReversed) {
                                this.scrollToTop();
                                return; // Diff is already loaded
                            }
                            this.diffReversed = false;
                            this.diffSelection = [older, newer];
                            this.loadDiff();
                        }
                        return;
                    }

                    if ((this.selectedBackupId ?? this.selected?.id) === backup.id && this.selected?.content != null) {
                        this.scrollToTop();
                        return;
                    }

                    this.selectedBackupId = backup.id;
                    this.anchorIndex = index;
                    this.focusIndex = index;
                    this.error = null;
                    this.loadBackupContent(backup);
                },

                // ── Diff ─────────────────────────────────────────────────

                toggleDiffMode() {
                    this.diffMode = !this.diffMode;
                    this.error = null;
                    this.diffReversed = false;
                    this.diffMode ? this.enterDiffMode() : this.exitDiffMode();
                },

                enterDiffMode() {
                    const textBackups = [];
                    let selectedIdx = -1;
                    const curId = this.selectedBackupId ?? this.selected?.id;
                    for (const b of this.backups) {
                        if (b.type !== 'TEXT') continue;
                        if (selectedIdx === -1 && curId === b.id) {
                            selectedIdx = textBackups.length;
                        }
                        textBackups.push(b);
                    }

                    if (selectedIdx !== -1) {
                        const pair = textBackups[selectedIdx + 1] ?? textBackups[selectedIdx - 1];
                        this.diffSelection = pair ? [textBackups[selectedIdx], pair] : [textBackups[selectedIdx]];
                        this.anchorIndex = this.backups.findIndex(b => b.id === textBackups[selectedIdx].id);
                        this.focusIndex = pair ? this.backups.findIndex(b => b.id === pair.id) : this.anchorIndex;
                    } else {
                        this.diffSelection = textBackups.slice(0, 2);
                        this.anchorIndex = this.diffSelection[0] ? this.backups.findIndex(b => b.id === this.diffSelection[0].id) : 0;
                        this.focusIndex = this.diffSelection[1] ? this.backups.findIndex(b => b.id === this.diffSelection[1].id) : this.anchorIndex;
                    }

                    if (this.diffSelection.length === 2) {
                        this.loadDiff();
                    } else {
                        this.diffGroups = null;
                    }
                },

                exitDiffMode() {
                    if (this.diffSelection[0]) {
                        const target = this.diffSelection[0];
                        this.selectedBackupId = target.id;
                        const idx = this.backups.findIndex(b => b.id === target.id);
                        this.anchorIndex = idx !== -1 ? idx : 0;
                        this.focusIndex = this.anchorIndex;
                        if (this.selected?.id !== target.id || this.selected?.content == null) {
                            this.loadBackupContent(target);
                        }
                    }
                    this.diffSelection = [];
                    this.diffGroups = null;
                    this.diffReversed = false;
                },

                toggleDiffDirection() {
                    this.diffReversed = !this.diffReversed;
                    this.loadDiff();
                },

                get textBackups() {
                    return this.backups.filter(b => b.type === 'TEXT');
                },

                get sortedDiff() {
                    if (this.diffSelection.length !== 2) return null;
                    const [b1, b2] = this.diffSelection;
                    const [older, newer] = b1.date <= b2.date ? [b1, b2] : [b2, b1];

                    return this.diffReversed ? { orig: newer, rev: older } : { orig: older, rev: newer };
                },

                get activeDiffOrig() {
                    return this.sortedDiff?.orig ?? null;
                },

                get activeDiffRev() {
                    return this.sortedDiff?.rev ?? null;
                },

                get activeOrigId() {
                    return this.activeDiffOrig?.id ?? '';
                },

                get activeRevId() {
                    return this.activeDiffRev?.id ?? '';
                },

                onOrigDropdownChange(selectedId) {
                    const backup = this.backups.find(b => String(b.id) === String(selectedId));
                    if (!backup || backup.type !== 'TEXT' || !this.activeDiffRev) return;

                    if (String(backup.id) === String(this.activeRevId)) {
                        const other = this.textBackups.find(b => String(b.id) !== String(selectedId));
                        if (other) this.diffSelection = [backup, other];
                    } else {
                        this.diffSelection = [backup, this.activeDiffRev];
                    }
                    const idxOrig = this.backups.findIndex(b => b.id === this.diffSelection[0].id);
                    const idxRev = this.backups.findIndex(b => b.id === this.diffSelection[1].id);
                    this.anchorIndex = idxOrig !== -1 ? idxOrig : 0;
                    this.focusIndex = idxRev !== -1 ? idxRev : this.anchorIndex;
                    this.loadDiff();
                },

                onRevDropdownChange(selectedId) {
                    const backup = this.backups.find(b => String(b.id) === String(selectedId));
                    if (!backup || backup.type !== 'TEXT' || !this.activeDiffOrig) return;

                    if (String(backup.id) === String(this.activeOrigId)) {
                        const other = this.textBackups.find(b => String(b.id) !== String(selectedId));
                        if (other) this.diffSelection = [this.activeDiffOrig, other];
                    } else {
                        this.diffSelection = [this.activeDiffOrig, backup];
                    }
                    const idxOrig = this.backups.findIndex(b => b.id === this.diffSelection[0].id);
                    const idxRev = this.backups.findIndex(b => b.id === this.diffSelection[1].id);
                    this.anchorIndex = idxOrig !== -1 ? idxOrig : 0;
                    this.focusIndex = idxRev !== -1 ? idxRev : this.anchorIndex;
                    this.loadDiff();
                },

                get diffReady() {
                    return this.diffGroups !== null && this.diffSelection.length === 2;
                },

                get hasDiffChanges() {
                    if (!this.diffGroups) return false;
                    return this.diffGroups.some(g => g.type !== 'COMMON');
                },

                get diffStats() {
                    if (!this.diffGroups) return null;
                    let additions = 0;
                    let deletions = 0;
                    this.diffGroups.forEach((g) => {
                        if (g.type === 'INSERTED') {
                            additions += g.revised.length;
                        } else if (g.type === 'DELETED') {
                            deletions += g.original.length;
                        } else if (g.type === 'CHANGED') {
                            deletions += g.original.length;
                            additions += g.revised.length;
                        }
                    });
                    return { additions, deletions };
                },

                get diffRangeSummaryText() {
                    const range = this.selectedRangeIndices;
                    if (!range) return '{{ __('config_backups.revision_step') }}';
                    const count = range[1] - range[0] + 1;
                    if (count <= 2) {
                        return '{{ __('config_backups.revision_step') }}';
                    }
                    return '{{ __('config_backups.revisions_spanned', ['count' => '__COUNT__']) }}'.replace('__COUNT__', count);
                },

                get diffRoleMap() {
                    if (!this.diffMode || !this.sortedDiff) return {};
                    const { orig, rev } = this.sortedDiff;
                    return { [orig.id]: 'old', [rev.id]: 'new' };
                },

                async loadDiff() {
                    if (!this.sortedDiff) return;
                    const { orig, rev } = this.sortedDiff;
                    if (orig.id === rev.id) {
                        this.diffGroups = [];
                        return;
                    }
                    const timer = this.beginLoading();
                    this.error = null;

                    try {
                        const { data } = await window.axios.get(this.urls.diff, {
                            params: { orig: orig.id, rev: rev.id },
                        });
                        this.diffGroups = data.groups;
                    } catch (error) {
                        this.error = this.requestError(error);
                    } finally {
                        this.endLoading(timer);
                    }
                },

                get diffRows() {
                    if (!this.diffGroups) {
                        return [];
                    }

                    const rows = [];
                    const push = (mode, lines) => {
                        lines.forEach((line) => {
                            rows.push({
                                mode,
                                line: line.line,
                                text: line.text,
                            });
                        });
                    };

                    this.diffGroups.forEach((group) => {
                        if (group.type === 'COMMON') {
                            push('common', group.original);
                            return;
                        }
                        if (group.type === 'DELETED' || group.type === 'CHANGED') {
                            push('removed', group.original);
                        }
                        if (group.type === 'INSERTED' || group.type === 'CHANGED') {
                            push('added', group.revised);
                        }
                    });

                    return rows;
                },

                getDiffRole(backup) {
                    return this.diffRoleMap[backup.id] ?? null;
                },

                // ── Timeline Styling Helpers ─────────────────────────────

                get selectedRangeIndices() {
                    if (this.diffMode && this.diffSelection.length === 2) {
                        const idx1 = this.backups.findIndex(b => b.id === this.diffSelection[0].id);
                        const idx2 = this.backups.findIndex(b => b.id === this.diffSelection[1].id);
                        if (idx1 !== -1 && idx2 !== -1) {
                            return [Math.min(idx1, idx2), Math.max(idx1, idx2)];
                        }
                    }
                    return null;
                },

                isIndexInRange(index) {
                    const range = this.selectedRangeIndices;
                    return range && index >= range[0] && index <= range[1];
                },

                getRowRangeClass(backup, index) {
                    if (this.isIndexInRange(index)) {
                        return 'tw:bg-blue-50/70 tw:dark:bg-blue-950/30';
                    }
                    if (!this.diffMode && this.isSelected(backup)) {
                        return 'tw:bg-gray-100 tw:dark:bg-dark-gray-300';
                    }
                    return 'tw:hover:bg-gray-50 tw:dark:hover:bg-dark-gray-300';
                },

                isTopConnectorActive(index) {
                    const range = this.selectedRangeIndices;
                    return !!(range && index > range[0] && index <= range[1]);
                },

                isBottomConnectorActive(index) {
                    const range = this.selectedRangeIndices;
                    return !!(range && index >= range[0] && index < range[1]);
                },

                // ── View visibility ─────────────────────────────────────

                get showDiffView() {
                    return this.diffMode && this.diffReady;
                },

                get showDiffPrompt() {
                    return !this.showSpinner && this.diffMode && !this.diffReady && !this.error;
                },

                get showBinaryNotice() {
                    return !this.showSpinner && !this.diffMode && this.selected && this.selected.type !== 'TEXT';
                },

                get showConfigView() {
                    return !this.diffMode && this.selected?.content != null && (!this.selected || this.selected.type === 'TEXT');
                },

                // ── UI helpers ───────────────────────────────────────────

                isBackupDisabled(backup) {
                    return this.diffMode && backup.type !== 'TEXT';
                },

                get diffSelectionIdSet() {
                    return new Set(this.diffSelection.map((b) => b.id));
                },

                isSelected(backup) {
                    if (this.diffMode) {
                        return this.diffSelectionIdSet.has(backup.id);
                    }

                    const currentId = this.selectedBackupId ?? this.selected?.id;
                    return currentId === backup.id;
                },

                get selectedDisplayDate() {
                    if (this.selectedBackupId) {
                        const b = this.backups.find(b => b.id === this.selectedBackupId);
                        if (b) return b.date;
                    }
                    return this.selected?.date;
                },

                errorMessage() {
                    return this.messages[this.error] || this.messages.request_failed || this.error;
                },

                formatDate(ts) {
                    return ts ? window.LibreNMS.Date.display(ts) : '';
                },

                requestError(error) {
                    return error.response?.data?.error ?? 'request_failed';
                },

                // ── Export Actions (Config & Diff) ───────────────────────

                generateUnifiedDiff() {
                    if (!this.diffGroups || !this.activeDiffOrig || !this.activeDiffRev) {
                        return '';
                    }

                    const origDate = this.formatDate(this.activeDiffOrig.date);
                    const revDate = this.formatDate(this.activeDiffRev.date);
                    let diffText = `--- Base (${origDate})\n+++ Compare (${revDate})\n`;

                    this.diffGroups.forEach((group) => {
                        if (group.type === 'COMMON') {
                            group.original.forEach((l) => {
                                diffText += `  ${l.text}\n`;
                            });
                        } else if (group.type === 'DELETED') {
                            group.original.forEach((l) => {
                                diffText += `-${l.text}\n`;
                            });
                        } else if (group.type === 'INSERTED') {
                            group.revised.forEach((l) => {
                                diffText += `+${l.text}\n`;
                            });
                        } else if (group.type === 'CHANGED') {
                            group.original.forEach((l) => {
                                diffText += `-${l.text}\n`;
                            });
                            group.revised.forEach((l) => {
                                diffText += `+${l.text}\n`;
                            });
                        }
                    });

                    return diffText;
                },

                downloadDiff() {
                    const diffText = this.generateUnifiedDiff();
                    if (!diffText) return;

                    const origDateStr = this.activeDiffOrig?.date
                        ? new Date(this.activeDiffOrig.date * 1000).toISOString().split('T')[0]
                        : 'orig';
                    const revDateStr = this.activeDiffRev?.date
                        ? new Date(this.activeDiffRev.date * 1000).toISOString().split('T')[0]
                        : 'rev';
                    const hostname = config.hostname ? `${config.hostname}-` : '';
                    const filename = `${hostname}config-diff-${origDateStr}-to-${revDateStr}.diff`;

                    const blob = new Blob([diffText], { type: 'text/x-diff;charset=utf-8' });
                    const url = URL.createObjectURL(blob);

                    Object.assign(document.createElement('a'), { href: url, download: filename }).click();
                    URL.revokeObjectURL(url);
                },

                copyDiff() {
                    const diffText = this.generateUnifiedDiff();
                    if (!diffText) return;

                    navigator.clipboard.writeText(diffText).then(() => {
                        this.copiedDiff = true;
                        setTimeout(() => {
                            this.copiedDiff = false;
                        }, 2000);
                    }).catch((error) => {
                        console.error('Failed to copy diff to clipboard:', error);
                    });
                },

                downloadConfig() {
                    if (!this.selected?.content) {
                        return;
                    }

                    const dateStr = this.selected?.date
                        ? new Date(this.selected.date * 1000).toISOString().split('T')[0]
                        : 'latest';
                    const hostname = config.hostname ? `${config.hostname}-` : '';
                    const filename = `${hostname}config-${dateStr}.txt`;
                    const blob = new Blob([this.selected.content], { type: 'text/plain;charset=utf-8' });
                    const url = URL.createObjectURL(blob);

                    Object.assign(document.createElement('a'), { href: url, download: filename }).click();
                    URL.revokeObjectURL(url);
                },

                copyToClipboard() {
                    if (!this.selected?.content) {
                        return;
                    }

                    navigator.clipboard.writeText(this.selected.content).then(() => {
                        this.copied = true;
                        setTimeout(() => {
                            this.copied = false;
                        }, 2000);
                    }).catch((error) => {
                        console.error('Failed to copy configuration to clipboard:', error);
                    });
                },

                refresh() {
                    if (this.refreshing) {
                        return;
                    }

                    this.refreshing = true;
                    window.axios.post(this.urls.refresh)
                        .then(({ data }) => {
                            window.toastr?.success(data.message);
                        })
                        .catch((error) => {
                            window.toastr?.error(error.response?.data?.message || this.messages.request_failed);
                        })
                        .finally(() => {
                            this.refreshing = false;
                        });
                },
            }));
        });
    </script>
@endpush
