<!--
  - SettingPasswordArray.vue
  -
  - Like SettingArray, but each item is rendered as a password input with
  - a per-item visibility toggle. Used for sensitive array values such as
  - SNMP community strings.
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
  -->

<template>
    <div v-tooltip="disabled ? $t('settings.readonly') : false">
        <draggable v-model="localList" @end="dragged()" :disabled="disabled">
            <div v-for="(item, index) in localList" class="input-group">
                <span :class="['input-group-addon', disabled ? 'disabled' : '']">{{ index+1 }}.</span>
                <input :type="visibleItems[index] ? 'text' : 'password'"
                       class="form-control"
                       :value="item"
                       :readonly="disabled"
                       autocomplete="new-password"
                       @blur="updateItem(index, $event.target.value)"
                       @keyup.enter="updateItem(index, $event.target.value)"
                >
                <span class="input-group-btn">
                    <button type="button" class="btn btn-default"
                            @click="toggleVisibility(index)"
                            :disabled="disabled"
                            :title="visibleItems[index] ? 'Hide' : 'Show'">
                        <i :class="visibleItems[index] ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
                    </button>
                    <button v-if="!disabled" @click="removeItem(index)" type="button" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button>
                </span>
            </div>
        </draggable>
        <div v-if="!disabled">
            <div class="input-group">
                <input :type="newItemVisible ? 'text' : 'password'"
                       v-model="newItem"
                       @keyup.enter="addItem"
                       autocomplete="new-password"
                       class="form-control">
                <span class="input-group-btn">
                    <button type="button" class="btn btn-default"
                            @click="newItemVisible = !newItemVisible"
                            :title="newItemVisible ? 'Hide' : 'Show'">
                        <i :class="newItemVisible ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
                    </button>
                    <button @click="addItem" type="button" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button>
                </span>
            </div>
        </div>
    </div>
</template>

<script>
    import BaseSetting from "./BaseSetting.vue";
    import draggable from 'vuedraggable'

    export default {
        name: "SettingPasswordArray",
        mixins: [BaseSetting],
        components: {
            draggable,
        },
        data() {
            return {
                localList: this.value ?? [],
                newItem: "",
                visibleItems: {},
                newItemVisible: false
            }
        },
        methods: {
            toggleVisibility(index) {
                this.$set(this.visibleItems, index, !this.visibleItems[index]);
            },
            addItem() {
                if (this.disabled) return;
                this.localList.push(this.newItem);
                this.$emit('input', this.localList);
                this.newItem = "";
            },
            removeItem(index) {
                if (this.disabled) return;
                this.localList.splice(index, 1);
                this.$emit('input', this.localList);
            },
            updateItem(index, value) {
                if (this.disabled || this.localList[index] === value) return;
                this.localList[index] = value;
                this.$emit('input', this.localList);
            },
            dragged() {
                if (this.disabled) return;
                this.$emit('input', this.localList);
            }
        },
        watch: {
            value(updated) {
                this.localList = updated;
            }
        }
    }
</script>

<style scoped>
    .input-group {
        margin-bottom: 3px;
    }

    .input-group-addon:not(.disabled) {
        cursor: move;
    }
</style>
