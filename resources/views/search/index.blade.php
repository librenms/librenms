@extends('layouts.librenmsv1')

@section('title', __('search.title'))

@section('content')
    <div class="container-fluid" x-data="networkSearch({
            query: @js($query),
            hasQuery: @js($hasQuery),
            openHost: @js($openHost),
            filters: @js((object) $filter),
            resultsUrl: @js($resultsUrl),
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
                    <button type="submit" class="btn btn-primary">{{ __('search.search') }}</button>
                </div>

                <div class="tw:mt-2 tw:flex tw:flex-wrap tw:items-center tw:gap-1.5 tw:text-sm" x-show="recent.length" x-cloak>
                    <span class="tw:text-gray-500 tw:dark:text-dark-white-400">{{ __('search.recent') }}</span>
                    <template x-for="item in recent" :key="item">
                        <button type="button" @click="input = item; submit()"
                                class="tw:px-2 tw:rounded tw:font-mono tw:max-w-64 tw:truncate tw:bg-gray-100 tw:text-gray-700 tw:hover:bg-gray-200 tw:dark:bg-dark-gray-300 tw:dark:text-dark-white-200 tw:dark:hover:bg-dark-gray-200"
                                :title="item" x-text="item"></button>
                    </template>
                    <a href="#" @click.prevent="clearRecent()" class="tw:ml-1">{{ __('search.clear') }}</a>
                </div>
            </form>

            <div class="tw:mt-3">
                <x-filter name="network-search" :fields="$filterFields" :initial="$filter" />
            </div>
        </x-panel>

        <div class="tw:flex tw:flex-wrap tw:items-center tw:gap-x-4 tw:gap-y-2 tw:mb-2 tw:text-sm" x-show="searched" x-cloak>
            <span class="tw:text-gray-500 tw:dark:text-dark-white-400" x-show="result.mode === 'list'" x-text="result.summary"></span>
            <i class="fa fa-spinner fa-spin tw:text-gray-400" x-show="loading" aria-hidden="true"></i>
            <span class="tw:flex-1"></span>
            <label for="per-page" x-show="result.mode === 'list'" class="tw:mb-0 tw:font-normal tw:text-gray-500 tw:dark:text-dark-white-400">{{ __('search.per_page') }}</label>
            <select id="per-page" x-show="result.mode === 'list'" x-model.number="listPerPage" @change="setPerPage()" class="form-control input-sm tw:w-auto!">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
            <label for="mac-format" x-show="result.mode !== 'not_found'" class="tw:mb-0 tw:font-normal tw:text-gray-500 tw:dark:text-dark-white-400">{{ __('search.mac_format') }}</label>
            <select id="mac-format" x-show="result.mode !== 'not_found'" x-model="macFormat" @change="savePreference('network_search_mac_format', macFormat)"
                    title="{{ __('search.mac_format_keys') }}"
                    class="form-control input-sm tw:w-auto! tw:font-mono">
                <option value="colon">aa:bb:cc:dd:ee:ff</option>
                <option value="dash">aa-bb-cc-dd-ee-ff</option>
                <option value="dot">aabb.ccdd.eeff</option>
                <option value="bare">aabbccddeeff</option>
            </select>
            {{-- the button has its own page and perPage, the list's are named so they can be read here --}}
            <x-table-export :export-route="route('search.export')" :filter="$filter" filter-name="network-search" class="tw:shrink-0"
                            x-show="result.mode === 'list'" x-cloak
                            x-effect="filter = filters; page = listPage; perPage = listPerPage; params = {q: query, sort: sort.key, dir: sort.dir}" />
        </div>

        <div x-html="result.html" @click="followPageLink($event)"></div>

        <div x-show="error" x-cloak class="tw:py-12 tw:text-center tw:text-red-600">{{ __('search.error') }}</div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            window.Alpine.data('networkSearch', (options) => ({
                input: options.query,
                query: '',
                searched: false,
                forceHost: options.openHost,
                filters: options.filters || {},
                result: {},
                loading: false,
                error: false,
                listPage: 1,
                listPerPage: 25,
                sort: {key: null, dir: 'asc'},
                macFormat: options.macFormat,
                recent: [],
                recallIndex: -1,
                needles: {text: [], macs: []},
                controller: null,
                init() {
                    window.addEventListener('popstate', () => this.loadFromUrl());
                    window.addEventListener('keydown', (event) => this.macFormatKey(event));
                    window.addEventListener('filter:apply', (event) => this.applyFilters(event.detail));
                    try { this.recent = (JSON.parse(localStorage.getItem('network-search-recent')) || []).slice(0, 5); } catch (e) { this.recent = []; }
                    this.readListOptions();
                    if (options.hasQuery || Object.keys(this.filters).length > 0) {
                        this.submit(false);
                    }
                },
                // an empty search lists every host
                submit(push = true) {
                    this.forceHost = push ? false : this.forceHost;
                    this.listPage = push ? 1 : this.listPage;
                    this.setQuery(this.input.trim());
                    this.remember(this.query);
                    this.updateUrl(push);
                    this.fetchResults();
                    // the next paste or keystroke replaces this search
                    this.$nextTick(() => this.$refs.input.select());
                },
                setQuery(query) {
                    this.query = query;
                    this.searched = true;
                    this.recallIndex = -1;
                    this.needles = this.findNeedles(query);
                },
                applyFilters(detail) {
                    if (detail.name !== 'network-search' || JSON.stringify(detail.filters) === JSON.stringify(this.filters)) { return; }
                    this.filters = detail.filters;
                    this.forceHost = false;
                    if (this.searched || Object.keys(this.filters).length > 0) {
                        this.listPage = 1;
                        this.setQuery(this.input.trim());
                        this.fetchResults();
                    }
                },
                setSort(key) {
                    this.sort = {key: key, dir: this.result.sort === key && this.result.dir === 'asc' ? 'desc' : 'asc'};
                    this.listPage = 1;
                    this.updateUrl(true);
                    this.fetchResults();
                },
                setPerPage() {
                    this.listPage = 1;
                    this.updateUrl(true);
                    this.fetchResults();
                },
                // the page links are regular links to this page, stay on it without reloading
                followPageLink(event) {
                    let link = event.target.closest('nav a[href]');
                    if (!link || event.ctrlKey || event.metaKey || event.shiftKey) { return; }
                    event.preventDefault();
                    history.pushState({}, '', link.href);
                    this.loadFromUrl();
                    window.scrollTo({top: 0});
                },
                readListOptions() {
                    let params = new URLSearchParams(window.location.search);
                    this.listPage = parseInt(params.get('page')) || 1;
                    this.listPerPage = parseInt(params.get('per_page')) || 25;
                    this.sort = {key: params.get('sort'), dir: params.get('dir') || 'asc'};
                },
                fetchResults() {
                    this.controller?.abort();
                    this.controller = new AbortController();
                    this.loading = true;
                    this.error = false;
                    let url = new URL(options.resultsUrl, window.location.origin);
                    url.search = new URLSearchParams({q: this.query, page: this.listPage, per_page: this.listPerPage});
                    if (this.sort.key) { url.searchParams.set('sort', this.sort.key); url.searchParams.set('dir', this.sort.dir); }
                    if (this.forceHost) { url.searchParams.set('view', 'host'); }
                    LibreNMS.Url.applyNestedParamsToUrl(url, 'filter', this.filters);
                    fetch(url, {signal: this.controller.signal, headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}})
                        .then(response => response.ok ? response.json() : Promise.reject(response))
                        .then(data => { this.result = data; this.loading = false; })
                        .catch(error => {
                            if (error?.name === 'AbortError') { return; }
                            this.result = {};
                            this.loading = false;
                            this.error = true;
                        });
                },
                updateUrl(push) {
                    let url = new URL(window.location);
                    url.searchParams.set('q', this.query);
                    this.forceHost ? url.searchParams.set('view', 'host') : url.searchParams.delete('view');
                    this.listPage > 1 ? url.searchParams.set('page', this.listPage) : url.searchParams.delete('page');
                    this.listPerPage !== 25 ? url.searchParams.set('per_page', this.listPerPage) : url.searchParams.delete('per_page');
                    this.sort.key ? url.searchParams.set('sort', this.sort.key) : url.searchParams.delete('sort');
                    this.sort.key ? url.searchParams.set('dir', this.sort.dir) : url.searchParams.delete('dir');
                    push ? history.pushState({}, '', url) : history.replaceState({}, '', url);
                },
                loadFromUrl() {
                    let params = new URLSearchParams(window.location.search);
                    let filters = {};
                    for (let [key, value] of params.entries()) {
                        let match = key.match(/^filter\[(.+)\]\[(.+)\]$/);
                        if (match) { (filters[match[1]] ??= {})[match[2]] = value; }
                    }
                    this.filters = filters;
                    this.input = params.get('q') || '';
                    this.forceHost = params.get('view') === 'host';
                    this.readListOptions();
                    if (params.has('q') || Object.keys(filters).length > 0) {
                        this.setQuery(this.input);
                        this.fetchResults();
                    } else {
                        this.searched = false;
                        this.query = '';
                        this.result = {};
                    }
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
                    this.recent = [query, ...this.recent.filter(item => item !== query)].slice(0, 5);
                    try { localStorage.setItem('network-search-recent', JSON.stringify(this.recent)); } catch (e) {}
                },
                clearRecent() {
                    this.recent = [];
                    try { localStorage.removeItem('network-search-recent'); } catch (e) {}
                },
                // the parts of the search to highlight in the results, networks match by range so they are skipped
                findNeedles(query) {
                    let tokens = query.split(/[\s,;]+/)
                        .map(token => token.replace(/^["'`(\[{<]+|["'`)\]}>]+$/g, '').replace(/^(ip|ipv4|ipv6|addr|address|mac|hwaddr|ether|lladdr|host|hostname)\s*[=:]\s*/i, '').replace(/^(\d+(?:\.\d+){3}):\d+$/, '$1').toLowerCase())
                        .filter(token => token.length > 1 && !token.includes('/'));

                    return {
                        text: tokens,
                        macs: tokens.map(token => token.replace(/^0x|[:\-. ]/g, '')).filter(token => /^[0-9a-f]{4,12}$/.test(token)),
                    };
                },
                escapeHtml(text) {
                    return text.replace(/[&<>"']/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'})[c]);
                },
                mark(html) {
                    // not <mark>, the legacy styles give it a loud background
                    return '<span class="tw:bg-amber-200/60 tw:dark:bg-amber-300/20 tw:rounded-sm">' + html + '</span>';
                },
                highlightText(text) {
                    let lower = text.toLowerCase();
                    for (let needle of this.needles.text) {
                        let start = lower.indexOf(needle);
                        if (start !== -1) {
                            return this.escapeHtml(text.slice(0, start)) + this.mark(this.escapeHtml(text.slice(start, start + needle.length))) + this.escapeHtml(text.slice(start + needle.length));
                        }
                    }
                    return this.escapeHtml(text);
                },
                // highlight the matched hex digits in whichever format the MAC is shown
                highlightMac(hex) {
                    let formatted = this.formatMac(hex);
                    let needle = this.needles.macs.find(needle => hex.includes(needle));
                    if (!needle) { return formatted; }
                    let start = hex.indexOf(needle), end = start + needle.length;
                    let html = '', digit = 0, run = '';
                    for (let char of formatted) {
                        let separator = !/[0-9a-f]/i.test(char);
                        let inside = separator ? digit > start && digit < end : digit >= start && digit < end;
                        if (!separator) { digit++; }
                        if (inside) { run += char; continue; }
                        if (run) { html += this.mark(run); run = ''; }
                        html += char;
                    }
                    return html + (run ? this.mark(run) : '');
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
            }));
        });
    </script>
@endpush
