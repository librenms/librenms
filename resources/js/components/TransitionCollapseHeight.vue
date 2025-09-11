<!--
  - TransitionCollapseHeight.vue
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
    <transition
        enter-active-class="enter-active"
        leave-active-class="leave-active"
        @before-enter="beforeEnter"
        @enter="enter"
        @after-enter="afterEnter"
        @before-leave="beforeLeave"
        @leave="leave"
        @after-leave="afterLeave"
    >
        <slot />
    </transition>
</template>

<script>
    export default {
        name: "TransitionCollapseHeight",
        methods: {
            beforeEnter(el) {
                requestAnimationFrame(() => {
                    if (!el.style.height) {
                        el.style.height = '0px';
                    }

                    el.style.display = null;
                });
            },
            enter(el) {
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        el.style.height = el.scrollHeight + 'px';
                    });
                });
            },
            afterEnter(el) {
                el.style.height = null;
            },
            beforeLeave(el) {
                requestAnimationFrame(() => {
                    if (!el.style.height) {
                        el.style.height = el.offsetHeight + 'px';
                    }
                });
            },
            leave(el) {
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        el.style.height = '0px';
                    });
                });
            },
            afterLeave(el) {
                el.style.height = null;
            }
        }
    }
</script>

<style scoped>
    .enter-active,
    .leave-active {
        overflow: hidden;
        transition: height 0.2s linear;
    }
</style>
