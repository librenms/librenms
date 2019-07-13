<!--
  - Tabs.vue
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
    <div>
        <div class="panel with-nav-tabs panel-default">
            <div class="panel-heading">
                <div class="tabs">
                    <ul class="nav nav-tabs" role="tablist">
                        <li v-for="tab in tabs" :key="tab.name" :class="{ 'active': tab.isActive }" role="presentation">
                            <a role="tab" :href="tab.href" :aria-controls="tab.name" @click="selectTab(tab)">{{ tab.name }}</a>
                        </li>
                        <li class="pull-right">
                            <slot name="header"></slot>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="panel-body">
                <slot></slot>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "Tabs",
        data() {
            return {tabs: []};
        },
        created() {
            this.tabs = this.$children;
        },
        methods: {
            selectTab(selectedTab) {
                this.tabs.forEach(tab => {
                    tab.isActive = (tab.name === selectedTab.name);
                });
            }
        }
    }
</script>

<style scoped>
.nav-tabs { border-bottom: none; }
</style>
