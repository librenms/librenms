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
    <select :multiple="multiple"></select>
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
        multiple: {
            type: Boolean,
            default: false
        },
        value: {
            type: [String, Number, Array],
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
            if (this.value === '' || this.value === []) {
                return true;
            }

            // search for missing options and fetch them
            let values = this.value instanceof Array ? this.value : [this.value];
            if (this.select2.find("option").filter((id, el) => values.includes(el.value)).length < values.length) {
                axios.get(route(this.routeName), {params: {id: values.join(',')}}).then((response) => {
                    response.data.results.forEach((item) => {
                        if (values.find(x => x == item.id) !== undefined) {
                            this.select2.append(new Option(item.text, item.id, false, true));
                        }
                    })

                    this.select2.trigger('change');
                });

                return false;
            }

            return true;
        }
    },
    watch: {
        value(value) {
            if (value instanceof Object && value.hasOwnProperty('id') && value.hasOwnProperty('text')) {
                this.select2.append(new Option(value.text, value.id, true, true))
                    .trigger('change');

                return;
            }

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
                multiple: this.multiple,
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
