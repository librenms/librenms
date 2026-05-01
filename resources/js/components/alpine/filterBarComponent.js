export default function filterBarComponent({
    name,
    fields,
    reload = false,
    initial = [],
}) {
    return {
        name,
        fields,
        reload,
        initial,

        // --- Static Configuration ---
        OPS: {
            text: [
                { v: "contains", s: "~", l: "Contains" },
                { v: "not_contains", s: "!~", l: "Not Contains" },
                { v: "eq", s: "=", l: "Equals" },
                { v: "neq", s: "≠", l: "Not Equals" },
                { v: "starts_with", s: "^", l: "Starts With" },
                { v: "ends_with", s: "$", l: "Ends With" },
                { v: "is_empty", s: "∅", l: "Is Empty" },
                { v: "is_not_empty", s: "∃", l: "Is Not Empty" },
            ],
            number: [
                { v: "eq", s: "=", l: "Equals" },
                { v: "neq", s: "≠", l: "Not Equals" },
                { v: "gt", s: ">", l: "Greater Than" },
                { v: "gte", s: "≥", l: "Greater or Equal" },
                { v: "lt", s: "<", l: "Less Than" },
                { v: "lte", s: "≤", l: "Less or Equal" },
                { v: "is_empty", s: "∅", l: "Is Empty" },
                { v: "is_not_empty", s: "∃", l: "Is Not Empty" },
            ],
            boolean: [{ v: "eq", s: "is", l: "Is" }],
            date: [
                { v: "on", s: "=", l: "On Date" },
                { v: "before", s: "<", l: "Before" },
                { v: "after", s: ">", l: "After" },
                { v: "is_empty", s: "∅", l: "Is Empty" },
            ],
            select: [
                { v: "eq", s: "=", l: "Is" },
                { v: "neq", s: "≠", l: "Is Not" },
            ],
            "multi-select": [
                { v: "in", s: "∈", l: "Is Any Of" },
                { v: "not_in", s: "∉", l: "Is Not Any Of" },
            ],
        },

        // --- State ---
        filters: [],
        showAdd: false,
        showOptions: false,
        dialog: false,
        current: null,

        // Mapping HTML refs to these legacy property names to ensure HTML compatibility
        op: "",
        value: null,
        display: "",
        highlightedIndex: -1,
        lastFocusedElement: null,
        searchQuery: "",
        remoteOptions: [],
        isLoading: false,

        // --- Initialization ---
        async init() {
            const params = new URLSearchParams(window.location.search);
            const hasUrlFilters = Array.from(params.keys()).some((k) =>
                k.startsWith("filter[")
            );
            const sessionData = sessionStorage.getItem(`filter-cache-${this.name}`);

            if (hasUrlFilters) {
                await this.restoreFromUrl(params);
            } else if (sessionData !== null) {
                this.filters = JSON.parse(sessionData);
                await this.hydrateAll();
            } else if (Object.keys(this.initial || {}).length > 0) {
                await this.restoreFromData(this.initial);
            }

            // Sync external links on load
            this.syncPageUrls();

            this.$dispatch("filter:loaded", {
                filters: this.filters,
                source: hasUrlFilters ? "url" : "initial",
            });

            window.addEventListener("popstate", () =>
                this.restoreFromUrl(new URLSearchParams(window.location.search))
            );
        },

        // --- Data Restoration ---
        async restoreFromUrl(params) {
            const newFilters = [];
            for (const [fullKey, val] of params.entries()) {
                const match = fullKey.match(/^filter\[(.+)\]\[(.+)\]$/);
                if (match) {
                    const [_, key, op] = match;
                    const field = this.fields.find((f) => f.key === key);
                    if (field)
                        newFilters.push(
                            this.createFilterObject(field, op, val)
                        );
                }
            }
            this.filters = newFilters;
            await this.hydrateAll();
        },

        async restoreFromData(data) {
            const newFilters = [];
            Object.entries(data).forEach(([key, ops]) => {
                const field = this.fields.find((f) => f.key === key);
                if (field) {
                    Object.entries(ops).forEach(([op, val]) => {
                        newFilters.push(
                            this.createFilterObject(field, op, val)
                        );
                    });
                }
            });
            this.filters = newFilters;
            await this.hydrateAll();
        },

        createFilterObject(field, op, val) {
            const ops = this.OPS[field.type] || this.OPS.text;
            const opObj = ops.find((o) => o.v === op);
            return {
                key: field.key,
                label: field.label,
                type: field.type,
                op,
                sym: opObj?.s || op,
                value: this.decodeValue(field.type, val),
                display: "...",
            };
        },

        // --- Syncing & Persistence ---
        getFormattedFilters() {
            const formatted = {};
            this.filters.forEach((f) => {
                if (!formatted[f.key]) formatted[f.key] = {};
                formatted[f.key][f.op] = this.encodeValue(f.value);
            });
            return formatted;
        },

        applyFiltersToUrl(url) {
            [...url.searchParams.keys()]
                .filter((k) => k.startsWith("filter["))
                .forEach((k) => url.searchParams.delete(k));

            this.filters.forEach((f) => {
                url.searchParams.set(
                    `filter[${f.key}][${f.op}]`,
                    this.encodeValue(f.value)
                );
            });

            return url;
        },

        syncUrl() {
            const url = this.applyFiltersToUrl(new URL(window.location));
            sessionStorage.setItem(`filter-cache-${this.name}`, JSON.stringify(this.filters));
            if (this.reload) {
                window.location.href = url.href;
            } else {
                window.history.pushState({}, "", url);
                this.syncPageUrls();
                this.$dispatch("filter:apply", {
                    filters: this.getFormattedFilters(),
                });
            }
        },

        syncPageUrls() {
            document.querySelectorAll("a.sync-filter-url").forEach((el) => {
                const url = this.applyFiltersToUrl(
                    new URL(el.getAttribute("href"), window.location.origin)
                );
                el.setAttribute("href", url.href);
            });
        },

        async savePreferences() {
            try {
                await fetch(route("preferences.update", "filters"), {
                    method: "PUT",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]'
                        ).content,
                    },
                    body: JSON.stringify({
                        name: this.name,
                        filters: this.getFormattedFilters(),
                    }),
                });
                this.showOptions = false;
            } catch (e) {
                console.error("Failed to save preferences", e);
            }
        },

        // --- Helpers ---
        decodeValue: (type, val) =>
            val === "" || val === null
                ? null
                : type === "multi-select" && typeof val === "string"
                ? val.split(",")
                : val,
        encodeValue: (val) => (Array.isArray(val) ? val.join(",") : val ?? ""),
        nullary(operator) {
            return ["is_empty", "is_not_empty"].includes(operator || this.op);
        },
        isEmpty(val) {
            return Array.isArray(val)
                ? val.length === 0
                : val === "" || val == null;
        },
        isActive(key) {
            return this.filters.some((f) => f.key === key);
        },
        ops() {
            return this.OPS[this.current?.type] || this.OPS.text;
        },
        toggleOptions() {
            this.showAdd = false;
            this.showOptions = !this.showOptions;
        },
        toggleAdd() {
            this.showOptions = false;
            this.showAdd = !this.showAdd;
        },
        handleRemoteSelect(event) {
            const { id, text } = event.detail;
            if (this.current.type === "multi-select") {
                this.toggleMulti(id, text);
            } else {
                this.value = id;
                this.display = text;
                this.apply();
            }
        },
        selectOption(value, label) {
            if (this.current.type === "multi-select") {
                this.toggleMulti(value, label);
            } else {
                this.value = value;
                this.display = label;
                this.apply();
            }
        },
        setBoolean(val, label) {
            this.value = val;
            this.display = label;
            this.apply();
        },

        async hydrateAll() {
            await Promise.all(this.filters.map((f) => this.hydrate(f)));
        },

        async hydrate(filter) {
            const field = this.fields.find((f) => f.key === filter.key);
            if (!field || this.nullary(filter.op)) {
                filter.display = "";
                return;
            }

            if (field.options) {
                const isMap = !Array.isArray(field.options);
                const lookup = (v) => (isMap ? field.options[v] || v : v);
                filter.display = Array.isArray(filter.value)
                    ? filter.value.map(lookup)
                    : lookup(filter.value);
            } else if (field.endpoint && filter.value) {
                try {
                    const url = new URL(field.endpoint, window.location.origin);
                    url.searchParams.append(
                        "id",
                        this.encodeValue(filter.value)
                    );
                    if (field.params)
                        Object.entries(field.params).forEach(([k, v]) =>
                            url.searchParams.append(k, v)
                        );
                    const res = await fetch(url);
                    const data = await res.json();
                    const results = data.data || data.results || data;
                    filter.display =
                        field.type === "multi-select"
                            ? results.map((r) => r.text || r)
                            : results[0]?.text || results[0] || filter.value;
                } catch {
                    filter.display = filter.value;
                }
            } else {
                filter.display = filter.value;
            }
        },

        // --- UI Actions ---
        open(field) {
            this.lastFocusedElement = document.activeElement;
            const existing = this.filters.find((f) => f.key === field.key);

            this.current = field;
            this.op = existing?.op || this.ops()[0].v;
            this.value =
                existing?.value ?? (field.type === "multi-select" ? [] : "");
            this.display = existing?.display ?? this.value;
            this.searchQuery = "";
            this.remoteOptions = [];
            this.highlightedIndex = -1;

            this.dialog = true;
            this.showAdd = false;
            this.showOptions = false;
        },

        apply() {
            if (!this.current) return;
            const isNullary = this.nullary();
            if (!isNullary && this.isEmpty(this.value)) return;

            const entry = {
                key: this.current.key,
                label: this.current.label,
                type: this.current.type,
                op: this.op,
                sym: this.ops().find((o) => o.v === this.op).s,
                value: isNullary
                    ? null
                    : Array.isArray(this.value)
                    ? [...this.value]
                    : this.value,
                display: isNullary
                    ? ""
                    : this.current.type === "boolean"
                    ? this.value == 1
                        ? "Yes"
                        : "No"
                    : this.display || this.value,
            };

            const i = this.filters.findIndex((f) => f.key === entry.key);
            i >= 0
                ? this.filters.splice(i, 1, entry)
                : this.filters.push(entry);
            this.syncUrl();
            this.close();
        },

        toggleMulti(optValue, optLabel) {
            if (!Array.isArray(this.value)) this.value = [];
            if (!Array.isArray(this.display)) this.display = [];

            const i = this.value.indexOf(optValue);
            if (i > -1) {
                this.value.splice(i, 1);
                this.display.splice(i, 1);
            } else {
                this.value.push(optValue);
                this.display.push(optLabel);
            }
        },

        remove(key) {
            this.filters = this.filters.filter((f) => f.key !== key);
            this.syncUrl();
        },

        clearAll() {
            this.filters = [];
            this.syncUrl();
            this.showOptions = false;
        },

        close() {
            this.dialog = false;
            this.showAdd = false;
            this.showOptions = false;
            this.$nextTick(() => this.lastFocusedElement?.focus());
        },

        getNormalizedOptions() {
            const options = this.current?.options;
            if (!options) return [];
            if (Array.isArray(options))
                return options.map((o) => ({ value: o, label: o }));
            return Object.keys(options).map((key) => ({
                value: key,
                label: options[key],
            }));
        },

        navDropdown(dir) {
            if (!this.showAdd) return;
            if (dir === "next")
                this.highlightedIndex =
                    (this.highlightedIndex + 1) % this.fields.length;
            if (dir === "prev")
                this.highlightedIndex =
                    (this.highlightedIndex - 1 + this.fields.length) %
                    this.fields.length;
        },
    };
}
