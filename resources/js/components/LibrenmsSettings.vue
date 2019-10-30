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
            <form class="form-inline" @submit.prevent>
                <div class="input-group">
                    <input id="settings-search" type="search" class="form-control" placeholder="Filter Settings" v-model.trim="search_phrase">
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
            groups: {type: Object}
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
            },
            updateSetting(name, value) {
                this.$set(this.settings[name], 'value', value)
            },
            settingShown(setting_name) {
                let setting = this.settings[setting_name];

                if (setting.when === null) {
                    return true;
                }

                switch (setting.when.operator) {
                    case 'equals':
                        return this.settings[setting.when.setting].value === setting.when.value;
                    case 'in':
                        return setting.when.value.includes(this.settings[setting.when.setting].value);
                    default:
                        return true;
                }
            }
        },
        mounted() {
            window.onpopstate = this.handleBack; // handle back button
            axios.get(route('settings.list')).then((response) => this.settings = response.data)
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
