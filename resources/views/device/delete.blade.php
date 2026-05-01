@extends('layouts.librenmsv1')

@section('title', __('device.delete_device'))

@section('content')
<div class="tw:max-w-4xl tw:mx-auto tw:px-4">
    <x-panel x-data="deviceDeleteButton">
        <x-slot name="title">
            {{ __('device.delete_device') }}
        </x-slot>
        @if (session('success'))
            <div class="tw:mb-6 tw:rounded-md tw:bg-green-50 tw:dark:bg-green-900/30 tw:border tw:border-green-200 tw:dark:border-green-700 tw:px-4 tw:py-3 tw:text-green-800 tw:dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif

        <div class="tw:rounded-md tw:bg-red-50 tw:dark:bg-red-900/30 tw:border tw:border-red-200 tw:dark:border-red-700 tw:px-4 tw:py-3 tw:text-red-800 tw:dark:text-red-300 tw:mb-6">
            <p class="tw:font-semibold">{{ __('device.warning_monitored') }}</p>
            <p>{{ __('device.warning_data') }}
            <ul class="tw:ml-2">
                @foreach($data_warn as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
            </p>
        </div>

    <div>
        <label for="device" class="tw:block tw:font-medium tw:text-gray-700 tw:dark:text-gray-300 tw:mb-1">
            {{ __('Device') }}
        </label>

        <select
            id="device"
            x-ref="deviceSelect"
            class="tw:block tw:w-full tw:rounded-md tw:border tw:border-gray-300 tw:dark:border-gray-600 tw:bg-white tw:dark:bg-gray-700 tw:text-gray-900 tw:dark:text-gray-100 tw:px-3 tw:py-2 tw:shadow-sm"
        ></select>

        <div class="tw:text-center tw:mt-4">
            <button
                type="button"
                class="tw:inline-flex tw:items-center tw:gap-2 tw:rounded-md tw:bg-red-600 tw:px-4 tw:py-2 tw:font-medium tw:text-white! tw:shadow-sm tw:hover:bg-red-700 tw:disabled:opacity-40 tw:disabled:cursor-not-allowed tw:transition-colors"
                :disabled="!selected"
                @click="modalOpen = true"
            >
                <i class="fa fa-trash"></i>
                {{ __('device.delete_device') }}
            </button>
        </div>

        {{-- Modal --}}
        <div
            x-show="modalOpen"
            x-transition:enter="tw:transition tw:ease-out tw:duration-200"
            x-transition:enter-start="tw:opacity-0"
            x-transition:enter-end="tw:opacity-100"
            x-transition:leave="tw:transition tw:ease-in tw:duration-150"
            x-transition:leave-start="tw:opacity-100"
            x-transition:leave-end="tw:opacity-0"
            class="tw:fixed tw:inset-0 tw:z-50 tw:flex tw:items-center tw:justify-center tw:bg-black/50 tw:p-4"
            @keydown.escape.window="modalOpen = false"
            style="display:none"
        >
            <x-panel
                class="tw:w-fit tw:max-w-[90vw] tw:rounded-xl tw:bg-white tw:dark:bg-gray-800 tw:shadow-xl"
                @click.outside="modalOpen = false">
                <x-slot:heading class="tw:flex tw:items-center tw:bg-red-50 tw:dark:bg-red-900/30!">
                    <i class="fa fa-warning fa-3x tw:text-red-800 tw:pr-2 tw:dark:text-dark-white-100"></i>
                    <span class="tw:text-2xl tw:font-semibold tw:text-red-800 tw:dark:text-dark-white-100!"
                     x-text="'{{ __('device.delete') }}'.replace(':name', name)"></span>
                </x-slot:heading>
                <p x-text="'{{ __('device.confirm_delete') }}'.replace(':name', name)"></p>
                <x-slot:footer class="tw:flex tw:justify-end tw:gap-3 tw:bg-transparent">
                    <button
                        type="button"
                        class="tw:rounded-md tw:bg-white tw:border tw:border-gray-300 tw:dark:border-gray-600 tw:dark:bg-gray-700 tw:px-4 tw:py-2 tw:font-medium tw:text-gray-700 tw:dark:text-gray-300 tw:hover:bg-gray-100 tw:dark:hover:bg-gray-600 tw:transition-colors"
                        @click="modalOpen = false"
                    >
                        {{ __('Cancel') }}
                    </button>

                    <form method="POST" :action="actionUrl">
                        @csrf
                        @method('DELETE')
                        <button
                            type="submit"
                            class="tw:rounded-md tw:border tw:border-transparent tw:bg-red-600 tw:px-4 tw:py-2 tw:font-medium tw:text-white! tw:hover:bg-red-700 tw:transition-colors"
                        >
                            <i class="fa fa-trash"></i>
                            {{ __('Delete') }}
                        </button>
                    </form>
                </x-slot:footer>
            </x-panel>
            <div


            >

                <div class="tw:px-6 tw:py-4 tw:break-all tw:sm:break-normal tw:text-gray-600 tw:dark:text-gray-400">

                </div>

                <div class="tw:flex tw:justify-end tw:gap-3 tw:border-t tw:border-gray-100 tw:dark:border-gray-700 tw:px-6 tw:py-4">

                </div>
            </div>
        </div>
        </div>
    </x-panel>
</div>
@endsection

@push('scripts')
    <script>
        init_select2('#device', 'device', {}, null, '{{ __('device.please_select') }}');

        document.addEventListener('alpine:init', () => {
            Alpine.data('deviceDeleteButton', () => ({
                modalOpen: false,
                selected: null,
                name: '',
                urlTemplate: '{{ route('device.destroy', ':device_id') }}',
                actionUrl: '',

                init() {
                    $(this.$refs.deviceSelect).on('select2:select', (e) => {
                        const data = e.params.data;
                        this.selected = data.id;
                        this.name = data.text;
                        this.actionUrl = this.urlTemplate.replace(':device_id', data.id);
                    });
                    $(this.$refs.deviceSelect).on('select2:clear', () => {
                        this.selected = null;
                        this.name = '';
                        this.actionUrl = '';
                    })
                }
            }))
        })
    </script>
@endpush
