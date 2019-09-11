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
        <tab v-for="(groups, tab) in sections" :key="tab" :name="tab" :selected="tab === active_tab">
            <accordion>
                <accordion-item v-for="group in groups" :name="group">
                    <ul>
                        <li v-for="setting in getSectionSettings(tab, group)">{{ setting.name }}</li>
                    </ul>
                </accordion-item>
            </accordion>
        </tab>
    </tabs>
</template>

<script>
    export default {
        name: "LibrenmsSettings",
        props: ['sections'],
        data: function () {
            return {
                active_tab: 'alerting',
                active_section: '',
                search_phrase: '',
                settings: {}
            }
        },
        methods: {
            getSectionSettings: function (group, section) {
                let settings = this.settings;
                return Object.keys(settings).reduce(function (acc, val) {
                    return (settings[val]['group'] !== group && settings[val]['section'] !== section) ? acc : {
                        ...acc,
                        [val]: settings[val]
                    };
                }, {});

            }
        },
        mounted() {
            axios.get(route('settings.list'))
                .then(response => (this.settings = response.data))
        }
    }
</script>

<style scoped>
    #settings-search {
        border-radius: 4px
    }
</style>
