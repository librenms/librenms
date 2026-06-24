@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
        @if($data['error'])
            <div class="tw:mt-4 tw:rounded-xl tw:border tw:border-gray-300 tw:dark:border-dark-gray-200 tw:bg-white tw:dark:bg-dark-gray-400 tw:p-6 tw:text-gray-700 tw:dark:text-dark-white-300">
                <h3 class="tw:font-semibold tw:text-lg tw:mb-2 tw:text-gray-800 tw:dark:text-dark-white-100">{{ __('Device Configuration') }}</h3>
                {{ $data['error_message'] }}
            </div>
        @else
            <div x-data="configBackups({
                    backups: {{ Js::from($data['backups']) }},
                    latest: {{ Js::from($data['latest']) }},
                    total: {{ Js::from($data['total']) }},
                    totalPages: {{ Js::from($data['totalPages']) }},
                    urls: {
                        backups: @js(route('device.config.backups', $device->device_id)),
                        backup: @js(route('device.config.backup', [$device->device_id, 'BACKUP_ID'])),
                        diff: @js(route('device.config.diff', $device->device_id)),
                    },
                    messages: {
                        unreachable: @js(__(':provider is not reachable.', ['provider' => $data['provider']])),
                        error: @js(__(':provider returned an error.', ['provider' => $data['provider']])),
                        backup_not_found: @js(__('This backup could not be loaded from :provider.', ['provider' => $data['provider']])),
                        request_failed: @js(__('The request failed. Please try again.')),
                    },
                })"
                class="tw:mt-4 tw:grid tw:grid-cols-1 tw:lg:grid-cols-4 tw:gap-4">

                {{-- Backup list --}}
                <div class="tw:rounded-xl tw:border tw:border-gray-300 tw:dark:border-dark-gray-200 tw:bg-white tw:dark:bg-dark-gray-400 tw:overflow-hidden tw:self-start">
                    <div class="tw:flex tw:items-center tw:justify-between tw:px-4 tw:py-3 tw:border-b tw:border-gray-200 tw:dark:border-dark-gray-200">
                        <h3 class="tw:text-gray-800 tw:dark:text-dark-white-100">
                            {{ __('Backups') }}
                            <span class="tw:font-normal tw:text-xl tw:text-gray-500 tw:dark:text-dark-white-400" x-text="'(' + total + ')'"></span>
                        </h3>
                        <button type="button"
                                x-on:click="toggleDiffMode()"
                                x-text="diffMode ? '{{ __('Cancel') }}' : '{{ __('Diff') }}'"
                                :class="diffMode
                                    ? 'lnms-btn-default'
                                    : 'lnms-btn-primary'"
                                class="lnms-btn tw:transition-colors">
                        </button>
                    </div>

                    <p class="tw:px-4 tw:py-2 tw:m-0 tw:text-gray-500 tw:dark:text-dark-white-400 tw:border-b tw:border-gray-200 tw:dark:border-dark-gray-200"
                       x-show="diffMode" x-cloak>
                        {{ __('Select two backups to compare.') }}
                    </p>

                    <ul class="tw:list-none tw:m-0 tw:p-0 tw:divide-y tw:divide-gray-200 tw:dark:divide-dark-gray-200 tw:max-h-[70vh] tw:overflow-y-auto">
                        <template x-for="backup in backups" :key="backup.id">
                            <li>
                                <button type="button"
                                        x-on:click="selectBackup(backup)"
                                        :disabled="backup.type !== 'TEXT' && diffMode"
                                        :class="isSelected(backup)
                                            ? 'tw:bg-blue-50 tw:dark:bg-blue-900/40'
                                            : 'tw:hover:bg-gray-50 tw:dark:hover:bg-dark-gray-300'"
                                        class="tw:w-full tw:text-left tw:px-4 tw:py-2.5 tw:flex tw:items-center tw:gap-2 tw:transition-colors tw:disabled:opacity-50 tw:disabled:cursor-not-allowed">
                                    <span x-show="diffMode" x-cloak
                                          :class="isSelected(backup) ? 'tw:bg-blue-600 tw:border-blue-600' : 'tw:border-gray-400 tw:dark:border-dark-gray-100'"
                                          class="tw:inline-block tw:w-4 tw:h-4 tw:shrink-0 tw:rounded tw:border-2"></span>
                                    <span class="tw:flex-1">
                                        <span class="tw:block tw:text-base tw:text-gray-800 tw:dark:text-dark-white-100" x-text="formatDate(backup.date)"></span>
                                        <span class="tw:block tw:text-base tw:text-gray-500 tw:dark:text-dark-white-400"
                                              x-show="backup.until">{{ __('Valid until') }}<span x-show="backup.until" x-text="' ' + formatDate(backup.until)"></span></span>
                                    </span>
                                    <span x-show="backup.type !== 'TEXT'"
                                          class="tw:text-xs tw:font-medium tw:rounded tw:px-1.5 tw:py-0.5 tw:bg-gray-200 tw:text-gray-700 tw:dark:bg-dark-gray-200 tw:dark:text-dark-white-300"
                                          x-text="backup.type"></span>
                                </button>
                            </li>
                        </template>
                    </ul>

                    <div class="tw:p-3 tw:border-t tw:border-gray-200 tw:dark:border-dark-gray-200" x-show="hasMore" x-cloak>
                        <button type="button"
                                x-on:click="loadMore()"
                                :disabled="loadingMore"
                                class="lnms-btn lnms-btn-default tw:w-full"
                                x-text="loadingMore ? '{{ __('Loading...') }}' : '{{ __('Load more') }}'">
                        </button>
                    </div>
                </div>

                {{-- Config / diff pane --}}
                <div class="tw:lg:col-span-3 tw:rounded-xl tw:border tw:border-gray-300 tw:dark:border-dark-gray-200 tw:bg-white tw:dark:bg-dark-gray-400 tw:overflow-hidden tw:self-start">
                    <div class="tw:px-4 tw:py-3 tw:border-b tw:border-gray-200 tw:dark:border-dark-gray-200">
                        <h3 class="tw:text-gray-800 tw:dark:text-dark-white-100">
                            <span x-show="diffMode">{{ __('Diff') }}<span
                                    x-show="diffSelection.length === 2"
                                    x-text="': ' + formatDate(Math.min(diffSelection[0]?.date, diffSelection[1]?.date) ) + ' → ' + formatDate(Math.max(diffSelection[0]?.date, diffSelection[1]?.date))"></span></span>
                            <span x-show="!diffMode">{{ __('Configuration') }}<span
                                    x-show="selected"
                                    x-text="' - ' + formatDate(selected?.date)"></span></span>
                        </h3>
                    </div>

                    <div class="tw:p-4">
                        {{-- error --}}
                        <div x-show="error" x-cloak
                             class="tw:mb-3 tw:rounded-lg tw:border tw:border-red-300 tw:bg-red-50 tw:text-red-800 tw:dark:border-red-900 tw:dark:bg-red-900/30 tw:dark:text-red-300 tw:px-4 tw:py-3 tw:text-sm"
                             x-text="errorMessage()"></div>

                        {{-- loading --}}
                        <div x-show="loading" x-cloak class="tw:py-10 tw:text-center tw:text-gray-500 tw:dark:text-dark-white-400">
                            <i class="fa fa-spinner fa-spin fa-2x"></i>
                        </div>

                        {{-- diff view --}}
                        <template x-if="!loading && diffMode && diffReady">
                            <div class="tw:rounded-lg tw:overflow-hidden tw:border tw:border-gray-200 tw:dark:border-dark-gray-200">
                                <table class="tw:w-full tw:m-0 tw:font-mono tw:border-collapse">
                                    <tbody>
                                        <template x-for="(row, index) in diffRows" :key="index">
                                            <tr :class="{
                                                    'tw:bg-green-100 tw:dark:bg-green-900/40': row.mode === 'added',
                                                    'tw:bg-red-100 tw:dark:bg-red-900/40': row.mode === 'removed',
                                                }">
                                                <td class="tw:w-12 tw:px-2 tw:py-0.5 tw:text-right tw:select-none tw:text-gray-400 tw:dark:text-dark-white-400 tw:border-r tw:border-gray-200 tw:dark:border-dark-gray-200"
                                                    x-text="row.line ?? ''"></td>
                                                <td class="tw:w-6 tw:px-1 tw:py-0.5 tw:text-center tw:select-none"
                                                    :class="{
                                                        'tw:text-green-700 tw:dark:text-green-400': row.mode === 'added',
                                                        'tw:text-red-700 tw:dark:text-red-400': row.mode === 'removed',
                                                    }"
                                                    x-text="row.mode === 'added' ? '+' : (row.mode === 'removed' ? '-' : '')"></td>
                                                <td class="tw:px-2 tw:py-0.5 tw:whitespace-pre tw:text-gray-800 tw:dark:text-dark-white-100" x-text="row.text"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </template>

                        {{-- waiting for diff selection --}}
                        <p x-show="!loading && diffMode && !diffReady && !error" x-cloak
                           class="tw:py-10 tw:m-0 tw:text-center tw:text-gray-500 tw:dark:text-dark-white-400">
                            {{ __('Select two backups from the list to view their differences.') }}
                        </p>

                        {{-- binary backup notice --}}
                        <p x-show="!loading && !diffMode && selected && selected.type !== 'TEXT'" x-cloak
                           class="tw:py-10 tw:m-0 tw:text-center tw:text-gray-500 tw:dark:text-dark-white-400">
                            {{ __('This is a binary backup and cannot be displayed. View it in :provider instead.', ['provider' => $data['provider']]) }}
                        </p>

                        {{-- config view --}}
                        <template x-if="!loading && !diffMode && content !== null && (!selected || selected.type === 'TEXT')">
                            <pre class="tw:m-0 tw:p-3 tw:font-mono tw:whitespace-pre tw:overflow-x-auto tw:max-h-[70vh] tw:overflow-y-auto tw:rounded-lg tw:bg-gray-50 tw:text-gray-800 tw:dark:bg-dark-gray-500! tw:dark:text-dark-white-200! tw:border tw:border-gray-200 tw:dark:border-dark-gray-200"
                                 x-text="content"></pre>
                        </template>
                    </div>
                </div>
            </div>
        @endif
    </x-device.page>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            window.Alpine.data('configBackups', (config) => ({
                backups: (config.backups || []).map((b) => ({ ...b, page: 0 })),
                page: 0,
                totalPages: config.totalPages || 0,
                total: config.total || 0,
                urls: config.urls || {},
                messages: config.messages || {},

                selected: config.latest || null,
                content: config.latest ? config.latest.content : null,
                loading: false,
                loadingMore: false,
                error: null,

                diffMode: false,
                diffSelection: [],
                diffGroups: null,

                get hasMore() {
                    return this.page < this.totalPages - 1;
                },

                get diffReady() {
                    return this.diffGroups !== null && this.diffSelection.length === 2;
                },

                errorMessage() {
                    return this.messages[this.error] || this.messages.request_failed || this.error;
                },

                formatDate(ts) {
                    return ts ? window.LibreNMS.Date.display(ts) : '';
                },

                isSelected(backup) {
                    if (this.diffMode) {
                        return this.diffSelection.some((b) => b.id === backup.id);
                    }

                    return this.selected && this.selected.id === backup.id;
                },

                selectBackup(backup) {
                    if (this.diffMode) {
                        this.toggleDiffSelect(backup);
                        return;
                    }

                    this.selected = backup;
                    this.error = null;

                    if (backup.type !== 'TEXT') {
                        this.content = null;
                        return;
                    }

                    this.loading = true;
                    window.axios
                        .get(this.urls.backup.replace('BACKUP_ID', encodeURIComponent(backup.id)), { params: { page: backup.page } })
                        .then((response) => {
                            this.content = response.data.content;
                        })
                        .catch((error) => {
                            this.content = null;
                            this.error = error.response?.data?.error || 'request_failed';
                        })
                        .finally(() => {
                            this.loading = false;
                        });
                },

                loadMore() {
                    this.loadingMore = true;
                    const nextPage = this.page + 1;

                    window.axios
                        .get(this.urls.backups, { params: { page: nextPage } })
                        .then((response) => {
                            this.backups.push(...response.data.backups.map((b) => ({ ...b, page: nextPage })));
                            this.page = response.data.page;
                            this.totalPages = response.data.totalPages;
                            this.total = response.data.total;
                        })
                        .catch((error) => {
                            this.error = error.response?.data?.error || 'request_failed';
                        })
                        .finally(() => {
                            this.loadingMore = false;
                        });
                },

                toggleDiffMode() {
                    this.diffMode = !this.diffMode;
                    this.diffSelection = [];
                    this.diffGroups = null;
                    this.error = null;
                },

                toggleDiffSelect(backup) {
                    if (backup.type !== 'TEXT') {
                        return;
                    }

                    const index = this.diffSelection.findIndex((b) => b.id === backup.id);
                    if (index >= 0) {
                        this.diffSelection.splice(index, 1);
                        this.diffGroups = null;
                    } else {
                        if (this.diffSelection.length >= 2) {
                            this.diffSelection.pop();
                        }
                        this.diffSelection.push(backup);
                    }

                    if (this.diffSelection.length === 2) {
                        this.loadDiff();
                    }
                },

                loadDiff() {
                    // oldest as the original, newest as the revision
                    const [orig, rev] = [...this.diffSelection].sort((a, b) => a.date - b.date);

                    this.loading = true;
                    this.error = null;
                    this.diffGroups = null;

                    window.axios
                        .get(this.urls.diff, { params: { orig: orig.id, rev: rev.id } })
                        .then((response) => {
                            this.diffGroups = response.data.groups;
                        })
                        .catch((error) => {
                            this.error = error.response?.data?.error || 'request_failed';
                        })
                        .finally(() => {
                            this.loading = false;
                        });
                },

                get diffRows() {
                    if (!this.diffGroups) {
                        return [];
                    }

                    const rows = [];
                    this.diffGroups.forEach((group) => {
                        if (group.type === 'COMMON') {
                            group.original.forEach((line) => rows.push({ mode: 'common', line: line.line, text: line.text }));
                            return;
                        }
                        if (group.type === 'DELETED' || group.type === 'CHANGED') {
                            group.original.forEach((line) => rows.push({ mode: 'removed', line: line.line, text: line.text }));
                        }
                        if (group.type === 'INSERTED' || group.type === 'CHANGED') {
                            group.revised.forEach((line) => rows.push({ mode: 'added', line: line.line, text: line.text }));
                        }
                    });

                    return rows;
                },
            }));
        });
    </script>
@endpush
