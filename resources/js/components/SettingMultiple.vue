<!--
  - SettingMultiple.vue
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
    <div>
        <multiselect
            @input="$emit('input', mutateInputEvent($event))"
            :value="[{label: options[value.toString()], value: value.toString()}]"
            :required="required"
            :disabled="disabled"
            :name="name"
            label="label"
            track-by="value"
            :options="multiOptions"
            :allowEmpty="false"
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
            multiOptions() {
                return Object.entries(this.options).map(([k,v]) => ({label: v, value: k}))
            }
        },
        methods: {
            mutateInputEvent(options) {
                console.log(options);
                return options;
            }
        }
    }
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>

<style scoped>

</style>
