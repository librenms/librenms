import debounce from "lodash/debounce";
import isEqual from "lodash/isEqual";

const clone = (value) => (value === undefined ? undefined : JSON.parse(JSON.stringify(value)));

const KNOWN_TYPES = [
    "array",
    "array-sub-keyed",
    "boolean",
    "color",
    "directory",
    "email",
    "executable",
    "float",
    "group-role-map",
    "integer",
    "multiple",
    "oxidized-maps",
    "password",
    "password-array",
    "select",
    "select-dynamic",
    "snmp3auth",
    "text",
];

// types that persist immediately instead of waiting for the user to stop typing
const IMMEDIATE_TYPES = ["select", "select-dynamic", "boolean", "multiple"];

// types that keep what the user typed when the server rejects the value
const KEEP_ON_ERROR_TYPES = ["text", "email", "password"];

/**
 * The global settings page: tabs, sections, search filter and conditional display
 */
export function settingsPage({ prefix, tab, section, groups, settings }) {
    return {
        prefix,
        tab,
        section: section || null,
        groups,
        settings,
        search: "",

        init() {
            if (!this.section && window.location.hash) {
                this.section = window.location.hash.substring(1);
            }

            // so the back button can return to the initial tab/section
            window.history.replaceState(this.section ? this.tab + "/" + this.section : this.tab, "");

            window.addEventListener("popstate", (event) => {
                if (typeof event.state === "string") {
                    const [tab, section] = event.state.split("/");
                    this.tab = tab;
                    this.section = section || null;
                }
            });
        },

        // fall back to the first matching tab when the search filters out the selected one
        get displayTab() {
            if (this.groupVisible(this.groups.find((group) => group.name === this.tab))) {
                return this.tab;
            }

            return this.groups.find((group) => this.groupVisible(group))?.name ?? this.tab;
        },

        changeTab(tab) {
            if (this.tab !== tab) {
                this.tab = tab;
                this.section = null;
                this.updateUrl();
            }
        },

        toggleSection(section) {
            this.tab = this.displayTab;
            this.section = this.section === section ? null : section;
            this.updateUrl();
        },

        updateUrl() {
            const slug = this.section ? this.tab + "/" + this.section : this.tab;
            window.history.pushState(slug, "", this.prefix + "/" + slug);
        },

        matches(name) {
            const search = this.search.trim().toLowerCase();
            if (!search) {
                return true;
            }

            const setting = this.settings[name];
            return name.toLowerCase().includes(search) || setting?.description?.toLowerCase().includes(search);
        },

        sectionSettings(section) {
            return section.settings.filter((name) => this.matches(name));
        },

        groupVisible(group) {
            return group !== undefined && group.sections.some((section) => this.sectionSettings(section).length > 0);
        },

        settingShown(name) {
            const when = this.settings[name]?.when;

            if (!when) {
                return true;
            }

            if (Object.hasOwn(when, "and")) {
                return when.and.every((logic) => this.checkLogic(logic));
            }

            if (Object.hasOwn(when, "or")) {
                return when.or.some((logic) => this.checkLogic(logic));
            }

            return this.checkLogic(when);
        },

        checkLogic(logic) {
            const value = this.settings[logic.setting]?.value;

            switch (logic.operator) {
                case "equals":
                    return value === logic.value;
                case "in":
                    return logic.value.includes(value);
                default:
                    return true;
            }
        },
    };
}

/**
 * A single setting row, handles persisting and resetting the value.
 * Type specific components nested inside read `value` and call changeValue()
 */
export function librenmsSetting(setting, { prefix = "settings", id = null } = {}) {
    return {
        setting,
        routePrefix: prefix,
        routeId: id,
        value: clone(setting.value),
        updateStatus: "none",
        feedback: "",
        feedbackTimeout: null,
        debouncedPersist: null,

        init() {
            this.debouncedPersist = debounce((value) => this.persistValue(value), 500);
        },

        destroy() {
            this.debouncedPersist?.flush();
        },

        get inputId() {
            return "setting-" + (this.routeId === null ? "" : this.routeId + "-") + this.setting.name;
        },

        get knownType() {
            return KNOWN_TYPES.includes(this.setting.type);
        },

        get showResetToDefault() {
            return !this.setting.overridden && !isEqual(this.value, this.setting.default);
        },

        get showUndo() {
            return !isEqual(this.setting.value, this.value);
        },

        routeParams() {
            return this.routeId === null ? [this.setting.name] : [this.routeId, this.setting.name];
        },

        changeValue(value) {
            this.value = value;

            if (IMMEDIATE_TYPES.includes(this.setting.type)) {
                this.persistValue(value);
            } else {
                this.debouncedPersist(value);
            }
        },

        persistValue(value) {
            this.updateStatus = "pending";

            axios
                .put(route(this.routePrefix + ".update", this.routeParams()), { value: value })
                .then((response) => {
                    this.value = response.data.value;
                    this.setting.value = clone(response.data.value);
                    this.updateStatus = "success";
                    this.showFeedback("has-success");
                })
                .catch((error) => {
                    this.updateStatus = "error";
                    this.showFeedback("has-error", !KEEP_ON_ERROR_TYPES.includes(this.setting.type));
                    toastr.error(this.errorMessage(error));

                    // don't reset certain types back to actual value on error
                    if (!KEEP_ON_ERROR_TYPES.includes(this.setting.type) && error.response?.data) {
                        this.value = error.response.data.value;
                        this.setting.value = clone(error.response.data.value);
                    }
                });
        },

        resetToDefault() {
            this.debouncedPersist.cancel();

            axios
                .delete(route(this.routePrefix + ".destroy", this.routeParams()))
                .then((response) => {
                    this.value = response.data.value;
                    this.setting.value = clone(response.data.value);
                    this.showFeedback("has-success");
                })
                .catch((error) => {
                    this.showFeedback("has-error");
                    toastr.error(this.errorMessage(error));
                });
        },

        resetToInitial() {
            this.changeValue(clone(this.setting.value));
        },

        showFeedback(feedback, clear = true) {
            clearTimeout(this.feedbackTimeout);
            this.feedback = feedback;

            if (clear) {
                this.feedbackTimeout = setTimeout(() => (this.feedback = ""), 3000);
            }
        },

        errorMessage(error) {
            const span = document.createElement("span");
            span.textContent = error.response?.data?.message ?? error.response?.data?.error ?? error.message;
            return span;
        },

        parseNumber(number) {
            const value = parseFloat(number);
            return isNaN(value) ? number : value;
        },
    };
}

/**
 * select2 wrapper.  Set the value with setValue() and listen for the select2-change event.
 */
export function librenmsSelect({
    route: routeName = null,
    options = [],
    multiple = false,
    placeholder = "",
    allowClear = true,
    allowEmpty = true,
    width = "auto",
} = {}) {
    return {
        init() {
            const select = this.$el.querySelector("select");
            const $select = $(select);

            options.forEach((option) => select.append(new Option(option.text, option.value)));

            const config = {
                theme: "bootstrap",
                dropdownAutoWidth: true,
                width: width,
                allowClear: Boolean(allowClear),
                placeholder: placeholder ?? "",
                multiple: multiple,
            };

            if (routeName) {
                config.ajax = {
                    url: route(routeName).toString(),
                    delay: 250,
                    cache: true,
                };
            }

            $select
                .select2(config)
                .on("select2:unselecting", (event) => {
                    if (!allowEmpty && multiple && ($select.val() ?? []).length <= 1) {
                        event.preventDefault();
                    }
                })
                .on("select2:select select2:unselect select2:clear", () => {
                    this.$dispatch("select2-change", $select.val() ?? (multiple ? [] : ""));
                });
        },

        destroy() {
            const $select = $(this.$el.querySelector("select"));
            if ($select.data("select2")) {
                $select.select2("destroy");
            }
        },

        setValue(value) {
            const $select = $(this.$el.querySelector("select"));
            const values = (Array.isArray(value) ? value : [value])
                .filter((item) => item !== "" && item !== null && item !== undefined)
                .map(String);

            // fetch the text for any selected values that we don't have options for yet
            const existing = $select.find("option").map((index, el) => el.value).get();
            const missing = values.filter((item) => !existing.includes(item));
            if (routeName && missing.length) {
                axios.get(route(routeName), { params: { id: missing.join(",") } }).then((response) => {
                    response.data.results.forEach((item) => {
                        if (missing.includes(String(item.id))) {
                            $select.append(new Option(item.text, item.id));
                        }
                    });
                    $select.val(values).trigger("change.select2");
                });
            }

            $select.val(values).trigger("change.select2");
        },
    };
}

/**
 * Sortable list of strings (array and password-array types)
 */
export function settingArray() {
    return {
        newItem: "",
        newItemVisible: false,
        visibleItems: {},
        renderKey: 0,

        get items() {
            return Array.isArray(this.value) ? this.value : Object.values(this.value ?? {});
        },

        addItem() {
            if (this.setting.overridden) return;
            this.changeValue([...this.items, this.newItem]);
            this.newItem = "";
        },

        removeItem(index) {
            if (this.setting.overridden) return;
            const items = [...this.items];
            items.splice(index, 1);
            this.changeValue(items);
        },

        updateItem(index, value) {
            if (this.setting.overridden || this.items[index] === value) return;
            const items = [...this.items];
            items[index] = value;
            this.changeValue(items);
        },

        moveItem(from, to) {
            if (this.setting.overridden) return;
            const items = [...this.items];
            items.splice(to, 0, ...items.splice(from, 1));
            this.renderKey++; // the DOM was changed by the sort, so render fresh
            this.changeValue(items);
        },

        toggleVisibility(index) {
            this.visibleItems[index] = !this.visibleItems[index];
        },
    };
}

/**
 * Nested key/value lists: {name: {key: value}}
 */
export function settingArraySubKeyed() {
    return {
        newSubItemKey: {},
        newSubItemValue: {},
        newSubArray: "",

        get subGroups() {
            return this.value && !Array.isArray(this.value) ? this.value : {};
        },

        update(callback) {
            if (this.setting.overridden) return;
            const groups = clone(this.subGroups);
            callback(groups);
            this.changeValue(groups);
        },

        addSubItem(index) {
            const key = this.newSubItemKey[index] ?? "";
            const value = this.newSubItemValue[index] ?? "";
            this.update((groups) => {
                if (Array.isArray(groups[index])) {
                    groups[index] = Object.assign({}, groups[index]);
                }
                groups[index][key] = value;
            });
            this.newSubItemKey[index] = "";
            this.newSubItemValue[index] = "";
        },

        removeSubItem(index, subindex) {
            this.update((groups) => {
                delete groups[index][subindex];
                if (Object.keys(groups[index]).length === 0) {
                    delete groups[index];
                }
            });
        },

        updateSubItem(index, subindex, value) {
            if (this.subGroups[index][subindex] === value) return;
            this.update((groups) => (groups[index][subindex] = value));
        },

        addSubArray() {
            this.update((groups) => (groups[this.newSubArray] = {}));
            this.newSubArray = "";
        },
    };
}

/**
 * Map of group names to roles: {group: {roles: []}}
 */
export function settingGroupRoleMap() {
    const levels = {
        1: "user",
        5: "global-read",
        10: "admin",
    };

    return {
        newItem: "",
        newItemRoles: [],

        get roleGroups() {
            // empty lists come through as an array
            if (!this.value || Array.isArray(this.value)) {
                return {};
            }

            // convert legacy levels to roles
            const groups = {};
            for (const [group, data] of Object.entries(this.value)) {
                groups[group] =
                    !Object.hasOwn(data, "roles") && Object.hasOwn(data, "level")
                        ? { roles: levels[data.level] ? [levels[data.level]] : [] }
                        : data;
            }

            return groups;
        },

        addItem() {
            const groups = clone(this.roleGroups);
            groups[this.newItem] = { roles: [...this.newItemRoles] };
            this.newItem = "";
            this.newItemRoles = [];
            this.changeValue(groups);
        },

        removeItem(group) {
            const groups = clone(this.roleGroups);
            delete groups[group];
            this.changeValue(groups);
        },

        renameItem(oldName, newName) {
            if (oldName === newName) return;

            // keep the order
            const groups = {};
            for (const [group, data] of Object.entries(clone(this.roleGroups))) {
                groups[group === oldName ? newName : group] = data;
            }
            this.changeValue(groups);
        },

        updateRoles(group, roles) {
            const groups = clone(this.roleGroups);
            groups[group].roles = roles;
            this.changeValue(groups);
        },
    };
}

/**
 * Oxidized maps: {target: {source: [{match|regex: x, value: y}]}}
 */
export function settingOxidizedMaps() {
    return {
        mapModal: false,
        mapModalIndex: null,
        mapModalSource: null,
        mapModalMatchType: null,
        mapModalMatchValue: null,
        mapModalTarget: null,
        mapModalReplacement: null,

        init() {
            this.$watch("updateStatus", (status) => {
                if (status === "success") {
                    this.mapModal = false;
                }
            });
        },

        get maps() {
            const maps = [];
            const value = this.value && !Array.isArray(this.value) ? this.value : {};

            Object.keys(value).forEach((target) => {
                Object.keys(value[target]).forEach((source) => {
                    value[target][source].forEach((match) => {
                        const type = Object.hasOwn(match, "regex") ? "regex" : "match";
                        maps.push({
                            target: target,
                            source: source,
                            matchType: type,
                            matchValue: match[type],
                            replacement: Object.hasOwn(match, "value") ? match.value : match[target],
                        });
                    });
                });
            });

            return maps;
        },

        showModal(index) {
            const map = index === null ? null : this.maps[index];
            this.mapModalIndex = index;
            this.mapModalSource = map?.source ?? "hostname";
            this.mapModalMatchType = map?.matchType ?? "match";
            this.mapModalMatchValue = map?.matchValue ?? "";
            this.mapModalTarget = map?.target ?? "os";
            this.mapModalReplacement = map?.replacement ?? "";
            this.mapModal = true;
        },

        submitModal() {
            const maps = this.maps;
            const map = {
                target: this.mapModalTarget,
                source: this.mapModalSource,
                matchType: this.mapModalMatchType,
                matchValue: this.mapModalMatchValue,
                replacement: this.mapModalReplacement,
            };

            if (this.mapModalIndex === null) {
                maps.push(map);
            } else {
                maps[this.mapModalIndex] = map;
            }

            this.updateMaps(maps);
        },

        deleteItem(index) {
            const maps = this.maps;
            maps.splice(index, 1);
            this.updateMaps(maps);
        },

        updateMaps(maps) {
            const value = {};
            maps.forEach((map) => {
                value[map.target] ??= {};
                value[map.target][map.source] ??= [];
                value[map.target][map.source].push({ [map.matchType]: map.matchValue, value: map.replacement });
            });
            this.changeValue(value);
        },
    };
}

/**
 * List of SNMPv3 credentials
 */
export function settingSnmp3auth() {
    return {
        authAlgorithms: ["MD5", "AES"],
        cryptoAlgorithms: ["AES", "DES"],
        visiblePasswords: {},
        renderKey: 0,

        init() {
            axios.get(route("snmp.capabilities")).then((response) => {
                this.authAlgorithms = response.data.auth;
                this.cryptoAlgorithms = response.data.crypto;
            });
        },

        get items() {
            return Array.isArray(this.value) ? this.value : [];
        },

        addItem() {
            this.changeValue([
                ...clone(this.items),
                {
                    authlevel: "noAuthNoPriv",
                    authalgo: "MD5",
                    authname: "",
                    authpass: "",
                    cryptoalgo: "AES",
                    cryptopass: "",
                },
            ]);
        },

        removeItem(index) {
            const items = clone(this.items);
            items.splice(index, 1);
            this.changeValue(items);
        },

        updateItem(index, key, value) {
            const items = clone(this.items);
            items[index][key] = value;
            this.changeValue(items);
        },

        moveItem(from, to) {
            const items = clone(this.items);
            items.splice(to, 0, ...items.splice(from, 1));
            this.renderKey++; // the DOM was changed by the sort, so render fresh
            this.changeValue(items);
        },

        togglePassword(key) {
            this.visiblePasswords[key] = !this.visiblePasswords[key];
        },
    };
}
