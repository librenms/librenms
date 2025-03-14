@props(['active' => ''])

<div x-data="{
        activeTab: '{{ $active }}',
        tabs: [],
        registerTab(name, value) {
            this.tabs.push({name: name, value: value});
            if (! this.activeTab) {
                this.changeTab(value)
            }

            return this.tabs.length;
        },
        changeTab(tabValue) {
            this.activeTab = tabValue;
            this.$dispatch('tab-change', tabValue);
        }
     }"
     {{ $attributes }}
>
    <ul role="tablist" class="tw:flex tw:flex-wrap tw:-mb-px tw:list-none tw:text-center tw:text-gray-500 tw:dark:text-gray-400">
        <template x-for="(tab, index) in tabs" :key="index">
            <li class="tw:me-2"
                @click="changeTab(tab.value)"
                :id="`tab-${index + 1}`"
                role="tab"
                :aria-selected="(tab.value === activeTab).toString()"
                :aria-controls="`tab-panel-${index + 1}`"
            >
                <div
                   x-text="tab.name"
                   class="tw:inline-block tw:p-3 tw:border-b-2 tw:rounded-t-lg tw:cursor-pointer"
                   :class="tab.value === activeTab ? 'tw:text-blue-600 tw:border-blue-600 active tw:dark:text-blue-500 tw:dark:border-blue-500' : 'tw:border-transparent tw:hover:text-gray-600 tw:hover:border-gray-300 tw:dark:hover:text-gray-300'"
                ></div>
            </li>
        </template>
    </ul>
    <div x-ref="tabs">
        {{ $slot }}
    </div>
</div>
