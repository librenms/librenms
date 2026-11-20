<script>
    document.addEventListener('alpine:init', () => {
        const { limitRange, now, panRange, resolveRange, zoomRange, toCsv } = window.LibreNMS.Graphs;
        const SAVE_DELAY = 500;
        const COMPARE = { period: 'yes', day: 'day', week: 'week' };

        function download(blob, name) {
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = name;
            link.click();
            setTimeout(() => URL.revokeObjectURL(link.href), 0);
        }

        // A missing value removes a parameter so shared URLs can follow the graph defaults.
        function setParam(params, key, value) {
            if (value == null) {
                params.delete(key);
            } else {
                params.set(key, value);
            }
        }

        window.Alpine.data('graphsPage', ({ range, options, interactive, view, ...config }) => {
            let saving = null;
            // the menus have their own data, where $root and $refs are theirs
            let root = null;

            return {
                // seconds up to now, null for a fixed range
                last: range.last ?? null,
                options: { ...options, ...view },
                // features: the options that change the graph shown
                state: { loading: interactive, refreshing: false, zoomed: false, features: { mirror: false, stacking: false } },
                showHelp: false,
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

                // Keep the line style explicit so even a fully default drawing can be shared.
                updateAddress() {
                    const url = new URL(window.location.href);
                    for (const key of config.addressKeys) {
                        const option = this.options[key];
                        const value = typeof option === 'boolean' ? Number(option) : option;
                        const omitDefault = key !== 'line_style' && option === config.defaults[key];
                        setParam(url.searchParams, key, omitDefault ? null : value);
                    }
                    setParam(url.searchParams, 'previous', COMPARE[this.options.compare]);
                    setParam(url.searchParams, 'trend', this.options.trend ? '1' : null);

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

                reset() {
                    this.graph()?.reset();
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

                // kept for the next graph, saved once changes pause
                saveOptions() {
                    clearTimeout(saving);
                    saving = setTimeout(() => {
                        const saved = Object.fromEntries(Object.keys(config.defaults).map((key) => [key, this.options[key]]));
                        window.axios.post(config.preferencesUrl, { pref: config.optionsPreference, value: saved })
                            .catch((error) => toastr.error(error.response?.data?.message ?? error.message));
                    }, SAVE_DELAY);
                },

                setOption(key, value) {
                    this.options[key] = value;
                    this.graph()?.setOptions({ [key]: value });
                    this.updateAddress();
                    if (key in config.defaults) {
                        this.saveOptions();
                    }
                },

                toggleColumn(column) {
                    const columns = this.options.columns.includes(column)
                        ? this.options.columns.filter((other) => other !== column)
                        : config.columns.filter((other) => other === column || this.options.columns.includes(other));
                    this.setOption('columns', columns);
                },

                resized({ width, height }) {
                    this.options.width = width;
                    this.options.height = height;
                    this.saveOptions();
                },

                autoSize() {
                    this.graph()?.setOptions({ width: null, height: null });
                    this.resized({ width: null, height: null });
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
                        const view = resolveRange(range);
                        const width = root.querySelector('[data-graph-image]').clientWidth || window.innerWidth;
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
