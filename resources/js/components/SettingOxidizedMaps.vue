<!--
  - SettingOxidizedMaps.vue
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
  - @copyright  2021 Tony Murray
  - @author     Tony Murray <murraytony@gmail.com>
  -->

<template>
    <div>
        <div class="new-btn-div" v-show="! disabled">
            <button type="button" class="btn btn-primary"><i class="fa fa-plus"></i> {{ $t('New Map') }}</button>
        </div>
        <template v-for="(targetValues, target) in value">
            <template v-for="(sourceValues, source) in targetValues">
                <div class="panel panel-default" v-for="(match, index) in sourceValues">
                    <div class="panel-body">
                        <div class="col-md-5 cell">{{ formatSource(source, match) }}</div>
                        <div class="col-md-4 cell">{{ formatTarget(target, match) }}</div>
                        <div class="col-md-3 buttons">
                            <div class="btn-group" v-tooltip="disabled ? $t('settings.readonly') : false">
                                <button type="button" class="btn btn-sm btn-info" v-tooltip="$t('Edit')" :disabled="disabled"><i class="fa fa-lg fa-edit"></i></button>
                                <button type="button" class="btn btn-sm btn-danger" v-tooltip="$t('Delete')" :disabled="disabled"><i class="fa fa-lg fa-remove"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </template>
    </div>
</template>

<script>
import BaseSetting from "./BaseSetting";

export default {
    name: "SettingOxidizedMaps",
    mixins: [BaseSetting],
    methods: {
        formatSource(source, matches) {
            if (matches.hasOwnProperty('regex')) {
                return source + ' ~ ' + matches.regex;
            }

            if (matches.hasOwnProperty('match')) {
                return source + ' = ' + matches.match;
            }

            return 'invalid';
        },
        formatTarget(target, matches) {
            let value = matches.hasOwnProperty('value') ? matches.value : matches[target];
            return target + ' > ' + value;
        }
    }
}
</script>

<style scoped>
.cell {
    white-space: nowrap;
    padding: 5px;
    height: 30px;
}

.buttons {
    white-space: nowrap;
    padding: 0 5px;
}

.new-btn-div {
    margin-bottom: 5px;
}

.panel-body {
    padding: 5px 0;
}
</style>
