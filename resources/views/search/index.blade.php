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
            macFormat: @js($macFormat),
            preferencesUrl: @js(route('preferences.store')),
        })">
        <x-panel>
            <form @submit.prevent="submit()" role="search">
                <div class="tw:flex tw:flex-wrap tw:gap-2">
                    <div class="tw:relative tw:flex-1 tw:min-w-64">
                        <i class="fa fa-search tw:absolute tw:left-3 tw:top-1/2 tw:-translate-y-1/2 tw:text-gray-400 tw:pointer-events-none" aria-hidden="true"></i>
                        <input type="search" name="q" x-model="input" x-ref="input" autofocus autocomplete="off"
                               @paste="paste($event)"
                               @keydown.escape.prevent="clear()"
                               @keydown.up.prevent="recall(1)"
                               @keydown.down.prevent="recall(-1)"
                               title="{{ __('search.keyboard_help') }}"
                               aria-label="{{ __('search.title') }}"
                               placeholder="{{ __('search.page_placeholder') }}"
                               class="form-control tw:pl-9! tw:pr-8! tw:[&::-webkit-search-cancel-button]:appearance-none">
                        <button type="button" x-show="input !== ''" x-cloak @click="clear(); $refs.input.focus()"
                                title="{{ __('search.clear_input') }}" aria-label="{{ __('search.clear_input') }}"
                                class="tw:absolute tw:right-2 tw:top-1/2 tw:-translate-y-1/2 tw:px-1 tw:text-gray-400 tw:hover:text-gray-700">
                            <i class="fa fa-times" aria-hidden="true"></i>
                        </button>
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

                <div class="tw:mt-2 tw:flex tw:flex-wrap tw:items-center tw:gap-1.5 tw:text-sm" x-show="recent.length" x-cloak>
                    <span class="tw:text-gray-500 tw:dark:text-dark-white-400">{{ __('search.recent') }}</span>
                    <template x-for="item in recent" :key="item">
                        <button type="button" @click="input = item; submit()"
                                class="tw:px-2 tw:rounded tw:font-mono tw:bg-gray-100 tw:text-gray-700 tw:hover:bg-gray-200 tw:dark:bg-dark-gray-300 tw:dark:text-dark-white-200 tw:dark:hover:bg-dark-gray-200"
                                x-text="item"></button>
                    </template>
                    <a href="#" @click.prevent="clearRecent()" class="tw:ml-1">{{ __('search.clear') }}</a>
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

        {{-- display options for the results, kept apart from the search options --}}
        <div class="tw:flex tw:justify-end tw:items-center tw:gap-2 tw:mb-2 tw:text-sm" x-show="searched" x-cloak>
            <label for="mac-format" class="tw:mb-0 tw:font-normal tw:text-gray-500 tw:dark:text-dark-white-400">{{ __('search.mac_format') }}</label>
            <select id="mac-format" x-model="macFormat" @change="savePreference('network_search_mac_format', macFormat)"
                    title="{{ __('search.mac_format_keys') }}"
                    class="form-control input-sm tw:w-auto! tw:font-mono">
                <option value="colon">aa:bb:cc:dd:ee:ff</option>
                <option value="dash">aa-bb-cc-dd-ee-ff</option>
                <option value="dot">aabb.ccdd.eeff</option>
                <option value="bare">aabbccddeeff</option>
            </select>
        </div>

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
                macFormat: options.macFormat,
                recent: [],
                recallIndex: -1,
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
                    window.addEventListener('keydown', (event) => this.macFormatKey(event));
                    try { this.recent = JSON.parse(localStorage.getItem('network-search-recent')) || []; } catch (e) { this.recent = []; }
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
                    this.recallIndex = -1;
                    this.remember(this.query);
                    this.updateUrl(push);
                    this.run();
                    // the next paste or keystroke replaces this search
                    this.$nextTick(() => this.$refs.input.select());
                },
                // inputs drop line breaks, keep a pasted list of addresses separated and search it right away
                paste(event) {
                    let text = event.clipboardData?.getData('text') ?? '';
                    let el = event.target;
                    let replacesAll = el.selectionStart === 0 && el.selectionEnd === el.value.length;
                    event.preventDefault();
                    el.setRangeText(text.replace(/\s+/g, ' ').trim(), el.selectionStart, el.selectionEnd, 'end');
                    this.input = el.value;
                    if (replacesAll) { this.submit(); }
                },
                clear() {
                    this.input = '';
                    this.recallIndex = -1;
                },
                // step through recent searches like shell history
                recall(direction) {
                    if (this.recent.length === 0) { return; }
                    this.recallIndex = Math.max(-1, Math.min(this.recent.length - 1, this.recallIndex + direction));
                    this.input = this.recallIndex === -1 ? '' : this.recent[this.recallIndex];
                },
                remember(query) {
                    if (query === '') { return; }
                    this.recent = [query, ...this.recent.filter(item => item !== query)].slice(0, 10);
                    try { localStorage.setItem('network-search-recent', JSON.stringify(this.recent)); } catch (e) {}
                },
                clearRecent() {
                    this.recent = [];
                    try { localStorage.removeItem('network-search-recent'); } catch (e) {}
                },
                formatMac(hex) {
                    let pairs = hex.match(/../g) || [];
                    return {
                        dash: pairs.join('-'),
                        dot: (hex.match(/..../g) || []).join('.'),
                        bare: hex,
                    }[this.macFormat] ?? pairs.join(':');
                },
                copy(event, text) {
                    let icon = event.currentTarget.querySelector('i');
                    let done = () => {
                        icon.classList.replace('fa-copy', 'fa-check');
                        setTimeout(() => icon.classList.replace('fa-check', 'fa-copy'), 1200);
                    };
                    if (navigator.clipboard) {
                        navigator.clipboard.writeText(text).then(done);
                        return;
                    }
                    let area = Object.assign(document.createElement('textarea'), {value: text});
                    document.body.appendChild(area);
                    area.select();
                    document.execCommand('copy');
                    area.remove();
                    done();
                },
                // m cycles the MAC format, 1-4 picks one, only while not typing in a field
                macFormatKey(event) {
                    let el = document.activeElement;
                    if (!this.searched || event.ctrlKey || event.metaKey || event.altKey
                        || (el && (['INPUT', 'TEXTAREA', 'SELECT'].includes(el.tagName) || el.isContentEditable))) {
                        return;
                    }
                    let formats = ['colon', 'dash', 'dot', 'bare'];
                    let index = event.key.toLowerCase() === 'm'
                        ? (formats.indexOf(this.macFormat) + (event.shiftKey ? formats.length - 1 : 1)) % formats.length
                        : ['1', '2', '3', '4'].indexOf(event.key);
                    if (index === -1) { return; }
                    event.preventDefault();
                    this.macFormat = formats[index];
                    this.savePreference('network_search_mac_format', this.macFormat);
                },
                savePreference(pref, value) {
                    fetch(options.preferencesUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify({pref: pref, value: value}),
                    });
                },
                saveSources() {
                    if (this.selected.length === 0) { return; }
                    this.savePreference('network_search_sources', this.selected.length === this.sources.length ? 'default' : this.selected.join(','));
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
                    this.saveSources();
                },
                selectAll() {
                    let added = this.sources.map(s => s.key).filter(k => !this.isSelected(k));
                    this.selected = this.sources.map(s => s.key);
                    if (this.searched) { added.forEach(key => this.fetchSource(key, 1)); }
                    this.updateUrl(false);
                    this.saveSources();
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
