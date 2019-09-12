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
            {{ index+1 }}. <input :value="item" :readonly="disabled">
            <button v-if="!disabled" @click="removeItem(index)"><i class="fa fa-minus-circle"></i></button>
        </li>
        <li v-if="!disabled">
            <input v-model="newItem" @keyup.enter="addItem">
            <button @click="addItem"><i class="fa fa-plus-circle"></i></button>
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
            }
        }
    }
</script>

<style scoped>
    ul {
        list-style-type: none;
    }
</style>
