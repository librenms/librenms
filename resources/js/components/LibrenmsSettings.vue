<!--
  - LibrenmsSettings.vue
  -
  - Description-
  -
  - This program is free software: you can redistribute it and/or modify
  - it under the terms of the GNU General Public License as published by
  - the Free Software Foundation, either version 3 of the License, or
  - (at your option) any later version.
  -
  - This program is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
  - GNU General Public License for more details.
  -
  - You should have received a copy of the GNU General Public License
  - along with this program.  If not, see <https://www.gnu.org/licenses/>.
  -
  - @package    LibreNMS
  - @link       https://www.librenms.org
  - @copyright  2019 Tony Murray
  - @author     Tony Murray <murraytony@gmail.com>
  -->

<template>
    <tabs @tab-selected="tabChanged" :selected="this.tab">
        <template v-slot:header>
            <form class="form-inline" @submit.prevent>
                <div class="input-group">
                    <input id="settings-search" type="search" class="form-control" :placeholder="$t('Filter Settings')" v-model.trim="search_phrase">
                </div>
            </form>
        </template>
        <tab name="global" :selected="'global' === tab" :text="$t('settings.groups.global')">
            <ul class="settings-list">
                <li v-for="setting in settings"><strong>{{ setting.name }}</strong> <code>{{ setting.value }}</code></li>
            </ul>
        </tab>
        <tab v-for="(sections, group) in groups" :key="group" :name="group" :selected="group === tab" :text="$t('settings.groups.' + group)">
            <accordion @expanded="sectionExpanded" @collapsed="sectionCollapsed">
                <accordion-item v-for="(items, item) in groups[group]" :key="item" :name="item" :text="$t('settings.sections.' + group + '.' + item)" :active="item === section">
                    <form class="form-horizontal" @submit.prevent>
                        <librenms-setting
                            v-for="setting in items"
                            :key="setting"
                            :setting="settings[setting]"
                            v-show="settingShown(setting)"
                            @setting-updated="updateSetting($event.name, $event.value)"
                        ></librenms-setting>
                    </form>
                </accordion-item>
            </accordion>
        </tab>
    </tabs>
</template>

<script>
    export default {
        name: "LibrenmsSettings",
        props: {
            prefix: String,
            initialTab: {type: String, default: 'alerting'},
            initialSection: String,
            tabs: {type: Array}
        },
        data() {
            return {
                tab: this.initialTab,
                section: this.initialSection,
                search_phrase: '',
                settings: {}
            }
        },
        methods: {
            tabChanged(group) {
                if (this.tab !== group) {
                    this.tab = group;
                    this.section = null;
                    this.updateUrl();
                }
            },
            sectionExpanded(section) {
                this.section = section;
                this.updateUrl()
            },
            sectionCollapsed(section) {
                if (this.section === section) { // don't do anything if section was changed instead of just closed
                    this.section = null;
                    this.updateUrl();
                }
            },
            updateUrl() {
                let slug = this.tab;
                if (this.section) {
                    slug += '/' + this.section
                }

                window.history.pushState(slug, '', this.prefix + '/' + slug)
            },
            handleBack(event) {
                [this.tab, this.section] = event.state.split('/');
            },
            updateSetting(name, value) {
                this.$set(this.settings[name], 'value', value)
            },
            settingShown(setting_name) {
                let setting = this.settings[setting_name];

                if (setting.when === null) {
                    return true;
                }

                if (setting.when.hasOwnProperty('and')) {
                    return setting.when.and.reduce((result, logic) => this.checkLogic(logic) && result, true)
                } else if (setting.when.hasOwnProperty('or')) {
                    return setting.when.or.reduce((result, logic) => this.checkLogic(logic) || result, false)
                }

                return this.checkLogic(setting.when);
            },
            translatedCompare(prefix, a, b) {
                return this.$t(prefix + a).localeCompare(this.$t(prefix + b))
            },
            checkLogic(logic) {
                switch (logic.operator) {
                    case 'equals':
                        return this.settings[logic.setting].value === logic.value;
                    case 'in':
                        return logic.value.includes(this.settings[logic.setting].value);
                    default:
                        return true;
                }
            }
        },
        mounted() {
            window.onpopstate = this.handleBack; // handle back button
            axios.get(route('settings.list')).then((response) => this.settings = response.data)
        },
        computed: {
            groups() {
                if (_.isEmpty(this.settings)) {
                    let sorted_tabs = {};
                    this.tabs.sort((a, b) => this.translatedCompare('settings.groups.', a, b)).forEach(function (tab) {
                        sorted_tabs[tab] = [];
                    });

                    return sorted_tabs;
                }

                // group data
                let groups = {};
                for (const key of Object.keys(this.settings)) {
                    let setting = this.settings[key];
                    // filter
                    if (!setting.name.includes(this.search_phrase)) {
                        continue
                    }
                    if (setting.group) {
                        if (!(setting.group in groups)) {
                            groups[setting.group] = {};
                        }
                        if (setting.section) {
                            if (!(setting.section in groups[setting.group])) {
                                groups[setting.group][setting.section] = [];
                            }

                            groups[setting.group][setting.section].push(setting);
                        }
                    }
                }

                // sort groups
                let sorted = {};
                Object.keys(groups).sort((a, b) => this.translatedCompare('settings.groups.', a, b)).forEach(group_key => {
                    sorted[group_key] = {};
                    Object.keys(groups[group_key]).sort((a, b) => this.translatedCompare('settings.sections.' + group_key + '.', a , b)).forEach(section_key => {
                        sorted[group_key][section_key] = _.sortBy(groups[group_key][section_key], 'order').map(a => a.name);
                    });
                });

                return sorted;
            }
        }
    }
</script>

<style scoped>
    #settings-search {
        border-radius: 4px
    }
    #settings-search::-webkit-search-cancel-button {
        -webkit-appearance: searchfield-cancel-button;
    }
    ul.settings-list {
        list-style-type: none;
    }
</style>
