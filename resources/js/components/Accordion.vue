<!--
  - Accordian.vue
  -
  - Accordion component contains multiple
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
    <div class="panel-group" role="tablist">
        <slot></slot>
    </div>
</template>

<script>
    export default {
        name: "Accordion",
        props: {
            multiple: {
                type: Boolean,
                default: false
            }
        },
        methods: {
            setActive(name) {
                this.$children.forEach((item) => {
                    if (item.slug() === name) {
                        item.isActive = true;
                    }
                })
            },
            activeChanged(name) {
                if (!this.multiple) {
                    this.$children.forEach((item)=> {
                        if (item.slug() !== name) {
                            item.isActive = false
                        }
                    })
                }
            }
        },
        mounted() {
            this.$on('expanded', this.activeChanged);
        }
    }
</script>

<style scoped>

</style>
