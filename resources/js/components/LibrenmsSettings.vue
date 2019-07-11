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
    <div class="panel with-nav-tabs panel-default">
        <div class="panel-heading">
            <ul class="nav nav-tabs settings-group-tabs">
                <li v-for="tab in tabs" :active="tab === active_tab" :class="{ active: active_tab === tab }">
                    <a v-on:click="active_tab = tab">{{ tab }}</a>
                </li>

                <li class="pull-right">
                    <form class="form-inline">
                        <div class="input-group">
                            <input v-model="search_phrase" type="search" class="form-control" placeholder="Search Settings">
                        </div>
                    </form>
                </li>
            </ul>
        </div>
        <div class="panel-body">
            <div class="tab-content">
                <div v-for="tab in tabs" :class="[{ active: tab === active_tab }, 'tab-pane', 'fade']">
                    <div v-for="section in sections[tab]" class="panel-group">
                        <div v-if="active_tab === 'global'">
                            <p v-for="setting in this.settings"><b>{{ setting.name }}</b> = {{ JSON.stringify(setting.value) }}</p>
                        </div>
                        <div v-else>
                            <div v-for="section in sections[tab]" class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a data-toggle="collapse" :href="'#' + tab + '-' + section" v-on:click="active_section = section">
                                            <i class="fa fa-caret-down"></i> {{ tab + '.' + section }}
                                        </a>
                                    </h4>
                                </div>
                                <div :class="['panel-collapse', 'collapse', { active: tab === active_tab && section === active_section }]">
                                    <div class="panel-body">
                                        <form class="form-horizontal section-form" role="form">
                                            <div v-for="setting in getSectionSettings(tab, section)">
                                                {{ setting.name }}
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
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
        computed: {
            tabs: function () {
                return this.sections ? Object.keys(this.sections) : []
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
            axios
                .get(route('settings.list'))
                .then(response => (this.settings = response.data))
        }
    }
</script>

<style scoped>
    #settings-search {
        border-radius: 4px
    }
</style>
