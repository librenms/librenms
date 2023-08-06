<!--
  - LibrenmsSelect.vue
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
  - @copyright  2023 Tony Murray
  - @author     Tony Murray <murraytony@gmail.com>
  -->

<template>
    <select></select>
</template>

<script>
export default {
    name: "LibrenmsSelect",
    props: {
        routeName: {
            type: String,
            required: true
        },
        placeholder: {
            type: String,
            default: '',
        },
        allowClear: {
            type: Boolean,
            default: true
        },
        value: {
            type: [String, Number],
            default: ''
        }
    },
    model: {
        event: 'change',
        prop: 'value'
    },
    data: () => ({
        select2: null
    }),
    methods: {
        checkValue() {
            if (this.value === '') {
                return true;
            }

            if (! this.select2.find("option[value='" + this.value + "']").length) {
                axios.get(route(this.routeName), {params: {id: this.value}}).then((response) => {
                    response.data.results.forEach((item) => {
                        if (item.id == this.value) {
                            this.select2.append(new Option(item.text, item.id, true, true))
                                .trigger('change');
                        }
                    })
                });

                return false;
            }

            return true;
        }
    },
    watch: {
        value(value) {
            // check value and if the value doesn't exist, cancel this update to fetch it
           if (! this.checkValue()) {
               return;
           }

            if (value instanceof Array) {
                this.select2.val([...value]);
            } else {
                this.select2.val([value]);
            }
            this.select2.trigger('change');
        }
    },
    computed: {
        settings() {
            return {
                theme: "bootstrap",
                dropdownAutoWidth : true,
                width: "auto",
                allowClear: Boolean(this.allowClear),
                placeholder: this.placeholder,
                ajax: {
                    url: route(this.routeName).toString(),
                    delay: 250,
                    cache: true
                }
            }
        }
    },
    mounted() {
        this.select2 = $(this.$el);

        this.checkValue();

        this.select2.select2(this.settings)
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
