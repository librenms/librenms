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
            theme: window.siteStylePreference,
            toggleTheme(newTheme) {
                if (this.theme === newTheme) {
                    return;
                }

                this.theme = newTheme;
                window.siteStylePreference = newTheme;

                fetch('{{ route('preferences.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        pref: 'site_style',
                        value: newTheme
                    })
                }).then(() => {
                    if (!['dark', 'light'].includes(window.siteStyle)) {
                        location.reload();
                    }

                    applySiteStyle(newTheme);
                });
            }
        }
    }
</script>
