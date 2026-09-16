<!--
  - SettingArrayDynamic.vue
  -
  - Ordered draggable array setting with dynamic item selection (e.g. stored secrets).
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
  - @copyright  2026 Tony Murray
  - @author     Tony Murray <murraytony@gmail.com>
  -->

<template>
    <div v-tooltip="disabled ? $t('settings.readonly') : false">
        <draggable v-model="localList" @end="dragged()" :disabled="disabled">
            <div v-for="(item, index) in localList" :key="item" class="input-group">
                <span :class="['input-group-addon', disabled ? 'disabled' : '']">{{ index + 1 }}.</span>
                <input type="text"
                       class="form-control"
                       :value="itemLabels[item] || ('#' + item)"
                       readonly
                >
                <span class="input-group-btn">
                    <button v-if="!disabled" @click="removeItem(index)" type="button" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button>
                </span>
            </div>
        </draggable>
        <div v-if="!disabled" class="tw:flex">
            <librenms-select
                ref="selector"
                class="form-control tw:grow"
                :route-name="'ajax.select.' + options.target"
                :placeholder="options.placeholder || $t('Select to add...')"
                :allow-clear="true"
                @select="itemSelected($event)"
            ></librenms-select>
            <button @click="addItem" type="button" class="btn btn-primary" :disabled="!selectedItem"><i class="fa fa-plus-circle"></i></button>
        </div>
    </div>
</template>

<script>
import BaseSetting from "./BaseSetting.vue";
import LibrenmsSelect from "./LibrenmsSelect.vue";
import draggable from "vuedraggable";

export default {
        name: "SettingArrayDynamic",
        mixins: [BaseSetting],
        components: {
            draggable,
            LibrenmsSelect
        },
        data() {
            return {
                localList: Array.isArray(this.value) ? [...this.value] : [],
                selectedItem: null,
                itemLabels: {}
            }
        },
        methods: {
            itemSelected(data) {
                if (data && data.id) {
                    this.$set(this.itemLabels, data.id, data.text);
                    this.selectedItem = data;
                } else {
                    this.selectedItem = null;
                }
            },
            addItem() {
                if (this.disabled || !this.selectedItem) return;
                const id = isNaN(Number(this.selectedItem.id)) ? this.selectedItem.id : Number(this.selectedItem.id);
                if (!this.localList.includes(id)) {
                    this.localList.push(id);
                    this.$emit('input', this.localList);
                }
                this.selectedItem = null;
                if (this.$refs.selector && this.$refs.selector.select2) {
                    this.$refs.selector.select2.val(null).trigger('change');
                }
            },
            removeItem(index) {
                if (this.disabled) return;
                this.localList.splice(index, 1);
                this.$emit('input', this.localList);
            },
            dragged() {
                if (this.disabled) return;
                this.$emit('input', this.localList);
            },
            fetchLabels(ids) {
                if (!ids || !Array.isArray(ids) || !ids.length || !this.options.target) return;
                const missing = ids.filter(id => !this.itemLabels[id]);
                if (!missing.length) return;
                axios.get(route('ajax.select.' + this.options.target), {params: {id: missing.join(',')}}).then((response) => {
                    if (response.data && Array.isArray(response.data.results)) {
                        response.data.results.forEach((item) => {
                            this.$set(this.itemLabels, item.id, item.text);
                        });
                    }
                }).catch((error) => {
                    console.error('Failed to fetch labels for setting:', error);
                });
            }
        },
        watch: {
            value(updated) {
                this.localList = Array.isArray(updated) ? [...updated] : [];
                this.fetchLabels(this.localList);
            }
        },
        mounted() {
            this.fetchLabels(this.localList);
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

    div >>> .select2-container {
        flex-grow: 1;
    }
</style>
