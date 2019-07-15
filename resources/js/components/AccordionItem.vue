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
        <div class="panel-heading" role="tab" id="headingOne">
            <h4 class="panel-title">
                <a :class="{'collapsed': !active}" role="button" data-parent="#accordion" @click="active = !active" :data-href="'#' + group + '-' + name" aria-expanded="true" aria-controls="collapseOne">
                    {{ name }}
                </a>
            </h4>
        </div>
        <transition
            name="accordion-item"
            @enter="startTransition"
            @after-enter="endTransition"
            @before-leave="startTransition"
            @after-leave="endTransition">
            <div :id="group + '-' + name" v-if="active" :class="['panel-collapse', 'collapse', {'in': active}]" role="tabpanel" aria-labelledby="headingOne">
                <div class="panel-body">
                    <slot></slot>
                </div>
            </div>
        </transition>
    </div>
</template>

<script>
    export default {
        name: "AccordionItem",
        props: ['name'],
        data() {
            return {
                group: null,
                multiple: false,
                active: false,
                details: 'banana'
            }
        },
        methods: {
            toggle(event) {
                if (this.multiple) {
                    this.active = !this.active
                } else {
                    this.$parent.$children.forEach((item, index) => {
                        if (this.name === item.name) item.active = !item.active
                        else item.active = false
                    })
                }
            },

            startTransition(el) {
                el.style.height = el.scrollHeight + 'px'
            },

            endTransition(el) {
                el.style.height = ''
            }
        }
    }
</script>

<style scoped>
    .accordion-item-trigger-icon {
        $size: 8px;
        display: block;
        position: absolute;
        top: 0; right: 1.25rem; bottom: 0;
        margin: auto;
        width: $size;
        height: $size;
        border-right: 2px solid #363636;
        border-bottom: 2px solid #363636;
        transform: translateY(-$size / 4) rotate(45deg);
        transition: transform 0.2s ease;

    .is-active & {
        transform: translateY($size / 4) rotate(225deg);
    }
    }

    .accordion-item-enter-active, .accordion-item-leave-active {
        will-change: height;
        transition: height 0.2s ease;
    }
    .accordion-item-enter, .accordion-item-leave-to {
        height: 0 !important;
    }

    /* Enter and leave animations can use different */
    /* durations and timing functions.              */
    .slide-fade-enter-active {
        transition: height 1s ease;
    }
    .slide-fade-leave-active {
        transition: height 1s ease;
    }
    .slide-fade-enter, .slide-fade-leave-to
        /* .slide-fade-leave-active below version 2.1.8 */ {
        transform: translateX(10px);
        opacity: 0;
    }
</style>
