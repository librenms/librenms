<!--
  - AccordionItem.vue
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
    <div class="panel panel-default">
        <div class="panel-heading" role="tab" :id="slug()">
            <h4 class="panel-title">
                <a class="accordion-item-trigger" :class="{'collapsed': !active}" role="button" data-parent="#accordion" @click="active = !active" :data-href="'#' + slug()">
                    <i class="fa fa-chevron-down accordion-item-trigger-icon"></i> {{ name }}
                </a>
            </h4>
        </div>
        <transition-collapse-height>
            <div :id="slug() + '-content'" v-if="active" :class="['panel-collapse', 'collapse', {'in': active}]" role="tabpanel" aria-labelledby="headingOne">
                <div class="panel-body">
                    <slot></slot>
                </div>
            </div>
        </transition-collapse-height>
    </div>
</template>

<script>
    export default {
        name: "AccordionItem",
        props: {
            name: {
                type: String,
                required: true
            },
            group: {
                type: String,
                default: ''
            },
            expanded: {
                type: Boolean,
                default: false
            }
        },
        data() {
            return {
                active: this.expanded
            }
        },
        watch: {
            active: function (active) {
                if (!this.$parent.multiple && active) {
                    this.$parent.$children.forEach((item, index) => {
                        if (this.name !== item.name) {
                            item.active = false
                        }
                    })
                }
            }
        },
        methods: {
            slug () {
                return (this.group ? (this.group + '-' + this.name) : this.name).toString().toLowerCase()
                    .replace(/\s+/g, '-');
            }
        }
    }
</script>

<style scoped>
    .accordion-item-trigger-icon {
        transition: transform 0.2s ease;
    }
    .accordion-item-trigger.collapsed .accordion-item-trigger-icon {
        transform: rotate(-90deg);
    }
</style>
