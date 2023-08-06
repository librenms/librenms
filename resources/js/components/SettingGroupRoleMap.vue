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
    <div class="form-inline" v-tooltip="disabled ? $t('settings.readonly') : false">
        <div v-for="(data, group) in localList" class="input-group">
            <input type="text"
                   class="form-control"
                   :value="group"
                   :readonly="disabled"
                   :placeholder="options.groupPlaceholder"
                   @blur="updateItem(group, $event.target.value)"
                   @keyup.enter="updateItem(group, $event.target.value)"
            >
            <span class="input-group-btn">
                <librenms-select class="form-control" @change="updateRole(group, $event)" route-name="ajax.select.role" :value="data.role"></librenms-select>
            </span>
            <span class="input-group-btn">
                <button v-if="!disabled" @click="removeItem(group)" type="button" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button>
            </span>
        </div>
        <div v-if="!disabled">
            <div class="input-group">
                <input type="text" class="form-control" v-model="newItem" :placeholder="options.groupPlaceholder">
                <span class="input-group-btn">
                    <librenms-select class="form-control" v-model="newItemRole" route-name="ajax.select.role" placeholder="Role"></librenms-select>
                </span>
                <span class="input-group-btn">
                    <button @click="addItem" type="button" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button>
               </span>
            </div>
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
                newItemRole: "",
                localList: this.parseValue(this.value),
            }
        },
        methods: {
            addItem() {
                this.$set(this.localList, this.newItem, {role: this.newItemRole});
                this.newItem = "";
                this.newItemRole = "";
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
            updateRole(group, role) {
                console.log(group, role, this.lock);
                this.localList[group].role = role;
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
                    if (! value[group].hasOwnProperty('role') && value[group].hasOwnProperty('level')) {
                        value[group].role = levels[value[group].level] ? levels[value[group].level] : 1;
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
    .input-group {
        padding-bottom: 3px;
    }
</style>
