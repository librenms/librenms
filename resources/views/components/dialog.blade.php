@props(['id' => null, 'maxWidth' => 'md'])

<x-modal :id="$id" :max-width="$maxWidth" {{ $attributes }}>
    <x-panel class="!tw-mb-0">
        <x-slot name="header">
            <div class="tw-flex tw-justify-between">
                <div>{{ $attributes['title'] ?? $title ?? $header }}</div>
                <div class="tw-text-gray-400 hover:tw-text-black tw-cursor-pointer" x-on:click="show = false"><i class="fa-solid fa-lg fa-times"></i></div>
            </div>
        </x-slot>
        <div class="tw-flex tw-flex-col tw-p-4">
            <div>{{ $slot }}</div>
            <div class="tw-text-right tw-mt-4">
                <button class="btn btn-primary" x-on:click="$dispatch('dialog-confirm')">{{ __('Ok') }}</button>
                <button class="btn btn-danger" x-on:click="show=false">{{ __('Cancel') }}</button>
            </div>
        </div>
    </x-panel>
</x-modal>
