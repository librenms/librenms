<!--
  - LibrenmsSetting.vue
  -
  -
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
  - @copyright  2019 Tony Murray
  - @author     Tony Murray <murraytony@gmail.com>
  -->

<template>
    <div :class="['form-group', 'has-feedback', setting.class, feedback]">
        <label :for="setting.name" class="col-sm-5 control-label" v-tooltip="{ content: setting.name }">
            {{ getDescription() }}
            <span v-if="setting.units">({{ getUnits() }})</span>
        </label>
        <div class="col-sm-5" v-tooltip="{ content: setting.disabled ? $t(this.prefix + '.readonly') : false }">
            <component :is="getComponent()"
                       :value="value"
                       :name="setting.name"
                       :pattern="setting.pattern"
                       :disabled="setting.overridden"
                       :required="setting.required"
                       :options="setting.options"
                       :update-status="updateStatus"
                       @input="changeValue($event)"
                       @change="changeValue($event)"
            ></component>
            <span class="form-control-feedback"></span>
        </div>
        <div class="col-sm-2">
            <button :style="{'opacity': showResetToDefault()?1:0}" @click="resetToDefault" class="btn btn-default" :class="{'disable-events': ! showResetToDefault()}" type="button" v-tooltip="{ content: $t('Reset to default') }"><i class="fa fa-refresh"></i></button>
            <button :style="{'opacity': showUndo()?1:0}" @click="resetToInitial" class="btn btn-primary" :class="{'disable-events': ! showUndo()}" type="button" v-tooltip="{ content: $t('Undo') }"><i class="fa fa-undo"></i></button>
            <div v-if="hasHelp()" v-tooltip="{content: getHelp(), trigger: 'hover click'}" class="fa fa-fw fa-lg fa-question-circle"></div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "LibrenmsSetting",
        props: {
            'setting': {type: Object, required: true},
            'prefix': {type: String, default: 'settings'},
            'id': {required: false}
        },
        data() {
            return {
                value: this.setting.value,
                updateStatus: 'none',
                feedback: ''
            }
        },
        methods: {
            persistValue(value) {
                this.updateStatus = 'pending';
                axios.put(route(this.prefix + '.update', this.getRouteParams()), {value: value})
                    .then((response) => {
                        this.value = response.data.value;
                        this.$emit('setting-updated', {name: this.setting.name, value: this.value});
                        this.updateStatus = 'success';
                        this.feedback = 'has-success';
                        setTimeout(() => this.feedback = '', 3000);
                    })
                    .catch((error) => {
                        this.feedback = 'has-error';
                        this.updateStatus = 'error';
                        toastr.error(error.response.data.message);

                        // don't reset certain types back to actual value on error
                        const ignore = [
                            'text',
                            'email',
                            'password'
                        ];
                        if (!ignore.includes(this.setting.type)) {
                            this.value = error.response.data.value;
                            this.$emit('setting-updated', {name: this.setting.name, value: this.value});
                            setTimeout(() => this.feedback = '', 3000);
                        }
                    })
            },
            debouncePersistValue: _.debounce(function (value) {
                this.persistValue(value)
            }, 500),
            changeValue(value) {
                if (['select', 'boolean', 'multiple'].includes(this.setting.type)) {
                    // no need to debounce
                    this.persistValue(value);
                } else {
                    this.debouncePersistValue(value);
                }
                this.value = value
            },
            getUnits() {
                let key = this.prefix + '.units.' + this.setting.units;
                return this.$te(key) ? this.$t(key) : this.setting.units
            },
            getDescription() {
                let key = this.prefix + '.settings.' + this.setting.name + '.description';
                return (this.$te(key) || this.$te(key, this.$i18n.fallbackLocale)) ? this.$t(key) : this.setting.name;
            },
            getHelp() {
                let help = this.$t(this.prefix + '.settings.' + this.setting.name + '.help');
                if (this.setting.overridden) {
                    help += "</p><p>" + this.$t(this.prefix + '.readonly')
                }

                return help
            },
            hasHelp() {
                let key = this.prefix + '.settings.' + this.setting.name + '.help';
                return this.$te(key) || this.$te(key, this.$i18n.fallbackLocale)
            },
            resetToDefault() {
                axios.delete(route(this.prefix + '.destroy', this.getRouteParams()))
                    .then((response) => {
                        this.value = response.data.value;
                        this.feedback = 'has-success';
                        setTimeout(() => this.feedback = '', 3000);
                    })
                    .catch((error) => {
                        this.feedback = 'has-error';
                        setTimeout(() => this.feedback = '', 3000);
                        toastr.error(error.response.data.message);
                    })
            },
            resetToInitial() {
                this.changeValue(this.setting.value)
            },
            showResetToDefault() {
                return !this.setting.overridden
                    && !_.isEqual(this.value, this.setting.default)
            },
            showUndo() {
                return !_.isEqual(this.setting.value, this.value);
            },
            getRouteParams() {
                let parameters = [this.setting.name];
                if (this.id) {
                    parameters.unshift(this.id);
                }
                return parameters;
            },
            getComponent() {
                // snake to studly
                const component = 'Setting' +  this.setting.type.toString()
                    .replace(/(-[a-z]|^[a-z])/g, (group) => group.toUpperCase().replace('-', ''));

                return typeof Vue.options.components[component] !== 'undefined' ? component : 'SettingNull';
            }
        }
    }
</script>

<style scoped>
.disable-events {
    pointer-events: none;
}
</style>
