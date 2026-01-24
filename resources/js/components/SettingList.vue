<!--
  - SettingList.vue
  -
  - Component for editing list (array) type settings with drag-and-drop reordering.
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
    <div v-tooltip="disabled ? $t('settings.readonly') : false">
        <draggable v-model="localList" @end="dragged()" :disabled="disabled || isPending">
            <div v-for="(item, index) in localList" :key="index" :class="['input-group', errorIndex === index ? 'has-error' : '']">
                <span :class="['input-group-addon', disabled ? 'disabled' : '']">{{ index+1 }}.</span>
                <input type="text"
                       class="form-control"
                       :value="editingIndex === index ? editingValue : item"
                       :readonly="disabled || isPending"
                       @focus="startEdit(index, item)"
                       @blur="updateItem(index, $event.target.value)"
                       @keyup.enter="updateItem(index, $event.target.value)"
                       @input="onEditInput(index, $event.target.value)"
                       :ref="'itemInput' + index"
                >
                <span class="input-group-btn">
                    <button v-if="!disabled" @click="removeItem(index)" type="button" class="btn btn-danger" :disabled="isPending"><i class="fa fa-minus-circle"></i></button>
                </span>
            </div>
        </draggable>
        <div v-if="errorIndex !== null && errorIndex < localList.length" class="text-danger error-message">
            {{ errorMessage }}
        </div>
        <div v-if="!disabled">
            <div :class="['input-group', errorIndex === 'new' ? 'has-error' : '']">
                <input type="text"
                       v-model="newItem"
                       @keyup.enter="addItem"
                       :class="['form-control', errorIndex === 'new' ? 'is-invalid' : '']"
                       :readonly="isPending"
                       ref="newItemInput"
                >
                <span class="input-group-btn">
                    <button @click="addItem" type="button" class="btn btn-primary" :disabled="isPending"><i class="fa fa-plus-circle"></i></button>
                </span>
            </div>
            <div v-if="errorIndex === 'new'" class="text-danger error-message">
                {{ errorMessage }}
            </div>
        </div>
    </div>
</template>

<script>
    import BaseSetting from "./BaseSetting.vue";
    import draggable from 'vuedraggable'

    export default {
        name: "SettingList",
        mixins: [BaseSetting],
        components: {
            draggable,
        },
        data() {
            return {
                localList: this.value ?? [],
                newItem: "",
                pendingItem: null,
                editingIndex: null,
                editingValue: null,
                errorIndex: null
            }
        },
        computed: {
            hasError() {
                return this.updateStatus === 'error' && this.errorMessage;
            },
            isPending() {
                return this.updateStatus === 'pending';
            }
        },
        methods: {
            addItem() {
                if (this.disabled || this.isPending || !this.newItem.trim()) return;
                this.clearError();
                this.pendingItem = this.newItem;
                this.errorIndex = 'new';
                this.localList.push(this.newItem);
                this.$emit('input', this.localList);
            },
            removeItem(index) {
                if (this.disabled || this.isPending) return;
                this.clearError();
                this.localList.splice(index, 1);
                this.$emit('input', this.localList);
            },
            startEdit(index, value) {
                if (this.isPending) return;
                this.editingIndex = index;
                this.editingValue = value;
            },
            onEditInput(index, value) {
                if (this.editingIndex === index) {
                    this.editingValue = value;
                }
            },
            updateItem(index, value) {
                if (this.disabled || this.isPending) return;
                // If no change from original, just clear editing state
                if (this.localList[index] === value) {
                    this.editingIndex = null;
                    this.editingValue = null;
                    return;
                }
                this.clearError();
                this.errorIndex = index;
                this.editingIndex = index;
                this.editingValue = value;
                this.localList[index] = value;
                this.$emit('input', [...this.localList]);
            },
            dragged() {
                if (this.disabled || this.isPending) return;
                this.clearError();
                this.$emit('input', this.localList);
            },
            clearError() {
                this.errorIndex = null;
            }
        },
        watch: {
            value(updated) {
                // Don't reset if we're currently showing an error for an edit
                if (this.hasError && this.editingIndex !== null && this.errorIndex === this.editingIndex) {
                    // Keep the editing value in place, but update the rest
                    const newList = updated ? [...updated] : [];
                    if (this.editingIndex < newList.length) {
                        newList[this.editingIndex] = this.editingValue;
                    }
                    this.localList = newList;
                    return;
                }
                this.localList = updated ? [...updated] : [];
            },
            updateStatus(newStatus) {
                if (newStatus === 'success') {
                    // Clear all pending state on successful save
                    this.newItem = "";
                    this.pendingItem = null;
                    this.editingIndex = null;
                    this.editingValue = null;
                    this.errorIndex = null;
                } else if (newStatus === 'error') {
                    if (this.pendingItem !== null && this.errorIndex === 'new') {
                        // On error for new item, restore to input field and remove from list
                        this.newItem = this.pendingItem;
                        // Remove the invalid item from the list (it was added optimistically)
                        const indexToRemove = this.localList.lastIndexOf(this.pendingItem);
                        if (indexToRemove !== -1) {
                            this.localList.splice(indexToRemove, 1);
                        }
                        this.pendingItem = null;
                        this.$nextTick(() => {
                            if (this.$refs.newItemInput) {
                                this.$refs.newItemInput.focus();
                            }
                        });
                    }
                    // For edits, the editingValue is already preserved via the template binding
                }
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

    .error-message {
        font-size: 0.85em;
        margin-top: 4px;
        margin-bottom: 8px;
    }

    .has-error .form-control {
        border-color: #a94442;
    }
</style>
