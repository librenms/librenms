<!--
  - IntegerSetting.vue
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
  - along with this program.  If not, see <https://www.gnu.org/licenses/>.
  -
  - @package    LibreNMS
  - @link       https://www.librenms.org
  - @copyright  2019 Tony Murray
  - @author     Tony Murray <murraytony@gmail.com>
  -->

<template>
    <input type="number" class="form-control"
           :name="name"
           :value="value"
           @input="$emit('input', parseNumber($event.target.value))"
           :required="required"
           :disabled="disabled"
    >
</template>

<script>
    import BaseSetting from "./BaseSetting";

    export default {
        name: "SettingInteger",
        mixins: [BaseSetting],
        methods: {
            parseNumber(number) {
                let value = parseFloat(number);
                return isNaN(value) ? number : value;
            }
        }
    }
</script>

<style scoped>
    .form-control {
        padding-right: 12px;
    }
</style>
