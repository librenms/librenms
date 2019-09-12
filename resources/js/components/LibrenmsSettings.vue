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
    <tabs>
        <template v-slot:header>
            <form class="form-inline">
                <div class="input-group">
                    <input id="settings-search" type="search" class="form-control" placeholder="Search Settings" v-model.trim="search_phrase">
                </div>
            </form>
        </template>
        <tab name="global"></tab>
        <tab v-for="(sections, tab) in groups" :key="tab" :name="tab" :selected="tab === active_tab">
            <accordion>
                <accordion-item v-for="(items, section) in groups[tab]" :key="section" :name="section">
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
        data() {
            return {
                active_tab: 'alerting',
                active_section: '',
                search_phrase: '',
                settings: {},
                groups: {}
            }
        },
        methods: {
            loadData(response) {
                this.settings = response.data;

                // populate layout data
                let groups = {};
                for (const key of Object.keys(this.settings)) {
                    let setting = this.settings[key];
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
                let sorted = {};
                Object.keys(groups).sort().forEach(function(key) {
                    sorted[key] = groups[key];
                });

                // set groups to trigger reactivity (also sort)
                this.groups = Object.keys(groups).sort().reduce((a, c) => (a[c] = groups[c], a), {});
            }
        },
        mounted() {
            axios.get(route('settings.list')).then(this.loadData)
        }
    }
</script>

<style scoped>
    #settings-search {
        border-radius: 4px
    }
</style>
