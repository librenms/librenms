<!--
  - PollerSettings.vue
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
  - @copyright  2020 Tony Murray
  - @author     Tony Murray <murraytony@gmail.com>
  -->

<template>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                {{ $t('Poller Settings') }}
                <span class="pull-right">Advanced <toggle-button v-model="advanced"></toggle-button></span>
            </h3>
        </div>
        <div class="panel-body">
            <vue-tabs direction="vertical" type="pills">
                <v-tab :title="poller.poller_name" v-for="(poller, id) in pollers" :key="id">
                    <div class="setting-container clearfix"
                         v-for="setting in settings[id]"
                         v-if="!setting.advanced || advanced"
                         :key="setting.name">
                        <librenms-setting
                            prefix="poller.settings"
                            :setting='setting'
                            :id="poller.id"
                        ></librenms-setting>
                    </div>
                </v-tab>
            </vue-tabs>
        </div>
    </div>
</template>

<script>
    export default {
        name: "PollerSettings",
        props: {
            'pollers': Object,
            'settings': Object
        },
        data() {
            return {
                advanced: false
            }
        },
    }
</script>

<style>
    .tab-content {
        width: 100%;
    }
</style>

<style scoped>
    .setting-container {
        margin-bottom: 10px;
    }
</style>
