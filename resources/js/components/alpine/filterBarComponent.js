export default function filterBarComponent({ fields, reload = false }) {
    return {
        fields,
        reload,
        filters: [],
        showAdd: false,
        dialog: false,
        current: null,
        op: "",
        value: null,
        display: "", // Temporary holder for the label in the modal
        highlightedIndex: -1,
        lastFocusedElement: null,
        searchQuery: "",
        remoteOptions: [],
        isLoading: false,

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

        init() {
            this.restoreFromUrl();
            window.addEventListener("popstate", () => this.restoreFromUrl());
        },

        ops() {
            return this.OPS[this.current?.type] || this.OPS.text;
        },

        nullary(operator) {
            return ["is_empty", "is_not_empty"].includes(operator || this.op);
        },

        isActive(key) {
            return this.filters.some((f) => f.key === key);
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

        open(field) {
            this.lastFocusedElement = document.activeElement;
            this.current = null;

            this.$nextTick(() => {
                this.current = field;
                this.searchQuery = "";
                this.remoteOptions = [];
                this.highlightedIndex = -1;

                const existing = this.filters.find((f) => f.key === field.key);
                this.op = existing?.op || this.ops()[0].v;
                this.value =
                    existing?.value ??
                    (field.type === "multi-select" ? [] : "");
                this.display = existing?.display ?? this.value;

                this.dialog = true;
                this.showAdd = false;

                this.$nextTick(() => {
                    const el = field.endpoint
                        ? this.$refs.remoteSearch
                        : this.$refs.valInput;
                    el?.focus();
                });
            });
        },

        async fetchRemote() {
            if (!this.current?.endpoint || this.searchQuery.length < 2) {
                this.remoteOptions = [];
                return;
            }

            this.isLoading = true;
            try {
                const url = new URL(
                    this.current.endpoint,
                    window.location.origin
                );
                url.searchParams.append("term", this.searchQuery);

                if (this.current.params) {
                    Object.entries(this.current.params).forEach(([k, v]) => {
                        url.searchParams.append(k, v);
                    });
                }

                const response = await fetch(url);
                const data = await response.json();
                this.remoteOptions = data.results || data;
            } catch (e) {
                console.error("Fetch failed:", e);
            } finally {
                this.isLoading = false;
            }
        },

        apply() {
            if (!this.current) return;
            const isNullary = this.nullary();
            const isMulti = this.current.type === "multi-select";
            const hasVal = isMulti
                ? this.value.length > 0
                : this.value !== "" && this.value != null;
            if (!isNullary && !hasVal) return;

            const entry = {
                key: this.current.key,
                label: this.current.label,
                type: this.current.type,
                op: this.op,
                sym: this.ops().find((o) => o.v === this.op).s,
                value: isNullary
                    ? null
                    : isMulti
                    ? [...this.value]
                    : this.value,
                // Finalize the display text for the chip
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

        remove(key) {
            this.filters = this.filters.filter((f) => f.key !== key);
            this.syncUrl();
        },

        clearAll() {
            this.filters = [];
            this.syncUrl();
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

        close() {
            this.dialog = false;
            this.showAdd = false;
            this.$nextTick(() => this.lastFocusedElement?.focus());
        },

        syncUrl() {
            const url = new URL(window.location);
            const keysToDelete = [];
            const formatted = {};
            for (const key of url.searchParams.keys()) {
                if (key.startsWith("filter[")) keysToDelete.push(key);
            }
            keysToDelete.forEach((k) => url.searchParams.delete(k));

            this.filters.forEach((f) => {
                const val = Array.isArray(f.value)
                    ? f.value.join(",")
                    : f.value ?? "";
                url.searchParams.set(`filter[${f.key}][${f.op}]`, val);

                if (!formatted[f.key]) formatted[f.key] = {};
                formatted[f.key][f.op] = val;
            });
            window.history.pushState({}, "", url);

            if (this.reload) {
                window.location.href = url.href;
            } else {
                window.history.pushState({}, "", url);

                this.$dispatch("filter:apply", {
                    filters: this.filters,
                    formatted: formatted,
                });
            }
        },

        restoreFromUrl() {
            const params = new URLSearchParams(window.location.search);
            const newFilters = [];
            for (const [fullKey, val] of params.entries()) {
                const match = fullKey.match(/^filter\[(.+)\]\[(.+)\]$/);
                if (match) {
                    const [_, key, op] = match;
                    const field = this.fields.find((f) => f.key === key);
                    if (field) {
                        const opObj = (
                            this.OPS[field.type] || this.OPS.text
                        ).find((o) => o.v === op);
                        let finalVal = val === "" ? null : val;
                        if (field.type === "multi-select" && finalVal)
                            finalVal = finalVal.split(",");

                        newFilters.push({
                            key,
                            label: field.label,
                            type: field.type,
                            op,
                            sym: opObj?.s || op,
                            value: finalVal,
                            display: "...", // Loading state
                        });
                    }
                }
            }
            this.filters = newFilters;
            this.filters.forEach((f) => this.hydrate(f));
        },

        getNormalizedOptions() {
            const options = this.current?.options;
            if (!options) return [];

            if (Array.isArray(options)) {
                return options.map((o) => ({ value: o, label: o }));
            }

            return Object.keys(options).map((key) => ({
                value: key,
                label: options[key],
            }));
        },

        async hydrate(filter) {
            const field = this.fields.find((f) => f.key === filter.key);

            if (field?.options) {
                const options = field.options;
                const isMap = !Array.isArray(options);

                if (
                    field.type === "multi-select" &&
                    Array.isArray(filter.value)
                ) {
                    filter.display = filter.value.map((v) =>
                        isMap ? options[v] || v : v
                    );
                } else {
                    // Single select lookup
                    filter.display = isMap
                        ? options[filter.value] || filter.value
                        : filter.value;
                }
                return;
            }

            if (field?.type === "boolean") {
                filter.display = filter.value == 1 ? "Yes" : "No";
                return;
            }

            if (field?.endpoint && filter.value && !this.nullary(filter.op)) {
                try {
                    const url = new URL(field.endpoint, window.location.origin);
                    url.searchParams.append(
                        "id",
                        Array.isArray(filter.value)
                            ? filter.value.join(",")
                            : filter.value
                    );
                    if (field.params)
                        Object.entries(field.params).forEach(([k, v]) =>
                            url.searchParams.append(k, v)
                        );

                    const response = await fetch(url);
                    const data = await response.json();
                    const results = data.data || data.results || data;

                    if (field.type === "multi-select") {
                        filter.display = results.map((r) => r.text || r);
                    } else {
                        const match = results[0];
                        filter.display = match
                            ? match.text || match
                            : filter.value;
                    }
                } catch (e) {
                    filter.display = filter.value;
                }
                return;
            }

            filter.display = filter.value;
        },
    };
}
