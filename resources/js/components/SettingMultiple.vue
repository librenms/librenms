<!--
  - SettingMultiple.vue
  -
  - Multi-select option. Stores values as an array.
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
  -->

<template>
    <div>
        <multiselect
            class="form-control"
            :value="formattedValue"
            :required="required"
            :disabled="disabled"
            :name="name"
            label="label"
            track-by="value"
            :options="formattedOptions"
            :multiple="true"
            @input="$emit('input', mutateInputEvent($event))"
        >
        </multiselect>
    </div>
</template>

<script>
import BaseSetting from "./BaseSetting.vue";

export default {
    name: "SettingMultiple",
    mixins: [BaseSetting],
    computed: {
        formattedOptions() {
            return this.formatOptions(this.options || {})
        },
        formattedValue() {
            const values = Array.isArray(this.value)
                ? this.value
                : (this.value ?? '').toString().split(',').map(v => v.trim()).filter(v => v.length > 0)

            const allowed = new Set(Object.keys(this.options || {}))
            const selected = values.filter(v => allowed.has(v))
            return selected.map(v => ({label: (this.options || {})[v], value: v}))
        }
    },
    methods: {
        formatOptions(options) {
            return Object.entries(options).map(([k, v]) => ({label: v, value: k}))
        },
        mutateInputEvent(selected) {
            // Always save multiple selections as an array
            const items = Array.isArray(selected) ? selected : (selected ? [selected] : [])

            return items
                .map(option => typeof option === 'string' ? option : option?.value)
                .filter(v => typeof v === 'string' && v.length > 0)
        }
    }
}
</script>

<style scoped>
</style>

