<!--
  - SettingGroupRoleMap.vue
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
        <div v-for="(data, group) in localList" class="tw-flex">
            <input type="text"
                   class="form-control !tw-w-auto"
                   :value="group"
                   :readonly="disabled"
                   :placeholder="options.groupPlaceholder"
                   @blur="updateItem(group, $event.target.value)"
                   @keyup.enter="updateItem(group, $event.target.value)"
            >
            <librenms-select class="form-control tw-flex-grow" @change="updateRoles(group, $event)" route-name="ajax.select.role" :value="data.roles" multiple :disabled="disabled" :allow-clear="false"></librenms-select>
            <button v-if="!disabled" @click="removeItem(group)" type="button" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button>
        </div>
        <div v-if="!disabled" class="tw-flex">
            <input type="text" class="form-control !tw-w-auto" v-model="newItem" :placeholder="options.groupPlaceholder">
            <librenms-select class="form-control tw-flex-grow" v-model="newItemRoles" route-name="ajax.select.role" placeholder="Role" multiple :disabled="disabled" :allow-clear="false"></librenms-select>
            <button @click="addItem" type="button" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button>
        </div>
    </div>
</template>

<script>
import BaseSetting from "./BaseSetting";
import LibrenmsSelect from "./LibrenmsSelect.vue";

export default {
        name: "SettingGroupRoleMap",
        components: {LibrenmsSelect},
        mixins: [BaseSetting],
        data() {
            return {
                newItem: "",
                newItemRoles: [],
                localList: this.parseValue(this.value),
            }
        },
        methods: {
            addItem() {
                this.localList[this.newItem] = {roles: this.newItemRoles};
                this.newItem = "";
                this.newItemRoles = [];
                this.$emit('input', this.localList)
            },
            removeItem(index) {
                delete this.localList[index]
                this.$emit('input', this.localList)
            },
            updateItem(oldValue, newValue) {
                this.localList = Object.keys(this.localList).reduce((newList, current) => {
                    let key = (current === oldValue ? newValue : current);
                    newList[key] = this.localList[current];
                    return newList;
                }, {});
                this.$emit('input', this.localList)
            },
            updateRoles(group, roles) {
                console.log(group, roles, this.lock);
                this.localList[group].roles = roles;
                this.$emit('input', this.localList)
            },
            parseValue(value) {
                // empty lists parse to an array
                if (Array.isArray(value)) {
                    return {};
                }

                const levels =  {
                    1:  "user",
                    5:  "global-read",
                    10:  "admin",
                };

                for (const group of Object.keys(value)) {
                    if (! value[group].hasOwnProperty('roles') && value[group].hasOwnProperty('level')) {
                        value[group].roles = levels[value[group].level] ? [levels[value[group].level]] : [];
                        delete value[group]["level"];
                    }
                }

                return value;
            }
        },
        watch: {
            value() {
                this.localList = this.parseValue(this.value);
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
