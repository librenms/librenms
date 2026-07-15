<!--
  - SettingKeyValueMap.vue
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
  - @copyright  2023 Tony Murray
  - @author     Tony Murray <murraytony@gmail.com>
  -->

<template>
    <div v-tooltip="disabled ? $t('settings.readonly') : false">
        <div v-for="(value, key) in localList" class="tw:flex">
            <input type="text"
                   class="form-control tw:w-auto!"
                   :value="key"
                   :readonly="disabled"
                   :placeholder="options.keyPlaceholder"
                   @blur="updateItemKey(key, $event.target.value)"
                   @keyup.enter="updateItemKey(key, $event.target.value)"
            >
            <input type="text"
                   class="form-control tw:w-auto!"
                   :value="value"
                   :readonly="disabled"
                   :placeholder="options.valuePlaceholder"
                   @blur="updateItemValue(key, $event.target.value)"
                   @keyup.enter="updateItemValue(key, $event.target.value)"
            >
            <button v-if="!disabled" @click="removeItem(key)" type="button" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button>
        </div>
        <div v-if="!disabled" class="tw:flex">
            <input type="text" class="form-control tw:w-auto!" v-model="newItemKey" :placeholder="options.keyPlaceholder">
            <input type="text" class="form-control tw:w-auto!" v-model="newItemValue" :placeholder="options.valuePlaceholder">
            
            <button @click="addItem" type="button" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button>
        </div>
    </div>
</template>

<script>
import BaseSetting from "./BaseSetting.vue";

export default {
        name: "SettingKeyValueMap",
        mixins: [BaseSetting],
        data() {
            return {
                newItemKey: "",
                newItemValue: "",
                localList: this.normalizeList(this.value),
            }
        },
        methods: {
            normalizeList(value) {
                // empty lists parse to an array
                if (Array.isArray(value) || value === null || value === undefined) {
                    return {};
                }

                return value;
            },
            addItem() {
                this.localList[this.newItemKey] = this.newItemValue;
                this.newItemKey = "";
                this.newItemValue = "";
                this.$emit('input', this.localList)
            },
            removeItem(index) {
                console.log(index);
                delete this.localList[index]
                this.$emit('input', this.localList)
            },
            updateItemKey(oldValue, newValue) {
                this.localList = Object.keys(this.localList).reduce((newList, current) => {
                    let key = (current === oldValue ? newValue : current);
                    newList[key] = this.localList[current];
                    return newList;
                }, {});
                this.$emit('input', this.localList)
            },
            updateItemValue(key, newValue) {
                this.localList[key] = newValue;
                this.$emit('input', this.localList)
            },
        },
        watch: {
            value() {
                this.localList = this.normalizeList(this.value);
            }
        }
    }
</script>

<style scoped>
   div >>> .select2-container {
       flex-grow: 1;
   }
   div >>> .select2-selection--multiple .select2-search--inline .select2-search__field {
       width: 0.75em !important;
   }
</style>
