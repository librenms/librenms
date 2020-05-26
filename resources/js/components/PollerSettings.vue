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
  - along with this program.  If not, see <http://www.gnu.org/licenses/>.
  -
  - @package    LibreNMS
  - @link       http://librenms.org
  - @copyright  2020 Tony Murray
  - @author     Tony Murray <murraytony@gmail.com>
  -->

<template>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">{{ name }} <small>({{ node_id }})</small>
                <span class="pull-right">Advanced <toggle-button v-model="advanced"></toggle-button></span></h3>
        </div>
        <div class="panel-body">
            <template v-for="setting in settings">
                <div class="setting-container clearfix" v-if="!setting.advanced || advanced">
                    <librenms-setting
                        prefix="poller.settings"
                        :setting='setting'
                    ></librenms-setting>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
    export default {
        name: "PollerSettings",
        props: {
            'settings': Array,
            'name': String,
            'node_id': String
        },
        data() {
            return {
                advanced: false,
            }
        },
    }
</script>

<style scoped>
.setting-container {
    margin-bottom: 10px;
}
</style>
