@extends('layouts.librenmsv1')

@section('title', __('search.title'))

@section('content')
    <div class="container-fluid" x-data="networkSearch({
            query: @js($query),
            browse: @js($browse),
            sources: @js($sources),
            selected: @js($selected),
            deviceId: @js($device?->device_id ? (string) $device->device_id : ''),
            locateUrl: @js($locateUrl),
        })">
        <x-panel>
            <form @submit.prevent="submit()" role="search">
                <div class="tw:flex tw:flex-wrap tw:gap-2">
                    <div class="tw:relative tw:flex-1 tw:min-w-64">
                        <i class="fa fa-search tw:absolute tw:left-3 tw:top-1/2 tw:-translate-y-1/2 tw:text-gray-400 tw:pointer-events-none" aria-hidden="true"></i>
                        <input type="search" name="q" x-model="input" x-ref="input" autofocus autocomplete="off"
                               aria-label="{{ __('search.title') }}"
                               placeholder="{{ __('search.page_placeholder') }}"
                               class="form-control tw:pl-9!">
                    </div>
                    <div class="tw:w-full tw:sm:w-64 tw:self-center">
                        <x-select2 type="device" x-model="deviceId" :placeholder="__('search.all_devices')" aria-label="{{ __('search.columns.device') }}">
                            <option value=""></option>
                            @if($device)
                                <option value="{{ $device->device_id }}" selected>{{ $device->displayName() }}</option>
                            @endif
                        </x-select2>
                    </div>
                    <button type="submit" class="btn btn-primary">{{ __('search.search') }}</button>
                </div>

                <div class="tw:mt-3 tw:flex tw:flex-wrap tw:items-center tw:gap-2">
                    <span class="tw:text-gray-500 tw:dark:text-dark-white-400">{{ __('search.sources_label') }}</span>
                    <template x-for="source in sources" :key="source.key">
                        <button type="button" @click="toggle(source.key)"
                                :aria-pressed="isSelected(source.key)"
                                :title="source.description + (results[source.key]?.applicable === false ? ' (' + @js(__('search.not_applicable')) + ')' : '')"
                                class="tw:inline-flex tw:items-center tw:gap-1.5 tw:px-3 tw:py-1 tw:rounded-full tw:border tw:text-sm tw:transition-colors"
                                :class="[
                                    isSelected(source.key)
                                        ? 'tw:bg-blue-600 tw:border-blue-600 tw:text-white tw:hover:bg-blue-700'
                                        : 'tw:bg-white tw:border-gray-300 tw:text-gray-700 tw:hover:bg-gray-50 tw:dark:bg-dark-gray-300 tw:dark:border-dark-gray-200 tw:dark:text-dark-white-200 tw:dark:hover:bg-dark-gray-200',
                                    results[source.key]?.applicable === false ? 'tw:opacity-50' : '',
                                ]">
                            <i class="fa fa-fw" :class="source.icon" aria-hidden="true"></i>
                            <span x-text="source.label"></span>
                            <i class="fa fa-spinner fa-spin" x-show="results[source.key]?.loading" x-cloak aria-hidden="true"></i>
                            <span x-show="!results[source.key]?.loading && results[source.key]?.total > 0" x-cloak
                                  class="tw:rounded-full tw:px-1.5 tw:text-xs tw:font-bold"
                                  :class="isSelected(source.key) ? 'tw:bg-white tw:text-blue-700' : 'tw:bg-gray-200 tw:text-gray-700 tw:dark:bg-dark-gray-100 tw:dark:text-dark-white-100'"
                                  x-text="results[source.key]?.total"></span>
                        </button>
                    </template>
                    <span class="tw:text-sm">
                        <a href="#" @click.prevent="selectAll()">{{ __('search.all') }}</a> |
                        <a href="#" @click.prevent="selectNone()">{{ __('search.none') }}</a>
                    </span>
                </div>
                <p class="tw:mt-3 tw:mb-0 tw:text-sm tw:text-gray-500 tw:dark:text-dark-white-400" x-show="!searched">{{ __('search.hint') }}</p>
            </form>
        </x-panel>

        <div x-show="locate.loading" x-cloak class="tw:mb-4 tw:text-gray-500"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i> {{ __('search.searching') }}</div>
        <div x-html="locate.html"></div>

        <template x-for="source in withResults" :key="source.key">
            <div class="panel panel-default">
                <div class="panel-heading tw:flex tw:flex-wrap tw:items-center tw:justify-between tw:gap-2">
                    <h3 class="panel-title">
                        <i class="fa fa-fw" :class="source.icon" aria-hidden="true"></i>
                        <span x-text="source.label"></span>
                        <span class="badge" x-text="results[source.key].total"></span>
                    </h3>
                    <div class="tw:flex tw:items-center tw:gap-2 tw:text-sm" x-show="results[source.key].last_page > 1">
                        <i class="fa fa-spinner fa-spin" x-show="results[source.key].loading" aria-hidden="true"></i>
                        <span class="tw:text-gray-500 tw:dark:text-dark-white-400"
                              x-text="@js(__('search.showing')).replace(':from', results[source.key].from).replace(':to', results[source.key].to).replace(':total', results[source.key].total)"></span>
                        <div class="btn-group btn-group-xs">
                            <button type="button" class="btn btn-default" :disabled="results[source.key].page <= 1"
                                    @click="fetchSource(source.key, results[source.key].page - 1)" aria-label="{{ __('search.previous') }}">
                                <i class="fa fa-chevron-left" aria-hidden="true"></i>
                            </button>
                            <button type="button" class="btn btn-default" :disabled="results[source.key].page >= results[source.key].last_page"
                                    @click="fetchSource(source.key, results[source.key].page + 1)" aria-label="{{ __('search.next') }}">
                                <i class="fa fa-chevron-right" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive" x-html="results[source.key].html"></div>
            </div>
        </template>

        <div x-show="searched && !loading && withResults.length === 0 && !locate.html" x-cloak
             class="tw:py-12 tw:text-center tw:text-gray-500 tw:dark:text-dark-white-400">
            <i class="fa fa-search fa-2x tw:mb-2" aria-hidden="true"></i>
            <div>{{ __('search.no_results') }}</div>
        </div>

        <div class="tw:text-sm tw:text-gray-500 tw:dark:text-dark-white-400 tw:space-y-1" x-show="searched" x-cloak>
            <div x-show="emptySources.length">
                {{ __('search.no_matches_in') }}: <span x-text="emptySources.map(s => s.label).join(', ')"></span>
            </div>
            <div x-show="skippedSources.length">
                {{ __('search.skipped') }}: <span x-text="skippedSources.map(s => s.label).join(', ')"></span>
                <span class="tw:italic">({{ __('search.not_applicable') }})</span>
            </div>
            <div x-show="failedSources.length" class="tw:text-red-600">
                {{ __('search.error') }}: <span x-text="failedSources.map(s => s.label).join(', ')"></span>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            window.Alpine.data('networkSearch', (options) => ({
                sources: options.sources,
                selected: options.selected,
                deviceId: options.deviceId,
                input: options.query,
                query: '',
                searched: false,
                results: {},
                locate: {loading: false, html: ''},
                controllers: {},
                seq: 0,
                init() {
                    // select2 changes the device through x-model, rerun the current search
                    this.$watch('deviceId', () => { this.updateUrl(false); if (this.searched) { this.run(); } });
                    window.addEventListener('popstate', () => this.loadFromUrl());
                    if (this.input.trim() || options.browse) {
                        this.submit(false);
                    }
                },
                get loading() { return this.locate.loading || Object.values(this.results).some(r => r.loading); },
                get withResults() { return this.sources.filter(s => this.isSelected(s.key) && this.results[s.key]?.total > 0); },
                get emptySources() { return this.sources.filter(s => this.isSelected(s.key) && this.results[s.key]?.applicable && !this.results[s.key].loading && this.results[s.key].total === 0); },
                get skippedSources() { return this.sources.filter(s => this.isSelected(s.key) && this.results[s.key]?.applicable === false); },
                get failedSources() { return this.sources.filter(s => this.isSelected(s.key) && this.results[s.key]?.error); },
                isSelected(key) { return this.selected.includes(key); },
                // an empty search lists every entry of the selected sources
                submit(push = true) {
                    this.query = this.input.trim();
                    this.searched = true;
                    this.updateUrl(push);
                    this.run();
                },
                run() {
                    this.seq++;
                    Object.values(this.controllers).forEach(c => c.abort());
                    this.controllers = {};
                    this.results = {};
                    this.fetchLocate();
                    this.selected.forEach(key => this.fetchSource(key, 1));
                },
                toggle(key) {
                    if (this.isSelected(key)) {
                        this.selected = this.selected.filter(k => k !== key);
                        this.controllers[key]?.abort();
                    } else {
                        // keep the display order of the sources
                        this.selected = this.sources.map(s => s.key).filter(k => k === key || this.isSelected(k));
                        if (this.searched) { this.fetchSource(key, 1); }
                    }
                    this.updateUrl(false);
                },
                selectAll() {
                    let added = this.sources.map(s => s.key).filter(k => !this.isSelected(k));
                    this.selected = this.sources.map(s => s.key);
                    if (this.searched) { added.forEach(key => this.fetchSource(key, 1)); }
                    this.updateUrl(false);
                },
                selectNone() {
                    Object.values(this.controllers).forEach(c => c.abort());
                    this.selected = [];
                    this.updateUrl(false);
                },
                params(extra = {}) {
                    let params = new URLSearchParams({q: this.query, ...extra});
                    if (this.deviceId) { params.set('device_id', this.deviceId); }
                    return params;
                },
                fetchJson(key, url) {
                    this.controllers[key]?.abort();
                    let controller = new AbortController();
                    this.controllers[key] = controller;
                    return fetch(url, {signal: controller.signal, headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}})
                        .then(response => response.ok ? response.json() : Promise.reject(response));
                },
                fetchSource(key, page) {
                    let source = this.sources.find(s => s.key === key);
                    let seq = this.seq;
                    this.results[key] = {...(this.results[key] || {total: 0, html: ''}), loading: true, error: false};
                    this.fetchJson(key, source.url + '?' + this.params({page: page}))
                        .then(data => { if (seq === this.seq) { this.results[key] = {...data, loading: false, error: false}; } })
                        .catch(error => {
                            if (error?.name === 'AbortError' || seq !== this.seq) { return; }
                            this.results[key] = {applicable: true, total: 0, html: '', loading: false, error: true};
                        });
                },
                fetchLocate() {
                    this.locate = {loading: false, html: ''};
                    if (!options.locateUrl || this.query === '') { return; }
                    let seq = this.seq;
                    this.locate.loading = true;
                    this.fetchJson('_locate', options.locateUrl + '?' + new URLSearchParams({q: this.query}))
                        .then(data => { if (seq === this.seq) { this.locate = {loading: false, html: data.html}; } })
                        .catch(error => { if (error?.name !== 'AbortError' && seq === this.seq) { this.locate = {loading: false, html: ''}; } });
                },
                updateUrl(push) {
                    let params = new URLSearchParams();
                    if (this.searched) { params.set('q', this.query); }
                    if (this.selected.length !== this.sources.length) { params.set('sources', this.selected.join(',')); }
                    if (this.deviceId) { params.set('device_id', this.deviceId); }
                    let url = window.location.pathname + (params.toString() ? '?' + params : '');
                    push ? history.pushState({}, '', url) : history.replaceState({}, '', url);
                },
                loadFromUrl() {
                    let params = new URLSearchParams(window.location.search);
                    this.input = params.get('q') || '';
                    let sources = (params.get('sources') || '').split(',').filter(k => this.sources.some(s => s.key === k));
                    this.selected = sources.length ? sources : this.sources.map(s => s.key);
                    if (params.has('q')) {
                        this.submit(false);
                    } else {
                        this.searched = false;
                        this.query = '';
                        this.results = {};
                        this.locate = {loading: false, html: ''};
                    }
                },
            }));
        });
    </script>
@endpush
