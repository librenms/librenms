<!--
  - SettingArray.vue
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
  - @copyright  2019 Tony Murray
  - @author     Tony Murray <murraytony@gmail.com>
  -->

<template>
    <ul
        :title="disabled ? trans('setttings.readonly') : false"
    >
        <li v-for="(item, index) in value">
            <div class="input-group">
                <span class="input-group-addon">{{ index+1 }}.</span>
                <input type="text"
                       class="form-control"
                       :value="item"
                       :readonly="disabled"
                       @blur="updateItem(index, $event.target.value)"
                       @keyup.enter="updateItem(index, $event.target.value)"
                >
                <span class="input-group-btn">
                    <button v-if="!disabled" @click="removeItem(index)" type="button" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button>
                </span>
            </div>
        </li>
        <li v-if="!disabled">
            <div class="input-group">
                <input type="text" v-model="newItem" @keyup.enter="addItem" class="form-control">
                <span class="input-group-btn">
                    <button @click="addItem" type="button" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button>
                </span>
            </div>
        </li>
    </ul>
</template>

<script>
    import BaseSetting from "./BaseSetting";

    export default {
        name: "SettingArray",
        mixins: [BaseSetting],
        data() {
            return {
                newItem: ""
            }
        },
        methods: {
            addItem() {
                let newList = this.value;
                newList.push(this.newItem);
                this.$emit('input', newList);
                this.newItem = "";
            },
            removeItem(index) {
                let newList = this.value;
                newList.splice(index, 1);
                this.$emit('input', newList);
            },
            updateItem(index, value) {
                let newList = this.value;
                newList[index] = value;
                this.$emit('input', newList);
            }
        }
    }
</script>

<style scoped>
    ul {
        list-style-type: none;
    }
    li {
        margin-bottom: 2px;
    }
</style>
