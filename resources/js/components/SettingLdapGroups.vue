<!--
  - SettingAuthLdapGroups.vue
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
    <div class="form-inline" v-tooltip="disabled ? $t('settings.readonly') : false">
        <div v-for="(data, group) in localList" class="input-group">
            <input type="text"
                   class="form-control"
                   :value="group"
                   :readonly="disabled"
                   @blur="updateItem(group, $event.target.value)"
                   @keyup.enter="updateItem(group, $event.target.value)"
            >
            <span class="input-group-btn" style=" width:0;"></span>
            <select class="form-control" @change="updateLevel(group, $event.target.value)">
                <option value="1" :selected="data.level === 1">{{ $t('Normal') }}</option>
                <option value="5" :selected="data.level === 5">{{ $t('Global Read') }}</option>
                <option value="10" :selected="data.level === 10">{{ $t('Admin') }}</option>
            </select>
            <span class="input-group-btn">
                <button v-if="!disabled" @click="removeItem(group)" type="button" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button>
            </span>
        </div>
        <div v-if="!disabled">
            <div class="input-group">
                <input type="text" class="form-control" v-model="newItem">
                <span class="input-group-btn" style="width:0;"></span>
                <select class="form-control" v-model="newItemLevel">
                    <option value="1">{{ $t('Normal') }}</option>
                    <option value="5">{{ $t('Global Read') }}</option>
                    <option value="10">{{ $t('Admin') }}</option>
                </select>
                <span class="input-group-btn">
                    <button @click="addItem" type="button" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button>
               </span>
            </div>
        </div>
    </div>
</template>

<script>
import BaseSetting from "./BaseSetting";

export default {
        name: "SettingLdapGroups",
        mixins: [BaseSetting],
        data() {
            return {
                localList: Array.isArray(this.value) ? {} : this.value,
                newItem: "",
                newItemLevel: 1,
                lock: false
            }
        },
        methods: {
            addItem() {
                this.$set(this.localList, this.newItem, {level: this.newItemLevel});
                this.newItem = "";
                this.newItemLevel = 1;
            },
            removeItem(index) {
                this.$delete(this.localList, index);
            },
            updateItem(oldValue, newValue) {
                this.localList = Object.keys(this.localList).reduce((newList, current) => {
                    let key = (current === oldValue ? newValue : current);
                    newList[key] = this.localList[current];
                    return newList;
                }, {});
            },
            updateLevel(group, level) {
                this.$set(this.localList, group, {level: level})
            }
        },
        watch: {
            localList() {
                if (! this.lock) {
                    this.$emit('input', this.localList)
                } else {
                    // release the lock
                    this.lock = false;
                }
            },
            value() {
                this.lock = true // prevent loop
                this.localList = Array.isArray(this.value) ? {} : this.value;
            }
        }
    }
</script>

<style scoped>
    .input-group {
        padding-bottom: 3px;
    }
</style>
