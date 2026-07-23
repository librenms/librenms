@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
        @if($data['error'])
            <x-panel class="tw:mt-4" title="{{ __('Device Configuration') }}">
                {{ $data['error_message'] }}
            </x-panel>
        @else
            <div x-data="configBackups(@js($data))"
                class="tw:mt-4 tw:flex tw:flex-col tw:lg:flex-row tw:gap-4 tw:items-start">

                {{-- Backup list --}}
                <x-panel class="tw:w-full tw:lg:w-md tw:lg:shrink-0 tw:overflow-hidden tw:self-start tw:mb-0!">
                    <x-slot name="heading" class="tw:flex tw:items-center tw:justify-between">
                        <h3 class="panel-title">
                            {{ __('config_backups.backups') }}
                            <span x-show="!loadingBackups" x-cloak class="tw:font-normal tw:text-xl tw:text-gray-500 tw:dark:text-dark-white-400" x-text="'(' + total + ')'"></span>
                        </h3>
                        <button type="button"
                                x-show="total > 1" x-cloak
                                x-on:click="toggleDiffMode()"
                                x-text="diffMode ? '{{ __('config_backups.show_config') }}' : '{{ __('config_backups.show_diff') }}'"
                                class="lnms-btn lnms-btn-primary tw:transition-colors">
                        </button>
                    </x-slot>

                    <x-slot name="table">
                        <p class="tw:px-4 tw:py-2 tw:m-0 tw:text-gray-500 tw:dark:text-dark-white-400 tw:border-b tw:border-gray-200 tw:dark:border-dark-gray-200"
                           x-show="diffMode" x-cloak>
                            {{ __('config_backups.select_two_to_compare') }}
                        </p>

                        <div x-show="loadingBackups" x-cloak class="tw:py-6 tw:text-center tw:text-gray-500 tw:dark:text-dark-white-400">
                            <i class="fa fa-spinner tw:animate-spin"></i>
                        </div>

                        <ul x-show="!loadingBackups" class="tw:list-none tw:m-0 tw:p-0 tw:divide-y tw:divide-gray-200 tw:dark:divide-dark-gray-200 tw:max-h-60 tw:lg:max-h-[70vh] tw:overflow-y-auto">
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
                                                  x-show="backup.until">{{ __('config_backups.valid_until') }}<span x-show="backup.until" x-text="' ' + formatDate(backup.until)"></span></span>
                                        </span>
                                        <span x-show="diffMode && getDiffRole(backup)" x-cloak
                                              :class="getDiffRole(backup) === 'old'
                                                  ? 'tw:bg-red-100 tw:text-red-800 tw:dark:bg-red-900/40 tw:dark:text-red-300'
                                                  : 'tw:bg-green-100 tw:text-green-800 tw:dark:bg-green-900/40 tw:dark:text-green-300'"
                                              class="tw:text-xs tw:font-medium tw:rounded tw:px-1.5 tw:py-0.5"
                                              x-text="getDiffRole(backup) === 'old' ? '{{ __('config_backups.old') }}' : '{{ __('config_backups.new') }}'"></span>
                                        <span x-show="backup.type !== 'TEXT'"
                                              class="tw:text-xs tw:font-medium tw:rounded tw:px-1.5 tw:py-0.5 tw:bg-gray-200 tw:text-gray-700 tw:dark:bg-dark-gray-200 tw:dark:text-dark-white-300"
                                              x-text="backup.type"></span>
                                    </button>
                                </li>
                            </template>
                        </ul>
                    </x-slot>

                    <x-slot name="footer" x-show="hasMore" x-cloak>
                        <button type="button"
                                x-on:click="loadMore()"
                                :disabled="loadingMore"
                                class="lnms-btn lnms-btn-default tw:w-full"
                                x-text="loadingMore ? '{{ __('config_backups.loading') }}' : '{{ __('config_backups.load_more') }}'">
                        </button>
                    </x-slot>
                </x-panel>

                {{-- Config / diff pane --}}
                <x-panel class="tw:w-full tw:flex-1 tw:min-w-0 tw:overflow-hidden tw:self-start tw:mb-0!">
                    <x-slot name="heading" class="tw:flex tw:items-center tw:justify-between">
                        <h3 class="panel-title">
                            <span x-show="diffMode">{{ __('config_backups.diff') }}<span
                                    x-show="diffSelection.length === 2"
                                    x-text="': ' + formatDate(Math.min(diffSelection[0]?.date, diffSelection[1]?.date)) + ' → ' + formatDate(Math.max(diffSelection[0]?.date, diffSelection[1]?.date))"></span></span>
                            <span x-show="!diffMode">{{ __('config_backups.configuration') }}<span
                                    x-show="selected"
                                    x-text="' - ' + formatDate(selected?.date)"></span></span>
                        </h3>
                        <div x-show="!diffMode && selected?.content != null && (!selected || selected.type === 'TEXT')"
                             x-cloak
                             class="tw:flex tw:items-center tw:gap-2">
                            <button type="button"
                                    x-on:click="downloadConfig()"
                                    class="lnms-btn lnms-btn-default tw:flex tw:items-center tw:gap-1.5 tw:transition-colors">
                                <i class="fa fa-download" aria-hidden="true"></i>
                                <span>{{ __('config_backups.download') }}</span>
                            </button>
                            <button type="button"
                                    x-on:click="copyToClipboard()"
                                    class="lnms-btn lnms-btn-default tw:flex tw:items-center tw:gap-1.5 tw:transition-colors">
                                <i class="fa" :class="copied ? 'fa-check tw:text-green-600 tw:dark:text-green-400' : 'fa-copy'" aria-hidden="true"></i>
                                <span x-text="copied ? '{{ __('config_backups.copied') }}' : '{{ __('config_backups.copy') }}'"></span>
                            </button>
                        </div>
                    </x-slot>

                    {{-- error --}}
                    <div x-show="error" x-cloak
                         class="tw:mb-3 tw:rounded-lg tw:border tw:border-red-300 tw:bg-red-50 tw:text-red-800 tw:dark:border-red-900 tw:dark:bg-red-900/30 tw:dark:text-red-300 tw:px-4 tw:py-3 tw:text-sm"
                         x-text="errorMessage()"></div>

                    {{-- loading --}}
                    <div x-show="showSpinner" x-cloak class="tw:py-10 tw:text-center tw:text-gray-500 tw:dark:text-dark-white-400">
                        <i class="fa fa-spinner tw:animate-spin fa-2x"></i>
                    </div>

                    {{-- diff view --}}
                    <template x-if="!loading && diffMode && diffReady">
                        <div class="tw:rounded-lg tw:overflow-x-auto tw:max-h-[70vh] tw:overflow-y-auto tw:border tw:border-gray-200 tw:dark:border-dark-gray-200">
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
                                            <td class="tw:px-2 tw:py-0.5 tw:whitespace-pre-wrap tw:text-gray-800 tw:dark:text-dark-white-100" x-text="row.text"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </template>

                    {{-- waiting for diff selection --}}
                    <p x-show="!loading && diffMode && !diffReady && !error" x-cloak
                       class="tw:py-10 tw:m-0 tw:text-center tw:text-gray-500 tw:dark:text-dark-white-400">
                        {{ __('config_backups.select_two_hint') }}
                    </p>

                    {{-- binary backup notice --}}
                    <p x-show="!loading && !diffMode && selected && selected.type !== 'TEXT'" x-cloak
                       class="tw:py-10 tw:m-0 tw:text-center tw:text-gray-500 tw:dark:text-dark-white-400"
                       x-text="messages.binary_not_supported"></p>

                    {{-- config view --}}
                    <template x-if="!loading && !diffMode && selected?.content != null && (!selected || selected.type === 'TEXT')">
                        <pre class="tw:m-0 tw:p-3 tw:font-mono tw:whitespace-pre-wrap tw:overflow-x-auto tw:max-h-[70vh] tw:overflow-y-auto tw:rounded-lg tw:bg-gray-50 tw:text-gray-800 tw:dark:bg-dark-gray-500 tw:dark:text-dark-white-200 tw:border tw:border-gray-200 tw:dark:border-dark-gray-200"
                             style="white-space: pre-wrap;"
                             x-text="selected.content"></pre>
                    </template>
                </x-panel>
            </div>
        @endif
    </x-device.page>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
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
                loading: false,
                loadingMore: false,
                loadingBackups: false,
                showSpinner: false,
                error: null,
                copied: false,

                // Diff State
                diffMode: false,
                diffSelection: [],
                diffGroups: null,

                init() {
                    this.loadLatest();
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
                        if (this.selected?.id === backup.id) {
                            this.selected.content = data.content;
                        }
                    } catch (error) {
                        if (this.selected?.id === backup.id) {
                            this.selected.content = null;
                            this.error = this.requestError(error);
                        }
                    } finally {
                        this.endLoading(timer);
                    }
                },

                selectBackup(backup) {
                    if (this.diffMode) {
                        this.toggleDiffSelect(backup);
                        return;
                    }

                    if (this.selected?.id === backup.id && this.selected.content != null) {
                        return;
                    }

                    this.selected = backup;
                    this.error = null;

                    if (backup.type !== 'TEXT') {
                        this.selected.content = null;
                        return;
                    }

                    this.loadBackupContent(backup);
                },

                // ── Diff ─────────────────────────────────────────────────

                toggleDiffMode() {
                    this.diffMode = !this.diffMode;
                    this.error = null;
                    this.diffMode ? this.enterDiffMode() : this.exitDiffMode();
                },

                enterDiffMode() {
                    const textBackups = this.backups.filter(b => b.type === 'TEXT');
                    const selectedIndex = this.selected
                        ? textBackups.findIndex(b => b.id === this.selected.id)
                        : -1;
                    const hasNext = selectedIndex !== -1 && selectedIndex + 1 < textBackups.length;

                    if (hasNext) {
                        this.diffSelection = [textBackups[selectedIndex], textBackups[selectedIndex + 1]]
                            .sort((a, b) => a.date - b.date);
                    } else if (textBackups.length >= 2) {
                        this.diffSelection = [textBackups[0], textBackups[1]]
                            .sort((a, b) => a.date - b.date);
                    } else {
                        this.diffSelection = [];
                        this.diffGroups = null;
                        return;
                    }

                    this.loadDiff();
                },

                exitDiffMode() {
                    if (this.diffSelection.length === 2) {
                        const [, rev] = this.diffSelection;
                        this.selectBackup(rev);
                    } else if (this.diffSelection.length === 1) {
                        this.selectBackup(this.diffSelection[0]);
                    }
                    this.diffSelection = [];
                    this.diffGroups = null;
                },

                toggleDiffSelect(backup) {
                    if (backup.type !== 'TEXT') {
                        return;
                    }

                    const index = this.diffSelection.findIndex((b) => b.id === backup.id);

                    if (index >= 0) {
                        this.diffSelection.splice(index, 1);
                        this.diffGroups = null;
                        return;
                    }

                    if (this.diffSelection.length >= 2) {
                        this.diffSelection.pop();
                    }

                    this.diffSelection.push(backup);

                    if (this.diffSelection.length === 2) {
                        this.diffSelection.sort((a, b) => a.date - b.date);
                        this.loadDiff();
                    }
                },


                get diffReady() {
                    return this.diffGroups !== null && this.diffSelection.length === 2;
                },

                get diffRoleMap() {
                    if (!this.diffMode || this.diffSelection.length !== 2) {
                        return {};
                    }
                    const [orig, rev] = this.diffSelection;
                    return { [orig.id]: 'old', [rev.id]: 'new' };
                },

                async loadDiff() {
                    const [orig, rev] = this.diffSelection;
                    const timer = this.beginLoading();
                    this.error = null;
                    this.diffGroups = null;

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

                // ── UI helpers ───────────────────────────────────────────

                isSelected(backup) {
                    if (this.diffMode) {
                        return this.diffSelection.some((b) => b.id === backup.id);
                    }

                    return this.selected?.id === backup.id;
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
            }));
        });
    </script>
@endpush
