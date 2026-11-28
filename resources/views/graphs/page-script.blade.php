<script>
    document.addEventListener('alpine:init', () => {
        const { limitRange, now, panRange, resolveRange, zoomRange, toCsv } = window.LibreNMS.Graphs;
        const SAVE_DELAY = 500;
        const COMPARE = { period: 'yes', day: 'day', week: 'week' };
        const PREFERENCES_OPEN = 'graph-settings-preferences-open';

        // Storage can be blocked, the section is then closed.
        function remembered(key) {
            try {
                return window.localStorage.getItem(key) === '1';
            } catch {
                return false;
            }
        }

        function remember(key, on) {
            try {
                window.localStorage.setItem(key, on ? '1' : '0');
            } catch {
                // kept for this page only
            }
        }

        function download(blob, name) {
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = name;
            link.click();
            setTimeout(() => URL.revokeObjectURL(link.href), 0);
        }

        const same = (first, second) => JSON.stringify(first) === JSON.stringify(second);

        // A missing value removes a parameter so shared URLs can follow the graph defaults.
        function setParam(params, key, value) {
            if (value == null) {
                params.delete(key);
            } else {
                params.set(key, value);
            }
        }

        window.Alpine.data('graphSettingsMenu', () => ({
            open: false,
            menuStyle: {},

            placeMenu() {
                const picker = this.$refs.graphPickerGroup.getBoundingClientRect();
                const actions = this.$refs.graphActions.getBoundingClientRect();
                const wrapped = actions.top >= picker.bottom;

                this.menuStyle = wrapped ? {
                    position: 'fixed',
                    inset: '0',
                    margin: 'auto',
                    height: 'max-content',
                    maxHeight: 'calc(100dvh - 2rem)',
                    zIndex: '1101', // above the sticky navbar
                } : {};
            },
        }));

        window.Alpine.data('graphsPage', ({ range, options, interactive, view, ...config }) => {
            let saving = null;
            // the menus have their own data, where $root and $refs are theirs
            let root = null;

            return {
                // seconds up to now, null for a fixed range
                last: range.last ?? null,
                options: { ...options, ...view },
                state: { loading: interactive, refreshing: false, zoomed: false, features: null },
                showHelp: false,
                // the settings for all graphs are set once, so they start closed
                preferencesOpen: remembered(PREFERENCES_OPEN),
                // the picker is being set to what the graph shows
                syncing: false,

                init() {
                    root = this.$root;
                    root.querySelector('#period-thumbs [aria-current="true"]')?.scrollIntoView({ block: 'nearest', inline: 'center' });
                    if (interactive) {
                        this.updateAddress();
                    }
                },

                withShownRange(href) {
                    const url = new URL(href, window.location.href);
                    const shown = new URL(window.location.href).searchParams;

                    ['from', 'to'].forEach((key) => {
                        setParam(url.searchParams, key, shown.get(key));
                    });

                    return url.toString();
                },

                // The settings of this graph, left out at their defaults. The preferences are each viewer's own.
                updateAddress() {
                    const url = new URL(window.location.href);
                    for (const key of ['mirror', 'stacking', 'scale']) {
                        const option = this.options[key];
                        const value = typeof option === 'boolean' ? Number(option) : option;
                        setParam(url.searchParams, key, option === config.viewDefaults[key] ? null : value);
                    }
                    setParam(url.searchParams, 'previous', COMPARE[this.options.compare]);
                    setParam(url.searchParams, 'trend', this.options.trend ? '1' : null);
                    setParam(url.searchParams, 'percentile_lines', this.options.percentile_lines ? null : '0');

                    window.history.replaceState(window.history.state, '', url);
                },

                graph() {
                    return root.querySelector('#page-graph')?.timeSeriesGraph;
                },

                // an image graph is drawn again for the new range
                navigate(view) {
                    const { from, to } = limitRange(view);
                    const url = new URL(window.location.href);

                    url.searchParams.set('from', String(from));
                    url.searchParams.set('to', String(to));
                    window.location = url.toString();
                },

                // negative for out
                zoom(steps) {
                    if (interactive) {
                        this.graph()?.zoom(steps);

                        return;
                    }

                    this.navigate(zoomRange(resolveRange(range), steps));
                },

                // negative for earlier
                pan(steps) {
                    if (interactive) {
                        this.graph()?.pan(steps);

                        return;
                    }

                    this.navigate(panRange(resolveRange(range), steps));
                },

                viewChanged() {
                    return Object.entries(config.viewDefaults).some(([key, value]) => this.options[key] !== value);
                },

                preferencesChanged() {
                    return Object.entries(config.preferenceDefaults).some(([key, value]) => ! same(this.options[key], value));
                },

                resettable() {
                    return this.state.zoomed || this.viewChanged() || this.options.height !== null;
                },

                // The automatic size is saved like a resize, the other preferences are the viewer's taste and stay.
                reset() {
                    this.graph()?.reset();
                    if (this.viewChanged()) {
                        this.setOptions(config.viewDefaults);
                    }
                    if (this.options.height !== null) {
                        this.setOptions({ width: null, height: null });
                        this.saveOptions();
                    }
                },

                // What the graph does by itself as one of the choices, picking it is the default. null when the default is a choice of its own.
                ownChoice(key) {
                    const features = this.state.features ?? {};
                    const own = key === 'stacking'
                        ? { all: 'stack', none: 'overlay' }[features.stacking]
                        : (features.scale === 'fit' ? 'fit' : undefined);

                    return own ?? null;
                },

                isChosen(key, value) {
                    const chosen = this.options[key] === 'graph' ? this.ownChoice(key) ?? 'graph' : this.options[key];

                    return chosen === value;
                },

                choose(key, value) {
                    this.setOption(key, value === this.ownChoice(key) ? 'graph' : value);
                },

                keydown(event) {
                    const actions = {
                        '?': () => { this.showHelp = ! this.showHelp; },
                        ArrowLeft: () => this.pan(-1),
                        ArrowRight: () => this.pan(1),
                        '+': () => this.zoom(1),
                        '=': () => this.zoom(1),
                        '-': () => this.zoom(-1),
                        r: () => this.reset(),
                        R: () => this.reset(),
                    };

                    if (! (event.key in actions) || event.ctrlKey || event.metaKey || event.altKey || this.state.loading
                        || (this.showHelp && event.key !== '?')
                        || event.target.closest?.('input, textarea, select, [contenteditable]')) {
                        return;
                    }

                    event.preventDefault();
                    actions[event.key]();
                },

                saveRenderer(interactiveNext) {
                    window.axios.post(config.preferencesUrl, { pref: config.rendererPreference, value: interactiveNext ? 'js' : 'default' })
                        .then(() => window.location.reload());
                },

                saveOptions() {
                    clearTimeout(saving);
                    saving = setTimeout(() => {
                        const saved = Object.fromEntries(Object.keys(config.preferenceDefaults).map((key) => [key, this.options[key]]));
                        const value = this.preferencesChanged() ? saved : 'default';
                        window.axios.post(config.preferencesUrl, { pref: config.optionsPreference, value })
                            .catch((error) => toastr.error(error.response?.data?.message ?? error.message));
                    }, SAVE_DELAY);
                },

                setOptions(next) {
                    Object.assign(this.options, next);
                    this.graph()?.setOptions(next);
                    this.updateAddress();
                },

                setOption(key, value) {
                    this.setOptions({ [key]: value });
                    if (key in config.preferenceDefaults) {
                        this.saveOptions();
                    }
                },

                togglePreferences() {
                    this.preferencesOpen = ! this.preferencesOpen;
                    remember(PREFERENCES_OPEN, this.preferencesOpen);
                },

                columnsChanged({ columns, hidden_percentiles }) {
                    this.options.columns = columns;
                    this.options.hidden_percentiles = hidden_percentiles;
                    this.saveOptions();
                },

                resized({ width, height }) {
                    this.options.width = width;
                    this.options.height = height;
                    this.saveOptions();
                },

                // the address follows the range and the settings of this view
                copyLink() {
                    LibreNMS.Clipboard.copyLink(window.location.href, config.lang);
                },

                async exportImage() {
                    const image = root.querySelector('[data-graph-image]');
                    const picture = interactive
                        ? await this.graph()?.image()
                        : await window.axios.get(image.currentSrc || image.src, { responseType: 'blob' }).then((response) => response.data);
                    if (picture) {
                        // image graphs may be SVG
                        download(picture, `${config.fileName}.${picture.type === 'image/svg+xml' ? 'svg' : 'png'}`);
                    }
                },

                async exportCsv() {
                    let csv = this.graph()?.csv();
                    if (! interactive) {
                        const image = root.querySelector('[data-graph-image]');
                        // a dynamic image graph pans and zooms by itself
                        const legacy = image.legacyGraph;
                        const view = legacy
                            ? { from: Math.round(legacy.getStart()), to: Math.round(legacy.getStart() + legacy.getRange()) }
                            : resolveRange(range);
                        const width = image.clientWidth || window.innerWidth;
                        const response = await window.axios.get(config.dataUrl, { params: { ...view, width } });
                        csv = toCsv(response.data, view);
                    }
                    if (csv) {
                        download(new Blob([csv], { type: 'text/csv' }), `${config.fileName}.csv`);
                    }
                },

                isPeriod(seconds) {
                    return this.last !== null && Math.abs(this.last - seconds) <= 0.05 * seconds;
                },

                // modified clicks still open the link
                showPeriod(seconds, event) {
                    if (event.ctrlKey || event.metaKey || event.shiftKey || ! this.graph()) {
                        return;
                    }

                    event.preventDefault();
                    this.graph().setRange({ last: seconds });
                },

                picked({ start, end, relativeStartSeconds, relativeEndSeconds }) {
                    if (this.syncing) {
                        return;
                    }

                    if (! start) {
                        // cleared: the default period
                        this.graph()?.setRange({ last: 86400 });

                        return;
                    }

                    const time = now();
                    const next = relativeStartSeconds !== null && relativeEndSeconds === null
                        ? { last: relativeStartSeconds }
                        : {
                            from: relativeStartSeconds !== null ? time - relativeStartSeconds : LibreNMS.Date.toUrl(start),
                            to: relativeEndSeconds !== null ? time - relativeEndSeconds : (end ? LibreNMS.Date.toUrl(end) : time),
                        };

                    this.graph()?.setRange(next);
                },

                rangeChanged(shown) {
                    const from = 'last' in shown ? LibreNMS.Date.toShortOffset(shown.last) : String(shown.from);
                    const to = 'last' in shown ? null : String(shown.to);
                    const url = new URL(window.location.href);

                    url.searchParams.set('from', from);
                    setParam(url.searchParams, 'to', to);
                    window.history.replaceState(window.history.state, '', url);

                    range = shown;
                    this.last = shown.last ?? null;

                    const picker = root.querySelector('[data-graph-picker]')?.dateRangePicker;
                    this.syncing = true;
                    picker?.clear();
                    picker?.set(from, to);
                    this.syncing = false;
                },
            };
        });
    });
</script>
