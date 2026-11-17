@props([
    'remove', // Alpine expression that removes the chip
    'label', // Alpine expression for the remove button label
    'invalid' => 'false', // Alpine expression, true shows the chip as an error
    'icon' => null,
])
{{-- one item of a list field: a community, credential or network --}}
<span {{ $attributes->class(['tw:inline-flex tw:items-center tw:gap-1.5 tw:rounded-md tw:px-2 tw:py-0.5 tw:text-sm']) }}
      x-bind:class="({{ $invalid }}) ? 'tw:bg-red-50 tw:text-red-700 tw:dark:bg-red-950 tw:dark:text-red-300' : 'tw:bg-blue-50 tw:text-blue-800 tw:dark:bg-dark-gray-300 tw:dark:text-dark-white-100'">
    @if($icon)
        <i class="fa-solid {{ $icon }} tw:text-xs tw:opacity-70" aria-hidden="true"></i>
    @endif
    {{ $slot }}
    <button type="button" class="tw:bg-transparent tw:border-0 tw:p-0 tw:leading-none tw:text-inherit tw:opacity-60 tw:hover:opacity-100 tw:hover:text-red-600"
            x-on:click.stop="{{ $remove }}" x-bind:aria-label="{{ $label }}" title="{{ __('first_run.credentials.remove') }}">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
    </button>
</span>
