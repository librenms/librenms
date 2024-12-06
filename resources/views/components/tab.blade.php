@props(['name', 'value' => null])

<div x-data="{
        id: '',
        name: {{ Js::from($name) }},
        value: {{ Js::from($value ?: $name) }}
    }"
     x-show="value === activeTab"
     role="tabpanel"
     :aria-labelledby="`tab-${id}`"
     :id="`tab-panel-${id}`"
     x-init="id = registerTab(name, value)"
     {{ $attributes }}
>
    {{ $slot }}
</div>
