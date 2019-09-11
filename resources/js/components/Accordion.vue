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
  - along with this program.  If not, see <http://www.gnu.org/licenses/>.
  -
  - @package    LibreNMS
  - @link       http://librenms.org
  - @copyright  2019 Tony Murray
  - @author     Tony Murray <murraytony@gmail.com>
  -->

<template>
    <div class="panel-group" role="tablist"
         v-on:active-changed="activeChanged"
    >
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
        data() {
            return {
                groupId: null,
                prefix: 'vue'
            }
        },
        methods: {
            setActive(name) {
                this.$children.forEach((item, index) => {
                    if (item.slug() === name) {
                        item.isActive = true;
                    }
                })
            },
            activeChanged(name) {
                if (!this.multiple) {
                    this.$children.forEach((item, index) => {
                        if (item.slug() !== name) {
                            item.isActive = false
                        }
                    })
                }
            }
        },
        mounted() {
            this.$on('active-changed', this.activeChanged);

            this.groupId = this.$el.id;

            // TODO url parsing doesn't belong here
            let search = window.location.toString().match(new RegExp(this.prefix + '/?(?<tab>[^/]*)/?(?<setting>[^/]*)'));
            if (search && search.groups.setting) {
                this.setActive(search.groups.setting)
            }
        }
    }
</script>

<style scoped>

</style>
