<!--
  - SettingDashboardSelect.vue
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
    <v-select
            :options="localOptions"
            label="text"
            :clearable="false"
            :value="selected"
            @input="$emit('input', $event.id)"
            :required="required"
            :disabled="disabled"
    >
    </v-select>
</template>

<script>

    import BaseSetting from "./BaseSetting";

    export default {
        name: "SettingDashboardSelect",
        mixins: [BaseSetting],
        data() {
            return {
                ajaxData: {results: []},
                default: {id: 0, text: this.$t('No Default Dashboard')}
            }
        },
        mounted() {
            axios.get(route('ajax.select.dashboard')).then((response) => this.ajaxData = response.data);
        },
        computed: {
            localOptions() {
                return [this.default].concat(this.ajaxData.results)
            },
            selected() {
                return this.value === 0 ? this.default : this.ajaxData.results.find(dash => dash.id === this.value);
            }
        }
    }
</script>

<style scoped>

</style>
