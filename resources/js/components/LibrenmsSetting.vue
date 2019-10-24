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
    <div :class="['form-group', 'has-feedback', setting.class, feedback]">
        <label :for="setting.name" class="col-sm-5 control-label" v-tooltip="setting.name">
            {{ getDescription() }}
            <span v-if="setting.units !== null">({{ setting.units }})</span>
        </label>
        <div class="col-sm-5" v-tooltip="setting.disabled ? $t('settings.readonly') : false">
            <component :is="getComponent()"
                       :value="value"
                       :name="setting.name"
                       :pattern="setting.pattern"
                       :disabled="setting.overridden"
                       :required="setting.required"
                       :options="setting.options"
                       @input="changeValue($event)"
                       @change="changeValue($event)"
            ></component>
            <span class="form-control-feedback"></span>
        </div>
        <div class="col-sm-2">
            <button :style="{'opacity': showResetToDefault()?1:0}" @click="resetToDefault" class="btn btn-default" type="button" v-tooltip="$t('Reset to default')"><i class="fa fa-refresh"></i></button>
            <button :style="{'opacity': showUndo()?1:0}" @click="resetToInitial" class="btn btn-primary" type="button" v-tooltip="$t('Undo')"><i class="fa fa-undo"></i></button>
            <div v-if="hasHelp()" v-tooltip="{content: getHelp(), trigger: 'hover click'}" class="fa fa-fw fa-lg fa-question-circle"></div>
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
                value: this.setting.value,
                feedback: ''
            }
        },
        methods: {
            persistValue(value) {
                axios.put(route('settings.update', this.setting.name), {value: value})
                    .then((response) => {
                        this.value = response.data.value;
                        this.$emit('setting-updated', {name: this.setting.name, value: this.value});
                        this.feedback = 'has-success';
                        setTimeout(() => this.feedback = '', 3000);
                    })
                    .catch((error) => {
                        this.feedback = 'has-error';
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
                if (['select', 'boolean'].includes(this.setting.type)) {
                    // no need to debounce
                    this.persistValue(value);
                } else {
                    this.debouncePersistValue(value);
                }
                this.value = value
            },
            getDescription() {
                let key = 'settings.settings.' + this.setting.name + '.description';
                return (this.$te(key) || this.$te(key, this.$i18n.fallbackLocale)) ? this.$t(key) : this.setting.name;
            },
            getHelp() {
                let help = this.$t('settings.settings.' + this.setting.name + '.help');
                if (this.setting.overridden) {
                    help += "</p><p>" + this.$t('settings.readonly')
                }

                return help
            },
            hasHelp() {
                var key = 'settings.settings.' + this.setting.name + '.help';
                return this.$te(key) || this.$te(key, this.$i18n.fallbackLocale)
            },
            resetToDefault() {
                axios.delete(route('settings.destroy', this.setting.name))
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

</style>
