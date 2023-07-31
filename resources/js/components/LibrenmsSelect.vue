<!--
  - LibrenmsSelect.vue
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
  - @copyright  2023 Tony Murray
  - @author     Tony Murray <murraytony@gmail.com>
  -->

<template>
    <v-select
        :options="options"
        :filterable="false"
        @open="onOpen"
        @close="onClose"
        @search="onSearch"
    >
        <template #list-footer>
            <li v-show="hasNextPage" ref="load" class="loader">
                Loading more options...
            </li>
        </template>
    </v-select>
</template>

<script>
export default {
    name: "LibrenmsSelect",
    props: {
        route: {
            type: String,
            required: true
        }
    },
    data: () => ({
        options: [],
        searchString: '',
        observer: null,
        limit: 50,
        page: 1,
        hasNextPage: false
    }),
    mounted() {
        /**
         * You could do this directly in data(), but since these docs
         * are server side rendered, IntersectionObserver doesn't exist
         * in that environment, so we need to do it in mounted() instead.
         */
        this.observer = new IntersectionObserver(this.infiniteScroll)
        this.fetch(function(){}, this.searchString, this);
    },
    methods: {
        async onOpen() {
            if (this.hasNextPage) {
                await this.$nextTick()
                this.observer.observe(this.$refs.load)
            }
        },
        onClose() {
            this.observer.disconnect()
        },
        async infiniteScroll([{ isIntersecting, target }]) {
            if (isIntersecting) {
                const ul = target.offsetParent
                const scrollTop = target.offsetParent.scrollTop
                this.fetchMore()
                await this.$nextTick()
                ul.scrollTop = scrollTop
            }
        },
        fetchMore() {
            if (this.hasNextPage) {
                this.fetch(function(){}, this.searchString, this);
            }
        },
        onSearch(search, loading) {
            this.page = 1;
            this.hasNextPage = true;
            loading(true);
            this.fetch(loading, search, this);
        },
        fetch: _.debounce((loading, search, vm) => {
            fetch(
                route('ajax.select.' + vm.route, {limit: vm.limit, page: vm.page, term: search})
            ).then(res => {
                res.json().then(json => {
                    // parse the data into the expected format from the select2 backend
                    let options = json.results.map((item) => ({label: item.text, code: item.id}));
                    vm.hasNextPage = json.pagination.more;

                    // append or set the data
                    vm.options = vm.page > 1 ? vm.options.concat(options) : options;

                    vm.page++;
                    loading(false);
                });
            });
        }, 350)
    },
}
</script>

<style scoped>
.pagination {
    display: flex;
    margin: 0.25rem 0.25rem 0;
}
.pagination button {
    flex-grow: 1;
}
.pagination button:hover {
    cursor: pointer;
}
</style>
