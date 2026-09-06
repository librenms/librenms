@props([
    'id' => null,
    'name' => null,
    'type',
    'data' => [],
    'selected' => null,
    'placeholder' => null,
    'config' => [],
    'label' => null,
    'help' => null,
    'multiple' => false,
    'allowClear' => true,
    'disabled' => false,
    'required' => false,
])

@php
    $id = $id ?? 'select2-' . \Illuminate\Support\Str::random(8);
    $defaultConfig = array_merge(['width' => '100%', 'allowClear' => $allowClear], (array) $config);
@endphp

<div {{ $attributes->only(['class', 'x-show', 'x-cloak', 'style'])->merge(['class' => 'form-group tw:mb-0']) }}>
    @if($label)
        <label for="{{ $id }}" class="control-label tw:font-medium tw:text-gray-700 tw:dark:text-dark-white-200">
            {{ $label }}
            @if($required)
                <span class="tw:text-red-500">*</span>
            @elseif($help)
                <span class="text-muted">({{ $help }})</span>
            @endif
        </label>
    @endif

    <select
        id="{{ $id }}"
        @if($name) name="{{ $name }}" @endif
        @if($multiple) multiple @endif
        @if($disabled) disabled @endif
        @if($required) required @endif
        {{ $attributes->except(['class', 'x-show', 'x-cloak', 'style']) }}
        class="form-control"
        style="width: 100%;"
        x-init="
            setTimeout(() => {
                init_select2(
                    $el,
                    @js($type),
                    @js($data),
                    @js($selected),
                    @js($placeholder),
                    @js($defaultConfig)
                );
                $($el).on('change select2:select select2:clear', function () {
                    $el.dispatchEvent(new Event('input', { bubbles: true }));
                    $el.dispatchEvent(new Event('change', { bubbles: true }));
                });
            }, 100);
        "
    >
        {{ $slot }}
    </select>
</div>
