<!--
  - LibrenmsSetting.vue
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
        <label :for="setting.name" class="col-sm-4 control-label" :title="setting.name">
            {{ getDescription() }}
            <span v-if="setting.units !== null">({{ setting.units }})</span>
        </label>
        <div class="col-sm-6 col-lg-4">
            <component :is="getComponent()"
                       v-model="value"
                       :name="setting.name"
                       :pattern="setting.pattern"
                       :disabled="setting.overridden"
                       :required="setting.required"
                       :options="setting.options"
            ></component>
            <span class="form-control-feedback"></span>
        </div>
        <div class="col-sm-2">
            <button v-show="showUndo()" @click="resetToInitial" class="btn btn-primary" :title="'Undo' | trans"><i class="fa fa-undo"></i></button>
            <button v-show="showResetToDefault()" @click="resetToDefault" class="btn btn-default" :title="'Reset to default' | trans"><i class="fa fa-refresh"></i></button>
            <div v-if="hasHelp()" data-toggle="tooltip" :title="getHelp()" class="toolTip fa fa-fw fa-lg fa-question-circle"></div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "LibrenmsSetting",
        props: {
            'setting': {type: Object, required: true}
        },
        data() {
            return {
                value: this.setting.value
            }
        },
        methods: {
            commit() {
                this.previous = this.saved
            },
            getDescription() {
                return this.trans('settings.' + this.setting.name + '.description')
            },
            getHelp() {
                return this.trans('settings.' + this.setting.name + '.help')
            },
            hasHelp() {
                return true // TODO implement hasHelp
            },
            resetToDefault() {
                this.value = this.setting.default
            },
            resetToInitial() {
                this.value = this.setting.value
            },
            showResetToDefault() {
                return this.setting.default !== null
                    && !this.setting.overridden
                    && !_.isEqual(this.value, this.setting.default)
            },
            showUndo() {
                return !_.isEqual(this.setting.value, this.value);
            },
            getComponent() {
                const component = 'Setting' + this.setting.type.charAt(0).toUpperCase() + this.setting.type.toString().slice(1);
                return typeof Vue.options.components[component] !== 'undefined' ? component : 'SettingNull';
            }
        }
    }
</script>

<style scoped>

</style>
