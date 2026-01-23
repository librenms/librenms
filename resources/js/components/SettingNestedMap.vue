<!--
  - SettingNestedMap.vue
  -
  - Component for nested key-value structures (e.g., socialite configs)
  -
  - This program is free software: you can redistribute it and/or modify
  - it under the terms of the GNU General Public License as published by
  - the Free Software Foundation, either version 3 of the License, or
  - (at your option) any later version.
  -
  - This program is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
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
        <div v-for="(item, index) in localList">
            <b>{{ index }}</b>
            <div v-for="(subItem, subindex) in item" class="input-group">
                <span :class="['input-group-addon', disabled ? 'disabled' : '', keyErrors[index] === subindex ? 'btn-danger' : '']" v-if="keyErrors[index] === subindex" v-tooltip="keyErrorMessages[index]">{{ subindex }}</span>
                <span :class="['input-group-addon', disabled ? 'disabled' : '']" v-else>{{ subindex }}</span>
                <input type="text"
                       class="form-control"
                       :value="subItem"
                       :readonly="disabled"
                       @blur="updateSubItem(index, subindex, $event.target.value)"
                       @keyup.enter="updateSubItem(index, subindex, $event.target.value)"
                >
                <span class="input-group-btn">
                    <button v-if="!disabled" @click="removeSubItem(index, subindex)" type="button" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button>
                </span>
            </div>
            <div v-if="!disabled">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="input-group">
                            <input type="text" v-model="newSubItemKey[index]" @input="validateNewSubKey(index)" class="form-control" :placeholder="$t('Key')">
                        </div>
                        <div v-if="newSubItemKeyErrors[index]" class="text-danger small">{{ newSubItemKeyErrors[index] }}</div>
                    </div>
                    <div class="col-lg-8">
                        <div class="input-group">
                            <input type="text" v-model="newSubItemValue[index]" @keyup.enter="addSubItem(index)" class="form-control" :placeholder="$t('Value')">
                            <span class="input-group-btn">
                                <button @click="addSubItem(index)" type="button" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <hr/>
        </div>
        <div v-if="!disabled">
            <div class="input-group">
                <input type="text" v-model="newSubArray" @input="validateNewParentKey" @keyup.enter="addSubArray" class="form-control">
                <span class="input-group-btn">
                    <button @click="addSubArray" type="button" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button>
                </span>
            </div>
            <div v-if="newSubArrayError" class="text-danger small">{{ newSubArrayError }}</div>
        </div>
    </div>
</template>

<script>
    import BaseSetting from "./BaseSetting.vue";

    export default {
        name: "SettingNestedMap",
        mixins: [BaseSetting],

        data() {
            return {
                localList: this.value ?? {},
                newSubItemKey: {},
                newSubItemValue: {},
                newSubArray: "",
                newSubArrayError: "",
                newSubItemKeyErrors: {},
                keyErrors: {},
                keyErrorMessages: {}
            }
        },
        methods: {
            parseServerError(errorMessage) {
                // NOTE: This is a fallback because the API does not return structured
                // per-key errors for nested-map settings. If/when the API provides
                // structured error data, this parsing should be removed in favor of that.
                // Try to extract the key from server error messages like:
                // "The Key '/^cpu interface' is not a valid regular expression..." etc.
                if (!errorMessage) return;

                const tryMatch = (label) => {
                    if (!label) return null;
                    const escapedLabel = label.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                    const regex = new RegExp(escapedLabel + "\\s+'([^']+)'", 'i');
                    return errorMessage.match(regex);
                };

                // Try translation first, then fallback to English
                let match = tryMatch(this.$t('Key'));
                if (!match && this.$t('Key') !== 'Key') {
                    match = tryMatch('Key');
                }

                if (match && match[1]) {
                    const errorKey = match[1];

                    // For NestedMap, we need to find which parent key contains this child key
                    // We scan the localList structure
                    for (const parentKey in this.localList) {
                        const childObj = this.localList[parentKey];
                         if (Object.prototype.hasOwnProperty.call(childObj, errorKey)) {
                            this.$set(this.keyErrors, parentKey, errorKey);
                            this.$set(this.keyErrorMessages, parentKey, errorMessage);
                            return; // Stop after finding the first match
                        }
                    }
                }
            },
            validateNewParentKey() {
                if (!this.newSubArray || !this.newSubArray.trim()) {
                    this.newSubArrayError = '';
                    return;
                }

                if (Object.prototype.hasOwnProperty.call(this.localList, this.newSubArray)) {
                    this.newSubArrayError = this.$t('settings.validate.duplicate_key');
                    return;
                }

                this.newSubArrayError = '';
            },
            validateNewSubKey(index) {
                const key = this.newSubItemKey[index];
                if (!key || !key.trim()) {
                    this.$delete(this.newSubItemKeyErrors, index);
                    return;
                }

                const existing = this.localList[index] || {};
                if (Object.prototype.hasOwnProperty.call(existing, key)) {
                    this.$set(this.newSubItemKeyErrors, index, this.$t('settings.validate.duplicate_key'));
                    return;
                }

                this.$delete(this.newSubItemKeyErrors, index);
            },
            addSubItem(index) {
                if (this.disabled) return;

                const key = this.newSubItemKey[index];
                const value = this.newSubItemValue[index];
                if (!key || !key.trim()) return;

                const existing = this.localList[index] || {};
                if (Object.prototype.hasOwnProperty.call(existing, key)) {
                    this.$set(this.newSubItemKeyErrors, index, this.$t('settings.validate.duplicate_key'));
                    return;
                }

                if (this.newSubItemKeyErrors[index]) return;

                // Use spread to create new object for Vue 2 reactivity
                this.localList = {
                    ...this.localList,
                    [index]: {
                        ...this.localList[index],
                        [key]: value
                    }
                };
                this.$emit('input', this.localList);
                this.newSubItemValue = { ...this.newSubItemValue, [index]: "" };
                this.newSubItemKey = { ...this.newSubItemKey, [index]: "" };
                this.$delete(this.newSubItemKeyErrors, index);
            },
            removeSubItem(index, subindex) {
                if (this.disabled) return;

                // Create new sub-object without the removed key
                const { [subindex]: removed, ...restSub } = this.localList[index];

                if (Object.keys(restSub).length === 0) {
                    // Remove the entire parent key if no children left
                    const { [index]: removedParent, ...restList } = this.localList;
                    this.localList = restList;
                } else {
                    this.localList = {
                        ...this.localList,
                        [index]: restSub
                    };
                }

                this.$emit('input', this.localList);
            },
            updateSubItem(index, subindex, value) {
                if (this.disabled || this.localList[index][subindex] === value) return;

                this.localList = {
                    ...this.localList,
                    [index]: {
                        ...this.localList[index],
                        [subindex]: value
                    }
                };
                this.$emit('input', this.localList);
            },
            addSubArray() {
                if (this.disabled) return;

                if (!this.newSubArray || !this.newSubArray.trim()) return;
                if (Object.prototype.hasOwnProperty.call(this.localList, this.newSubArray)) {
                    this.newSubArrayError = this.$t('settings.validate.duplicate_key');
                    return;
                }

                if (this.newSubArrayError) return;

                this.localList = {
                    ...this.localList,
                    [this.newSubArray]: {}
                };
                this.$emit('input', this.localList);
                this.newSubArray = "";
            },
        },
        watch: {
            value(updated) {
                // careful to avoid loops with this
                this.localList = updated ?? {};
            },
            errorMessage(newError) {
                // When server returns an error, try to associate it with a specific key
                if (newError) {
                    this.parseServerError(newError);
                } else {
                    // Clear all key errors when error is cleared
                    this.keyErrors = {};
                    this.keyErrorMessages = {};
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
</style>
