<!--
  - SettingPassword.vue
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
    <div class="input-group">
        <input :type="visible ? 'text' : 'password'"
               class="form-control"
               :name="name"
               :value="value"
               @input="$emit('input', $event.target.value)"
               :pattern="pattern"
               :required="required"
               :disabled="disabled"
               autocomplete="new-password"
        >
        <span class="input-group-btn">
            <button type="button"
                    class="btn btn-default"
                    @click="visible = !visible"
                    :title="visible ? 'Hide' : 'Show'"
                    :disabled="disabled">
                <i :class="visible ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
            </button>
        </span>
    </div>
</template>

<script>
    import BaseSetting from "./BaseSetting.vue";

    export default {
        name: "SettingPassword",
        mixins: [BaseSetting],
        data() {
            return {
                visible: false
            }
        }
    }
</script>

<style scoped>
</style>
