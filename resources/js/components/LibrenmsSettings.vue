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
  - along with this program.  If not, see <http://www.gnu.org/licenses/>.
  -
  - @package    LibreNMS
  - @link       http://librenms.org
  - @copyright  2019 Tony Murray
  - @author     Tony Murray <murraytony@gmail.com>
  -->

<template>
    <tabs @tab-selected="tabChanged" :selected="this.tab">
        <template v-slot:header>
            <form class="form-inline">
                <div class="input-group">
                    <input id="settings-search" type="search" class="form-control" placeholder="Filter Settings" v-model.trim="search_phrase">
                </div>
            </form>
        </template>
        <tab name="global" :selected="'global' === tab">Global tab</tab>
        <tab v-for="(sections, group) in groups" :key="group" :name="group" :selected="group === tab">
            <accordion @expanded="sectionExpanded" @collapsed="sectionCollapsed">
                <accordion-item v-for="(items, item) in groups[group]" :key="item" :name="item" :active="item === section">
                    <form class="form-horizontal">
                        <librenms-setting v-for="setting in items" :key="setting" :setting="settings[setting]"></librenms-setting>
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
            initialSection: String
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
            loadData(response) {
                this.settings = response.data;
            }
        },
        mounted() {
            window.onpopstate = this.handleBack; // handle back button
            axios.get(route('settings.list')).then((response) => this.settings = response.data)
        },
        computed: {
            groups() {
                // populate layout data
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

                            // insert based on order
                            groups[setting.group][setting.section].splice(setting.order, 0, setting.name);
                        }
                    }
                }

                // sort groups
                return Object.keys(groups).sort().reduce((a, c) => (a[c] = groups[c], a), {});
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
</style>
