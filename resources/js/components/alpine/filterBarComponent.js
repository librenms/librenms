export default function filterBarComponent({
    fields,
    reload = false,
    initial = [],
}) {
    return {
        fields,
        reload,
        initial,
        filters: [],
        showAdd: false,
        showOptions: false,
        dialog: false,
        current: null,
        op: "",
        value: null,
        display: "",
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

        async init() {
            const hasUrlFilters = Array.from(
                new URLSearchParams(window.location.search).keys()
            ).some((key) => key.startsWith("filter["));

            let source = "none";

            // URL state overrides Saved state
            if (hasUrlFilters) {
                await this.restoreFromUrl();
                source = "url";
            } else if (this.initial && Object.keys(this.initial).length > 0) {
                await this.restoreFromData(this.initial);
                source = "initial";
            }

            // Dispatch the final loaded state
            this.$dispatch("filter:loaded", {
                filters: this.filters,
                source: source,
            });

            window.addEventListener("popstate", () => this.restoreFromUrl());
        },

        async restoreFromUrl() {
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
                            display: "...",
                        });
                    }
                }
            }
            this.filters = newFilters;
            // Wait for all hydration fetches to complete
            await Promise.all(this.filters.map((f) => this.hydrate(f)));
        },

        async restoreFromData(data) {
            const newFilters = [];
            Object.entries(data).forEach(([key, ops]) => {
                const field = this.fields.find((f) => f.key === key);
                if (!field) return;

                Object.entries(ops).forEach(([op, val]) => {
                    const opObj = (this.OPS[field.type] || this.OPS.text).find(
                        (o) => o.v === op
                    );
                    let finalVal = val === "" ? null : val;
                    if (
                        field.type === "multi-select" &&
                        typeof finalVal === "string"
                    ) {
                        finalVal = finalVal.split(",");
                    }

                    newFilters.push({
                        key,
                        label: field.label,
                        type: field.type,
                        op,
                        sym: opObj?.s || op,
                        value: finalVal,
                        display: "...",
                    });
                });
            });

            this.filters = newFilters;
            // Wait for all hydration fetches to complete
            await Promise.all(this.filters.map((f) => this.hydrate(f)));
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
            this.showOptions = false;
            this.showAdd = false;

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
            });
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

        async savePreferences() {
            const formatted = {};
            this.filters.forEach((f) => {
                if (!formatted[f.key]) formatted[f.key] = {};
                formatted[f.key][f.op] = Array.isArray(f.value)
                    ? f.value.join(",")
                    : f.value;
            });

            try {
                await fetch("/preferences/filters", {
                    method: "PUT",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]'
                        ).content,
                    },
                    body: JSON.stringify({
                        table: 'device.ports', // FIXME
                        filters: formatted,
                    }),
                });
            } catch (e) {
                console.error("Failed to save preferences", e);
            }
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
            this.showOptions = false;
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
