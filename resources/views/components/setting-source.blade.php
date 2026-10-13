@props([
    'setting',
    'reset',
    'stacked' => false,
])

{{--
    Shows where a setting comes from: device override, os default or global default, with a link to remove the device override.
    setting: an Alpine expression for an object with global, os and device values, for example a ModuleStatus
    reset: an Alpine expression that removes the device override
--}}
<div x-data="settingSource" {{ $attributes->class([
    'tw:flex',
    'tw:flex-col tw:items-start tw:leading-tight' => $stacked,
    'tw:flex-wrap tw:items-center tw:gap-3' => ! $stacked,
]) }}>
    <span class="tw:font-medium"
          :class="{{ $setting }}.device === null ? 'tw:text-gray-500 tw:dark:text-dark-white-400' : 'tw:text-amber-600 tw:dark:text-amber-400'"
          :title="sourceDetails({{ $setting }})"
          x-text="sourceLabel({{ $setting }})"></span>
    <button type="button"
            x-show="{{ $setting }}.device !== null"
            x-on:click="{{ $reset }}"
            class="tw:p-0 tw:border-0 tw:bg-transparent tw:text-left tw:text-blue-600 tw:hover:underline tw:dark:text-blue-400">
        <i class="fa fa-rotate-left" aria-hidden="true"></i> <span x-text="resetLabel({{ $setting }})"></span>
    </button>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('settingSource', () => ({
                    sourceLabel(setting) {
                        if (setting.device !== null) return @js(__('Device override'));
                        if (setting.os !== null) return @js(__('OS default'));
                        return @js(__('Global default'));
                    },

                    resetLabel(setting) {
                        return (setting.os ?? setting.global) ? @js(__('Use default (on)')) : @js(__('Use default (off)'));
                    },

                    sourceDetails(setting) {
                        const state = (value) => value === null ? @js(__('Unset')) : (value ? @js(__('Enabled')) : @js(__('Disabled')));

                        return @js(__('Global')) + ': ' + state(setting.global) + '\n'
                            + @js(__('OS')) + ': ' + state(setting.os) + '\n'
                            + @js(__('Device')) + ': ' + state(setting.device);
                    },
                }));
            });
        </script>
    @endpush
@endonce
