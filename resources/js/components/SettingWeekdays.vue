<!--
  - SettingWeekdays.vue
  -
  - Multi-select weekday picker that stores an array value.
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
    name: "SettingWeekdays",
    mixins: [BaseSetting],
    computed: {
        formattedOptions() {
            return this.formatOptions(this.options || {})
        },
        formattedValue() {
            if (!Array.isArray(this.value)) {
                return []
            }

            const allowed = new Set(Object.keys(this.options || {}))
            const selected = this.value.filter(v => allowed.has(v))
            return selected.map(v => ({label: (this.options || {})[v], value: v}))
        }
    },
    methods: {
        formatOptions(options) {
            return Object.entries(options).map(([k, v]) => ({label: v, value: k}))
        },
        mutateInputEvent(selected) {
            if (!Array.isArray(selected)) {
                return []
            }

            return selected.map(option => option.value)
        }
    }
}
</script>

<style scoped>
</style>

