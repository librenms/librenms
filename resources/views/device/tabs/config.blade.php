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
                <x-panel class="tw:w-full tw:lg:w-md tw:lg:shrink-0 tw:overflow-hidden tw:self-start tw:lg:sticky tw:lg:top-[calc(52px+1rem)] tw:mb-0">
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
                                    class="lnms-btn tw:h-8 tw:px-3 tw:flex tw:items-center tw:gap-1.5 tw:whitespace-nowrap tw:text-sm tw:transition-colors"
                                    :class="{ 'lnms-btn-default': diffMode, 'lnms-btn-primary': !diffMode }">
                                <i class="fa" :class="diffMode ? 'fa-times' : 'fa-exchange'" aria-hidden="true"></i>
                                <span x-text="diffMode ? '{{ __('config_backups.exit_diff') }}' : '{{ __('config_backups.compare') }}'"></span>
                            </button>
                            <button type="button"
                                    x-show="canRefresh" x-cloak
                                    x-on:click="refresh()"
                                    :disabled="refreshing"
                                    title="{{ __('config_backups.refresh') }}"
                                    class="lnms-btn lnms-btn-success tw:h-8 tw:w-8 tw:flex tw:items-center tw:justify-center tw:text-xs tw:transition-colors tw:disabled:opacity-50 tw:shrink-0"
                                    aria-label="{{ __('config_backups.refresh') }}">
                                <i class="fa fa-refresh" :class="refreshing ? 'tw:animate-spin' : ''" aria-hidden="true"></i>
                            </button>
                            <button type="button"
                                    x-on:click="showHelp = true"
                                    title="{{ __('config_backups.help') }}"
                                    class="lnms-btn lnms-btn-default tw:h-8 tw:w-8 tw:flex tw:items-center tw:justify-center tw:text-sm tw:transition-colors tw:shrink-0"
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
                                    <div class="tw:relative tw:w-7 tw:-my-3 tw:self-stretch tw:shrink-0 tw:flex tw:items-center tw:justify-center">
                                        {{-- Top connector line --}}
                                        <template x-if="index > 0">
                                            <div class="tw:absolute tw:-top-px tw:h-[calc(50%+1px)] tw:left-1/2 tw:-translate-x-1/2 tw:z-0 tw:transition-colors"
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
                                                <span class="tw:w-6 tw:h-6 tw:rounded-full tw:bg-red-500 tw:text-white tw:shadow-xs tw:ring-4 tw:ring-red-100 tw:dark:ring-red-900/50 tw:flex tw:items-center tw:justify-center" title="{{ __('config_backups.base') }}">
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
                                                <span class="tw:text-[11px] tw:font-medium tw:text-gray-500 tw:dark:text-dark-white-400">({{ __('config_backups.latest') }})</span>
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
                                            class="lnms-btn lnms-btn-default tw:w-full tw:min-h-11 tw:rounded-none tw:border-0 tw:border-t tw:border-gray-200 tw:dark:border-dark-gray-200"
                                            x-text="loadingMore ? '{{ __('config_backups.loading') }}' : '{{ __('config_backups.load_more') }}'">
                                    </button>
                                </li>
                            </template>
                        </ul>
                    </x-slot>
                </x-panel>

                {{-- Config / diff pane --}}
                <x-panel class="tw:w-full tw:flex-1 tw:min-w-0 tw:overflow-hidden tw:self-start" x-ref="viewerPanel">
                    <x-slot name="heading" class="tw:flex tw:flex-wrap tw:items-center tw:justify-between tw:gap-2">
                        <h3 class="panel-title tw:flex tw:items-center tw:gap-2 tw:flex-wrap">
                            <template x-if="diffMode">
                                <div class="tw:flex tw:items-center tw:gap-2 tw:flex-wrap">
                                    <span class="tw:font-semibold">{{ __('config_backups.diff') }}:</span>
                                    <template x-if="activeDiffOrig && activeDiffRev">
                                        <div class="tw:inline-flex tw:items-center tw:gap-2 tw:flex-wrap">
                                            {{-- Base (Old) Version Dropdown --}}
                                            <div class="tw:inline-flex tw:items-center tw:gap-1.5">
                                                <span class="tw:w-5 tw:h-5 tw:rounded-full tw:bg-red-500 tw:text-white tw:text-[9px] tw:flex tw:items-center tw:justify-center tw:shrink-0" title="{{ __('config_backups.base') }}">
                                                    <i class="fa fa-minus" aria-hidden="true"></i>
                                                </span>
                                                <div class="tw:relative tw:inline-flex tw:items-center">
                                                    <select :value="activeOrigId"
                                                            x-on:change="onOrigDropdownChange($event.target.value)"
                                                            aria-label="{{ __('config_backups.base') }}"
                                                            class="tw:appearance-none tw:cursor-pointer tw:text-lg tw:font-medium tw:h-8 tw:py-0 tw:pl-2 tw:pr-7 tw:rounded-md tw:border tw:border-gray-300 tw:bg-white tw:text-gray-900 tw:dark:bg-dark-gray-400 tw:dark:text-dark-white-100 tw:dark:border-dark-gray-100 tw:focus:ring-2 tw:focus:ring-blue-500 tw:focus:outline-none">
                                                        <template x-for="b in textBackups" :key="'orig-' + b.id">
                                                            <option :value="b.id"
                                                                    :selected="b.id === activeOrigId"
                                                                    x-text="formatDate(b.date)"></option>
                                                        </template>
                                                    </select>
                                                    <i class="fa fa-chevron-down tw:absolute tw:right-2 tw:text-[9px] tw:text-gray-500 tw:dark:text-dark-white-300 tw:pointer-events-none" aria-hidden="true"></i>
                                                </div>
                                            </div>

                                            {{-- direction toggle button --}}
                                            <button type="button"
                                                    x-on:click="toggleDiffDirection()"
                                                    title="{{ __('config_backups.reverse_direction') }}"
                                                    class="lnms-btn lnms-btn-default tw:h-8 tw:w-8 tw:flex tw:text-sm tw:items-center tw:justify-center tw:rounded-full tw:transition-transform tw:shrink-0"
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
                                                            class="tw:appearance-none tw:cursor-pointer tw:text-lg tw:font-medium tw:h-8 tw:py-0 tw:pl-2 tw:pr-7 tw:rounded-md tw:border tw:border-gray-300 tw:bg-white tw:text-gray-900 tw:dark:bg-dark-gray-400 tw:dark:text-dark-white-100 tw:dark:border-dark-gray-100 tw:focus:ring-2 tw:focus:ring-blue-500 tw:focus:outline-none">
                                                        <template x-for="b in textBackups" :key="'rev-' + b.id">
                                                            <option :value="b.id"
                                                                    :selected="b.id === activeRevId"
                                                                    x-text="formatDate(b.date)"></option>
                                                        </template>
                                                    </select>
                                                    <i class="fa fa-chevron-down tw:absolute tw:right-2 tw:text-[9px] tw:text-gray-500 tw:dark:text-dark-white-300 tw:pointer-events-none" aria-hidden="true"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <template x-if="!diffMode">
                                <span class="tw:flex tw:items-center tw:gap-1.5">
                                    <span class="tw:font-semibold">{{ __('config_backups.configuration') }}: <span class="tw:font-medium" x-show="selectedDisplayDate" x-text="formatDate(selectedDisplayDate)"></span></span>
                                </span>
                            </template>
                            <i x-show="loading" x-cloak class="fa fa-spinner tw:animate-spin tw:text-blue-500 tw:text-sm" aria-hidden="true"></i>
                        </h3>

                        {{-- Action buttons --}}
                        <div class="tw:flex tw:items-center tw:gap-2" x-cloak>
                            {{-- Single Config Actions --}}
                            <template x-if="showConfigView">
                                <div class="tw:flex tw:items-center tw:gap-2">
                                    <button type="button"
                                            x-on:click="downloadConfig()"
                                            title="{{ __('config_backups.download') }}"
                                            class="lnms-btn lnms-btn-default tw:h-8 tw:min-w-8 tw:px-2 tw:flex tw:items-center tw:justify-center tw:gap-1.5 tw:text-sm tw:transition-colors tw:shrink-0"
                                            aria-label="{{ __('config_backups.download') }}">
                                        <i class="fa fa-download" aria-hidden="true"></i>
                                        <span class="tw:hidden tw:xl:inline">{{ __('config_backups.download') }}</span>
                                    </button>
                                    <button type="button"
                                            x-on:click="copyToClipboard()"
                                            :title="copied ? '{{ __('config_backups.copied') }}' : '{{ __('config_backups.copy') }}'"
                                            class="lnms-btn lnms-btn-default tw:h-8 tw:min-w-8 tw:px-2 tw:flex tw:items-center tw:justify-center tw:gap-1.5 tw:text-sm tw:transition-colors tw:shrink-0"
                                            aria-label="{{ __('config_backups.copy') }}">
                                        <i class="fa" :class="copied ? 'fa-check tw:text-green-400' : 'fa-copy'" aria-hidden="true"></i>
                                        <span class="tw:hidden tw:xl:inline" x-text="copied ? '{{ __('config_backups.copied') }}' : '{{ __('config_backups.copy') }}'"></span>
                                    </button>
                                </div>
                            </template>

                            {{-- Diff Actions --}}
                            <template x-if="showDiffView">
                                <div class="tw:flex tw:items-center tw:gap-2">
                                    <button type="button"
                                            x-on:click="downloadDiff()"
                                            title="{{ __('config_backups.download_diff') }}"
                                            class="lnms-btn lnms-btn-default tw:h-8 tw:min-w-8 tw:px-2 tw:flex tw:items-center tw:justify-center tw:gap-1.5 tw:text-sm tw:transition-colors tw:shrink-0"
                                            aria-label="{{ __('config_backups.download_diff') }}">
                                        <i class="fa fa-download" aria-hidden="true"></i>
                                        <span class="tw:hidden tw:xl:inline">{{ __('config_backups.download_diff') }}</span>
                                    </button>
                                    <button type="button"
                                            x-on:click="copyDiff()"
                                            :title="copiedDiff ? '{{ __('config_backups.copied_diff') }}' : '{{ __('config_backups.copy_diff') }}'"
                                            class="lnms-btn lnms-btn-default tw:h-8 tw:min-w-8 tw:px-2 tw:flex tw:items-center tw:justify-center tw:gap-1.5 tw:text-sm tw:transition-colors tw:shrink-0"
                                            aria-label="{{ __('config_backups.copy_diff') }}">
                                        <i class="fa" :class="copiedDiff ? 'fa-check tw:text-green-400' : 'fa-copy'" aria-hidden="true"></i>
                                        <span class="tw:hidden tw:xl:inline" x-text="copiedDiff ? '{{ __('config_backups.copied_diff') }}' : '{{ __('config_backups.copy_diff') }}'"></span>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </x-slot>

                    <x-slot name="table">
                        {{-- top activity line --}}
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
                            <pre class="config-highlight line-numbers tw:m-0 tw:p-3 tw:border-0 tw:rounded-none tw:font-mono tw:whitespace-pre-wrap tw:overflow-x-auto tw:bg-gray-50 tw:text-gray-800 tw:dark:bg-dark-gray-500 tw:dark:text-dark-white-200 tw:transition-opacity tw:duration-150"
                                 :class="{ 'tw:opacity-60': loading }"><code
                                    x-config-highlight="selected.content"
                                    data-os="{{ $data['os'] }}"
                                    data-config-highlighting="{{ $data['config_highlighting'] }}"></code></pre>
                        </template>
                    </x-slot>
                </x-panel>

                {{-- Help & Shortcuts Modal --}}
                <x-modal show="showHelp">
                    <x-slot name="heading">
                        <h4 class="tw:m-0 tw:text-base tw:font-semibold tw:flex tw:items-center tw:gap-2">
                            <i class="fa fa-keyboard-o tw:text-blue-500" aria-hidden="true"></i>
                            {{ __('config_backups.help') }}
                        </h4>
                    </x-slot>

                    {{-- Navigate History --}}
                    <div>
                        <h5 class="tw:font-semibold tw:uppercase tw:tracking-wider tw:text-gray-500 tw:dark:text-dark-white-400 tw:mb-2">
                            {{ __('config_backups.navigate_history') }}
                        </h5>
                        <div class="tw:grid tw:grid-cols-1 tw:gap-2">
                            <div class="tw:flex tw:items-center tw:justify-between tw:p-2 tw:rounded-md tw:bg-gray-50 tw:dark:bg-dark-gray-400">
                                <span class="tw:text-gray-600 tw:dark:text-dark-white-300">{{ __('config_backups.shortcut_older') }}</span>
                                <div class="tw:flex tw:items-center tw:gap-1.5">
                                    <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">j</kbd>
                                    <span class="tw:text-gray-400 tw:dark:text-dark-white-400">/</span>
                                    <div class="tw:flex tw:items-center tw:gap-0.5">
                                        <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs" x-text="modifierKey">Ctrl</kbd>
                                        <span class="tw:text-gray-400 tw:dark:text-dark-white-400">+</span>
                                        <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">↓</kbd>
                                    </div>
                                </div>
                            </div>
                            <div class="tw:flex tw:items-center tw:justify-between tw:p-2 tw:rounded-md tw:bg-gray-50 tw:dark:bg-dark-gray-400">
                                <span class="tw:text-gray-600 tw:dark:text-dark-white-300">{{ __('config_backups.shortcut_newer') }}</span>
                                <div class="tw:flex tw:items-center tw:gap-1.5">
                                    <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">k</kbd>
                                    <span class="tw:text-gray-400 tw:dark:text-dark-white-400">/</span>
                                    <div class="tw:flex tw:items-center tw:gap-0.5">
                                        <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs" x-text="modifierKey">Ctrl</kbd>
                                        <span class="tw:text-gray-400 tw:dark:text-dark-white-400">+</span>
                                        <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">↑</kbd>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Compare Revisions --}}
                    <div>
                        <h5 class="tw:font-semibold tw:uppercase tw:tracking-wider tw:text-gray-500 tw:dark:text-dark-white-400 tw:mb-2">
                            {{ __('config_backups.compare_revisions') }}
                        </h5>
                        <div class="tw:grid tw:grid-cols-1 tw:gap-2">
                            <div class="tw:flex tw:items-center tw:justify-between tw:p-2 tw:rounded-md tw:bg-gray-50 tw:dark:bg-dark-gray-400">
                                <span class="tw:text-gray-600 tw:dark:text-dark-white-300">{{ __('config_backups.shortcut_toggle_diff') }}</span>
                                <div class="tw:flex tw:gap-1">
                                    <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">d</kbd>
                                    <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">c</kbd>
                                </div>
                            </div>
                            <div class="tw:flex tw:items-center tw:justify-between tw:p-2 tw:rounded-md tw:bg-gray-50 tw:dark:bg-dark-gray-400">
                                <span class="tw:text-gray-600 tw:dark:text-dark-white-300">{{ __('config_backups.shortcut_shift_click') }}</span>
                                <div class="tw:flex tw:items-center tw:gap-1">
                                    <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">Shift</kbd>
                                    <span class="tw:text-gray-500 tw:dark:text-dark-white-300">+</span>
                                    <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">{{ __('config_backups.click') }}</kbd>
                                </div>
                            </div>
                            <div class="tw:flex tw:items-center tw:justify-between tw:p-2 tw:rounded-md tw:bg-gray-50 tw:dark:bg-dark-gray-400">
                                <span class="tw:text-gray-600 tw:dark:text-dark-white-300">{{ __('config_backups.shortcut_expand_older') }}</span>
                                <div class="tw:flex tw:items-center tw:gap-1">
                                    <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">Shift</kbd>
                                    <span class="tw:text-gray-500 tw:dark:text-dark-white-300">+</span>
                                    <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">j/↓</kbd>
                                </div>
                            </div>
                            <div class="tw:flex tw:items-center tw:justify-between tw:p-2 tw:rounded-md tw:bg-gray-50 tw:dark:bg-dark-gray-400">
                                <span class="tw:text-gray-600 tw:dark:text-dark-white-300">{{ __('config_backups.shortcut_expand_newer') }}</span>
                                <div class="tw:flex tw:items-center tw:gap-1">
                                    <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">Shift</kbd>
                                    <span class="tw:text-gray-500 tw:dark:text-dark-white-300">+</span>
                                    <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">k/↑</kbd>
                                </div>
                            </div>
                            <div class="tw:flex tw:items-center tw:justify-between tw:p-2 tw:rounded-md tw:bg-gray-50 tw:dark:bg-dark-gray-400">
                                <span class="tw:text-gray-600 tw:dark:text-dark-white-300">{{ __('config_backups.shortcut_reverse_diff') }}</span>
                                <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">r</kbd>
                            </div>
                        </div>
                    </div>

                    {{-- General --}}
                    <div>
                        <h5 class="tw:font-semibold tw:uppercase tw:tracking-wider tw:text-gray-500 tw:dark:text-dark-white-400 tw:mb-2">
                            {{ __('config_backups.general') }}
                        </h5>
                        <div class="tw:grid tw:grid-cols-1 tw:gap-2">
                            <div class="tw:flex tw:items-center tw:justify-between tw:p-2 tw:rounded-md tw:bg-gray-50 tw:dark:bg-dark-gray-400">
                                <span class="tw:text-gray-600 tw:dark:text-dark-white-300">{{ __('config_backups.shortcut_help') }}</span>
                                <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">?</kbd>
                            </div>
                            <div class="tw:flex tw:items-center tw:justify-between tw:p-2 tw:rounded-md tw:bg-gray-50 tw:dark:bg-dark-gray-400">
                                <span class="tw:text-gray-600 tw:dark:text-dark-white-300">{{ __('config_backups.shortcut_exit_diff') }}</span>
                                <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">Esc</kbd>
                            </div>
                        </div>
                    </div>
                </x-modal>
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
                            if (currentUpdateId === updateId) highlightConfig(element, content);
                        });
                    });
                });
            });

            window.Alpine.data('configBackups', (config) => ({
                backups: [],
                page: 0,
                totalPages: 0,
                total: 0,
                urls: config.urls || {},
                messages: config.messages || {},
                canRefresh: config.can_refresh || false,

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
                refreshing: false,

                diffMode: false,
                diffSelection: [],
                diffGroups: null,
                diffReversed: false,

                async init() {
                    await this.loadLatest();
                    this.loadBackupPage(0);
                },

                beginLoading() {
                    this.loading = true;
                    this.showSpinner = false;
                    return setTimeout(() => { if (this.loading) this.showSpinner = true; }, 300);
                },

                endLoading(timer) {
                    clearTimeout(timer);
                    this.loading = false;
                    this.showSpinner = false;
                },

                indexOfId(id) {
                    return this.backups.findIndex(b => b.id === id);
                },

                get isMac() {
                    return typeof navigator !== 'undefined' && (/Mac|iPod|iPhone|iPad/.test(navigator.userAgentData?.platform || navigator.platform || navigator.userAgent || ''));
                },

                get modifierKey() {
                    return this.isMac ? '⌘' : 'Ctrl';
                },

                get currentSingleIndex() {
                    const idx = this.indexOfId(this.selectedBackupId ?? this.selected?.id);
                    return idx !== -1 ? idx : 0;
                },

                get textBackups() {
                    return this.backups.filter(b => b.type === 'TEXT');
                },

                scrollToIndex(index) {
                    this.$refs.timelineList
                        ?.querySelector(`[data-backup-index="${index}"]`)
                        ?.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                },

                scrollToTop() {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    this.$refs.viewerPanel
                        ?.querySelector('pre, .tw\\:overflow-x-auto')
                        ?.scrollTo({ top: 0, behavior: 'smooth' });
                },

                async loadBackupPage(page, append = false) {
                    const loadingKey = append ? 'loadingMore' : 'loadingBackups';
                    this[loadingKey] = true;
                    try {
                        const { data } = await window.axios.get(this.urls.backups, { params: { page } });
                        const mapped = data.backups.map(b => ({ ...b, page }));
                        append ? this.backups.push(...mapped) : this.backups = mapped;
                        this.page = data.page;
                        this.totalPages = data.totalPages;
                        this.total = data.total;
                    } catch (e) {
                        if (!this.error) this.error = this.requestError(e);
                    } finally {
                        this[loadingKey] = false;
                    }
                },

                loadMore() { this.loadBackupPage(this.page + 1, true); },

                get hasMore() { return this.page < this.totalPages - 1; },

                async loadLatest() {
                    const timer = this.beginLoading();
                    this.error = null;
                    try {
                        const { data } = await window.axios.get(this.urls.backup);
                        if (!this.selected) {
                            this.selected = data;
                            this.selectedBackupId = data.id;
                        }
                    } catch (e) {
                        if (!this.selected) {
                            this.error = this.requestError(e);
                            this.selected = null;
                        }
                    } finally {
                        this.endLoading(timer);
                    }
                },

                async loadBackupContent(backup) {
                    const timer = this.beginLoading();
                    this.error = null;
                    try {
                        const { data } = await window.axios.get(this.urls.backup, {
                            params: { backup: backup.id, page: backup.page },
                        });
                        if (this.selectedBackupId === backup.id) {
                            this.selected = { ...backup, content: data.content };
                        }
                    } catch (e) {
                        if (this.selectedBackupId === backup.id) {
                            this.error = this.requestError(e);
                            this.selected = null;
                        }
                    } finally {
                        this.endLoading(timer);
                    }
                },

                applyDiffRange(anchor, focus) {
                    const [minIdx, maxIdx] = [Math.min(anchor, focus), Math.max(anchor, focus)];
                    const older = this.backups[maxIdx];
                    const newer = this.backups[minIdx];
                    if (!older || !newer || older.type !== 'TEXT' || newer.type !== 'TEXT') return;

                    if (this.diffMode && this.diffSelection.length === 2 &&
                        this.diffSelection[0].id === older.id && this.diffSelection[1].id === newer.id && !this.diffReversed) {
                        this.scrollToTop();
                        return;
                    }

                    this.anchorIndex = anchor;
                    this.focusIndex = focus;
                    this.diffMode = true;
                    this.diffReversed = false;
                    this.diffSelection = [older, newer];
                    this.loadDiff();
                },

                selectBackup(backup, index, event = null) {
                    if (backup.type !== 'TEXT') {
                        if (!this.diffMode) {
                            this.selected = { ...backup, content: null };
                            this.selectedBackupId = backup.id;
                            this.anchorIndex = index;
                            this.focusIndex = index;
                        }
                        return;
                    }

                    if (event?.shiftKey) {
                        const curAnchor = this.anchorIndex ?? this.indexOfId(this.selectedBackupId ?? this.selected?.id ?? this.activeDiffRev?.id);
                        this.applyDiffRange(curAnchor !== -1 ? curAnchor : 0, index);
                        return;
                    }

                    if (this.diffMode) {
                        const adjIdx = this.findAdjacentTextIndex(index);
                        if (adjIdx !== null) this.applyDiffRange(index, adjIdx);
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

                findAdjacentTextIndex(index) {
                    if (index + 1 < this.backups.length && this.backups[index + 1].type === 'TEXT') return index + 1;
                    if (index - 1 >= 0 && this.backups[index - 1].type === 'TEXT') return index - 1;
                    return null;
                },

                navigateHistory(step, isShift = false) {
                    if (!this.backups.length) return;

                    if (this.diffMode && isShift) {
                        const curAnchor = this.anchorIndex ?? (this.activeDiffRev ? this.indexOfId(this.activeDiffRev.id) : 0);
                        const curFocus = this.focusIndex ?? (this.activeDiffOrig ? this.indexOfId(this.activeDiffOrig.id) : curAnchor + 1);
                        let nextFocus = curFocus + step;
                        if (nextFocus === curAnchor) nextFocus += step;
                        if (nextFocus < 0 || nextFocus >= this.backups.length) return;
                        this.applyDiffRange(curAnchor, nextFocus);
                        this.scrollToIndex(nextFocus);
                        return;
                    }

                    if (this.diffMode) {
                        const currentAnchor = this.anchorIndex ?? (this.activeDiffRev ? this.indexOfId(this.activeDiffRev.id) : 0);
                        const nextAnchor = currentAnchor + step;
                        if (nextAnchor < 0 || nextAnchor >= this.backups.length) return;
                        this.selectBackup(this.backups[nextAnchor], nextAnchor);
                        this.scrollToIndex(nextAnchor);
                        return;
                    }

                    const currentIdx = this.currentSingleIndex;
                    const nextIndex = currentIdx + step;
                    if (nextIndex < 0 || nextIndex >= this.backups.length) return;

                    if (isShift) {
                        this.applyDiffRange(currentIdx, nextIndex);
                        this.scrollToIndex(nextIndex);
                        return;
                    }

                    this.selectBackup(this.backups[nextIndex], nextIndex);
                    this.scrollToIndex(nextIndex);
                },

                handleKeyDown(e) {
                    if (e.altKey || e.target.closest('input, textarea, select')) return;

                    const key = e.key;
                    const ctrl = e.ctrlKey || e.metaKey;
                    const shift = e.shiftKey;

                    if (!ctrl && (key === '?' || (key === '/' && shift))) {
                        e.preventDefault();
                        this.showHelp = !this.showHelp;
                    } else if (key === 'Escape') {
                        if (this.showHelp) { e.preventDefault(); this.showHelp = false; }
                        else if (this.diffMode) { e.preventDefault(); this.toggleDiffMode(); }
                    } else if (!ctrl && (key === 'd' || key === 'c') && this.total > 1) {
                        e.preventDefault();
                        this.toggleDiffMode();
                    } else if (!ctrl && key === 'r' && this.diffMode && this.diffReady) {
                        e.preventDefault();
                        this.toggleDiffDirection();
                    } else if ((!ctrl && (key === 'j' || key === 'J')) || (ctrl && key === 'ArrowDown') || (shift && key === 'ArrowDown')) {
                        e.preventDefault();
                        this.navigateHistory(1, shift);
                    } else if ((!ctrl && (key === 'k' || key === 'K')) || (ctrl && key === 'ArrowUp') || (shift && key === 'ArrowUp')) {
                        e.preventDefault();
                        this.navigateHistory(-1, shift);
                    }
                },

                toggleDiffMode() {
                    this.diffMode = !this.diffMode;
                    this.error = null;
                    this.diffReversed = false;
                    this.diffMode ? this.enterDiffMode() : this.exitDiffMode();
                },

                enterDiffMode() {
                    const text = this.textBackups;
                    const curId = this.selectedBackupId ?? this.selected?.id;
                    const selectedIdx = text.findIndex(b => b.id === curId);

                    if (selectedIdx !== -1) {
                        const pair = text[selectedIdx + 1] ?? text[selectedIdx - 1];
                        this.diffSelection = pair ? [text[selectedIdx], pair] : [text[selectedIdx]];
                    } else {
                        this.diffSelection = text.slice(0, 2);
                    }

                    this.anchorIndex = this.diffSelection[0] ? this.indexOfId(this.diffSelection[0].id) : 0;
                    this.focusIndex = this.diffSelection[1] ? this.indexOfId(this.diffSelection[1].id) : this.anchorIndex;

                    this.diffSelection.length === 2 ? this.loadDiff() : this.diffGroups = null;
                },

                exitDiffMode() {
                    if (this.diffSelection[0]) {
                        const target = this.diffSelection[0];
                        this.selectedBackupId = target.id;
                        const idx = this.indexOfId(target.id);
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

                setDiffEndpoint(role, selectedId) {
                    const backup = this.backups.find(b => String(b.id) === String(selectedId));
                    const currentOther = role === 'orig' ? this.activeDiffRev : this.activeDiffOrig;
                    if (!backup || backup.type !== 'TEXT' || !currentOther) return;

                    const other = String(backup.id) === String(currentOther.id)
                        ? this.textBackups.find(b => String(b.id) !== String(selectedId))
                        : currentOther;
                    if (!other) return;

                    const [b1, b2] = role === 'orig' ? [backup, other] : [other, backup];
                    this.applyDiffRange(this.indexOfId(b1.id), this.indexOfId(b2.id));
                },

                onOrigDropdownChange(id) { this.setDiffEndpoint('orig', id); },
                onRevDropdownChange(id) { this.setDiffEndpoint('rev', id); },

                get sortedDiff() {
                    if (this.diffSelection.length !== 2) return null;
                    const [b1, b2] = this.diffSelection;
                    const [older, newer] = b1.date <= b2.date ? [b1, b2] : [b2, b1];
                    return this.diffReversed ? { orig: newer, rev: older } : { orig: older, rev: newer };
                },

                get activeDiffOrig() { return this.sortedDiff?.orig ?? null; },
                get activeDiffRev() { return this.sortedDiff?.rev ?? null; },
                get activeOrigId() { return this.activeDiffOrig?.id ?? ''; },
                get activeRevId() { return this.activeDiffRev?.id ?? ''; },
                get diffReady() { return this.diffGroups !== null && this.diffSelection.length === 2; },
                get hasDiffChanges() { return this.diffGroups?.some(g => g.type !== 'COMMON') ?? false; },

                get diffStats() {
                    if (!this.diffGroups) return null;
                    let additions = 0, deletions = 0;
                    for (const g of this.diffGroups) {
                        if (g.type === 'INSERTED') additions += g.revised.length;
                        else if (g.type === 'DELETED') deletions += g.original.length;
                        else if (g.type === 'CHANGED') { deletions += g.original.length; additions += g.revised.length; }
                    }
                    return { additions, deletions };
                },

                get diffRangeSummaryText() {
                    const range = this.selectedRangeIndices;
                    if (!range) return '{{ __('config_backups.revision_step') }}';
                    const count = range[1] - range[0] + 1;
                    return count <= 2
                        ? '{{ __('config_backups.revision_step') }}'
                        : '{{ __('config_backups.revisions_spanned', ['count' => '__COUNT__']) }}'.replace('__COUNT__', count);
                },

                get diffRoleMap() {
                    if (!this.diffMode || !this.sortedDiff) return {};
                    const { orig, rev } = this.sortedDiff;
                    return { [orig.id]: 'old', [rev.id]: 'new' };
                },

                async loadDiff() {
                    if (!this.sortedDiff) return;
                    const { orig, rev } = this.sortedDiff;
                    if (orig.id === rev.id) { this.diffGroups = []; return; }

                    const timer = this.beginLoading();
                    this.error = null;
                    try {
                        const { data } = await window.axios.get(this.urls.diff, { params: { orig: orig.id, rev: rev.id } });
                        this.diffGroups = data.groups;
                    } catch (e) {
                        this.error = this.requestError(e);
                        this.diffGroups = null;
                    } finally {
                        this.endLoading(timer);
                    }
                },

                get diffRows() {
                    if (!this.diffGroups) return [];
                    const rows = [];
                    const push = (mode, lines) => lines.forEach(l => rows.push({ mode, line: l.line, text: l.text }));
                    for (const g of this.diffGroups) {
                        if (g.type === 'COMMON') push('common', g.original);
                        if (g.type === 'DELETED' || g.type === 'CHANGED') push('removed', g.original);
                        if (g.type === 'INSERTED' || g.type === 'CHANGED') push('added', g.revised);
                    }
                    return rows;
                },

                getDiffRole(backup) { return this.diffRoleMap[backup.id] ?? null; },

                get selectedRangeIndices() {
                    if (this.diffMode && this.diffSelection.length === 2) {
                        const idx1 = this.indexOfId(this.diffSelection[0].id);
                        const idx2 = this.indexOfId(this.diffSelection[1].id);
                        if (idx1 !== -1 && idx2 !== -1) return [Math.min(idx1, idx2), Math.max(idx1, idx2)];
                    }
                    return null;
                },

                isIndexInRange(index) {
                    const range = this.selectedRangeIndices;
                    return range && index >= range[0] && index <= range[1];
                },

                getRowRangeClass(backup, index) {
                    if (this.isIndexInRange(index)) return 'tw:bg-blue-50/70 tw:dark:bg-dark-gray-300';
                    if (!this.diffMode && this.isSelected(backup)) return 'tw:bg-gray-100 tw:dark:bg-dark-gray-300';
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

                get showDiffView() { return this.diffMode && this.diffReady; },
                get showDiffPrompt() { return !this.showSpinner && this.diffMode && !this.diffReady && !this.error; },
                get showBinaryNotice() { return !this.showSpinner && !this.diffMode && this.selected && this.selected.type !== 'TEXT'; },
                get showConfigView() { return !this.diffMode && this.selected?.content != null && this.selected?.type === 'TEXT'; },

                isSelected(backup) {
                    if (this.diffMode) {
                        return this.diffSelection.some(b => b.id === backup.id);
                    }
                    return (this.selectedBackupId ?? this.selected?.id) === backup.id;
                },

                get selectedDisplayDate() {
                    if (this.selectedBackupId) {
                        const b = this.backups.find(b => b.id === this.selectedBackupId);
                        if (b) return b.date;
                    }
                    return this.selected?.date;
                },

                errorMessage() { return this.messages[this.error] || this.messages.request_failed || this.error; },
                formatDate(ts) { return ts ? window.LibreNMS.Date.display(ts) : ''; },
                requestError(e) { return e.response?.data?.error ?? 'request_failed'; },

                downloadFile(content, filename, mimeType) {
                    const blob = new Blob([content], { type: `${mimeType};charset=utf-8` });
                    const url = URL.createObjectURL(blob);
                    Object.assign(document.createElement('a'), { href: url, download: filename }).click();
                    URL.revokeObjectURL(url);
                },

                copyText(text, successKey) {
                    navigator.clipboard.writeText(text).then(() => {
                        this[successKey] = true;
                        setTimeout(() => { this[successKey] = false; }, 2000);
                    }).catch(err => console.error('Clipboard copy failed:', err));
                },

                generateUnifiedDiff() {
                    if (!this.diffGroups || !this.activeDiffOrig || !this.activeDiffRev) return '';
                    let text = `--- Base (${this.formatDate(this.activeDiffOrig.date)})\n+++ Compare (${this.formatDate(this.activeDiffRev.date)})\n`;
                    for (const g of this.diffGroups) {
                        if (g.type === 'COMMON') g.original.forEach(l => { text += `  ${l.text}\n`; });
                        if (g.type === 'DELETED' || g.type === 'CHANGED') g.original.forEach(l => { text += `-${l.text}\n`; });
                        if (g.type === 'INSERTED' || g.type === 'CHANGED') g.revised.forEach(l => { text += `+${l.text}\n`; });
                    }
                    return text;
                },

                downloadDiff() {
                    const text = this.generateUnifiedDiff();
                    if (!text) return;
                    const dateStr = (ts) => ts ? new Date(ts * 1000).toISOString().split('T')[0] : 'unknown';
                    const prefix = config.hostname ? `${config.hostname}-` : '';
                    this.downloadFile(text, `${prefix}config-diff-${dateStr(this.activeDiffOrig?.date)}-to-${dateStr(this.activeDiffRev?.date)}.diff`, 'text/x-diff');
                },

                copyDiff() {
                    const text = this.generateUnifiedDiff();
                    if (text) this.copyText(text, 'copiedDiff');
                },

                downloadConfig() {
                    if (!this.selected?.content) return;
                    const dateStr = this.selected?.date ? new Date(this.selected.date * 1000).toISOString().split('T')[0] : 'latest';
                    const prefix = config.hostname ? `${config.hostname}-` : '';
                    this.downloadFile(this.selected.content, `${prefix}config-${dateStr}.txt`, 'text/plain');
                },

                copyToClipboard() {
                    if (this.selected?.content) this.copyText(this.selected.content, 'copied');
                },

                refresh() {
                    if (this.refreshing) return;
                    this.refreshing = true;
                    window.axios.post(this.urls.refresh)
                        .then(({ data }) => window.toastr?.success(data.message))
                        .catch(err => window.toastr?.error(err.response?.data?.message || this.messages.request_failed))
                        .finally(() => { this.refreshing = false; });
                },
            }));
        });
    </script>
@endpush
