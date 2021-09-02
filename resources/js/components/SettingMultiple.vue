<!--
  - SettingMultiple.vue
  -
  - Setting for multiple select option.  Value is expected to be a comma delimited string
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
    <div>
        <multiselect
            @input="$emit('input', mutateInputEvent($event))"
            :value="formattedValue"
            :required="required"
            :disabled="disabled"
            :name="name"
            label="label"
            track-by="value"
            :options="formattedOptions"
            :allow-empty="false"
            :multiple="true"
        >
        </multiselect>
    </div>
</template>

<script>
    import BaseSetting from "./BaseSetting";

    export default {
        name: "SettingMultiple",
        mixins: [BaseSetting],
        computed: {
            formattedValue() {
                if (this.value === undefined) {
                    return []
                }

                let values = this.value.toString().split(',')
                return this.formatOptions(_.pick(this.options, ...values))
            },
            formattedOptions() {
                return this.formatOptions(this.options)
            }
        },
        methods: {
            formatOptions(options) {
                return Object.entries(options).map(([k, v]) => ({label: v, value: k}))
            },
            mutateInputEvent(options) {
                return options.map(option => option.value).join(',');
            }
        }
    }
</script>

<style scoped>

</style>
