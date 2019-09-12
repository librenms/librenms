<!--
  - SettingText.vue
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
    <div :class="['form-group', 'has-feedback', setting.class]">
        <label :for="setting.name" class="col-sm-4 control-label" :title="setting.name">Description: {{ setting.name | trans }}</label>
        <div class="col-sm-6 col-lg-4">
            <input type="text" class="form-control validation"
                   v-model="value"
                   :id="setting.name"
                   :name="setting.name"
                   :pattern="setting.pattern"
                   :required="!!setting.required"
                   :disabled="settings.overridden"
                   :title="settings.overridden ? trans('settings.readonly') : undefined"
            >
            <span class="form-control-feedback"></span>
        </div>
        <div class="col-sm-2">
            <button v-show="showUndo()" @click="resetToInitial" class="btn btn-primary" :title="'Undo' | trans"><i class="fa fa-undo"></i></button>
            <button v-show="showResetToDefault()" @click="resetToDefault" class="btn btn-default" :title="'Reset to default' | trans"><i class="fa fa-refresh"></i></button>
            <div v-if="hasHelp()" data-toggle="tooltip" :title="getHelp" class="toolTip fa fa-fw fa-lg fa-question-circle"></div>
        </div>
    </div>
</template>

<script>
    import BaseSetting from "./BaseSetting";

    export default {
        name: "SettingText",
        mixins: [BaseSetting]
    }
</script>

<style scoped>

</style>
