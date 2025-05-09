<div x-data="themeToggleComponentData()"
    {{ $attributes->class("tw:flex tw:mx-5 tw:my-2 tw:rounded-md tw:overflow-hidden") }}
>
    <button
        class="tw:p-2 tw:focus:outline-none tw:flex-1 tw:hover:bg-gray-200 tw:dark:hover:bg-dark-gray-500"
        title="Light Mode"
        @click.stop="toggleTheme('light')"
    >
        <i
            class="fas fa-lg fa-sun tw:m-auto"
            :class="theme === 'light' ? 'tw:text-gray-900 tw:dark:text-white' : 'tw:text-gray-400 tw:dark:text-gray-500'"
        ></i>
    </button>
    <button
        class="tw:p-2 tw:focus:outline-none tw:-ml-px tw:flex-1 tw:hover:bg-gray-200 tw:dark:hover:bg-dark-gray-500"
        title="Dark Mode"
        @click.stop="toggleTheme('dark')"
    >
        <i
            class="fas fa-lg fa-moon tw:m-auto"
            :class="theme === 'dark' ? 'tw:text-gray-900 tw:dark:text-white' : 'tw:text-gray-400 tw:dark:text-gray-500'"
        ></i>
    </button>
    <button
        class="tw:p-2 tw:focus:outline-none tw:-ml-px tw:flex-1 tw:hover:bg-gray-200 tw:dark:hover:bg-dark-gray-500"
        title="Device Mode"
        @click.stop="toggleTheme('device')"
    >
        <i
            class="fas fa-lg fa-desktop tw:m-auto"
            :class="theme === 'device' ? 'tw:text-gray-900 tw:dark:text-white' : 'tw:text-gray-400 tw:dark:text-gray-500'"
        ></i>
    </button>
</div>

<script>
    function themeToggleComponentData() {
        return {
            theme: '{{ LibrenmsConfig::get('applied_site_style') }}',
            toggleTheme(newTheme) {
                // reload if another theme is set
                const reload = ! ['dark', 'light', 'device'].includes(this.theme);
                this.theme = newTheme;

                if (newTheme === 'dark' || (newTheme === 'device' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }

                $.ajax({
                    url: '{{ route('preferences.store') }}',
                    dataType: 'json',
                    type: 'POST',
                    data: {
                        pref: 'site_style',
                        value: newTheme
                    },
                    success:() => reload ? location.reload() : null
                });
            }
        }
    }
</script>
