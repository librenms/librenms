<!--
  - SettingArraySubKeyed.vue
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
  -->

<template>
    <div v-tooltip="disabled ? $t('settings.readonly') : false">
        <div v-for="(item, index) in localList">
            <b>{{ index }}</b>
            <div v-for="(item, subindex) in item" class="input-group">
                <span :class="['input-group-addon', disabled ? 'disabled' : '']">{{ subindex }}</span>
                <input type="text"
                       class="form-control"
                       :value="item"
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
                            <input type="text" v-model="newSubItemKey[index]" class="form-control" placeholder="Key">
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="input-group">
                            <input type="text" v-model="newSubItemValue[index]" @keyup.enter="addSubItem(index)" class="form-control" placeholder="Value">
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
                <input type="text" v-model="newSubArray" @keyup.enter="addSubArray" class="form-control">
                <span class="input-group-btn">
                    <button @click="addSubArray" type="button" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button>
                </span>
            </div>
        </div>
    </div>
</template>

<script>
    import BaseSetting from "./BaseSetting";

    export default {
        name: "SettingArraySubKeyed",
        mixins: [BaseSetting],

        data() {
            return {
                localList: this.value ?? new Object(),
                newSubItemKey: {},
                newSubItemValue: {},
                newSubArray: ""
            }
        },
        methods: {
            addSubItem(index) {
                if (this.disabled) return;

                var obj = {};
                obj[this.newSubItemKey[index]] = this.newSubItemValue[index];

                if (Object.keys(this.localList[index]).length === 0) {
                    this.localList[index] = new Object();
                }
                Object.assign(this.localList[index], obj);
                this.$emit('input', this.localList);
                this.newSubItemValue[index] = "";
                this.newSubItemKey[index] = "";
            },
            removeSubItem(index, subindex) {
                if (this.disabled) return;
                delete this.localList[index][subindex];

                if (Object.keys(this.localList[index]).length === 0) {
                    delete this.localList[index];
                }

                this.$emit('input', this.localList);
            },
            updateSubItem(index, subindex, value) {
                if (this.disabled || this.localList[index][subindex] === value) return;
                this.localList[index][subindex] = value;
                this.$emit('input', this.localList);
            },
            addSubArray() {
                if (this.disabled) return;
                this.localList[this.newSubArray] = new Object();
                this.$emit('input', this.localList);
                this.newSubArray = "";
            },
        },
        watch: {
            value(updated) {
                // careful to avoid loops with this
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
