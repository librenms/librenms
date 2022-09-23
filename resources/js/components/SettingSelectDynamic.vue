<!--
  - SettingSelect2.vue
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
  - @copyright  2021 Tony Murray
  - @author     Tony Murray <murraytony@gmail.com>
  -->

<template>
    <div>
        <select class="form-control"
                :name="name"
                :value="value"
                :required="required"
                :disabled="disabled"
        >
        </select>
    </div>
</template>

<script>
import BaseSetting from "./BaseSetting";

export default {
    name: "SettingSelectDynamic",
    mixins: [BaseSetting],
    data() {
        return {
            select2: null
        };
    },
    watch: {
        value(value) {
            this.select2.val(value).trigger('change');
        }
    },
    computed: {
        settings() {
            return {
                theme: "bootstrap",
                dropdownAutoWidth : true,
                width: "auto",
                allowClear: Boolean(this.options.allowClear),
                placeholder: this.options.placeholder,
                ajax: {
                    url: route('ajax.select.' + this.options.target).toString(),
                    delay: 250,
                    data: this.options.callback
                }
            }
        }
    },
    mounted() {
        // load initial data
        axios.get(route('ajax.select.' + this.options.target), {params: {id: this.value}}).then((response) => {
            response.data.results.forEach((item) => {
                if (item.id == this.value) {
                    this.select2.append(new Option(item.text, item.id, true, true))
                        .trigger('change');
                }
            })
        });

        this.select2 = $(this.$el)
            .find('select')
            .select2(this.settings)
            .on('select2:select select2:unselect', ev => {
                this.$emit('change', this.select2.val());
                this.$emit('select', ev['params']['data']);
            });
    },
    beforeDestroy() {
        this.select2.select2('destroy');
    }
}
</script>

<style scoped>

</style>
