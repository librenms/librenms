@extends('layouts.librenmsv1')

@section('title', __('device.delete_device'))

@section('content')
    <div class="tw:max-w-4xl tw:mx-auto tw:py-10 tw:px-4">
        <x-panel class="tw:shadow-md">
            <x-slot:heading class="tw:flex tw:items-center tw:bg-red-50 tw:dark:bg-red-900/30!">
                <i class="fa fa-warning fa-3x tw:text-red-800 tw:pr-2 tw:dark:text-dark-white-100"></i>
                <span class="tw:text-2xl tw:font-semibold tw:text-red-800 tw:dark:text-dark-white-100!">
                    {{ __('device.delete', ['name' => $device->displayName()]) }}
                </span>
            </x-slot:heading>

            <div class="tw:rounded-md tw:bg-red-50 tw:dark:bg-red-900/30 tw:border tw:border-red-100 tw:dark:border-red-800 tw:px-4 tw:py-3 tw:text-red-700 tw:dark:text-red-300 tw:space-y-1">
                <p class="tw:font-semibold">{{ __('device.warning_monitored') }}</p>
                <p>
                    {{ __('device.warning_data') }}
                    <ul>
                        @foreach($data_warn as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </p>
            </div>
            <p class="tw:pt-3 tw:text-gray-700 tw:dark:text-gray-300">
                {{ __('device.confirm_delete', ['name' => $device->hostname]) }}
            </p>

            <x-slot:footer class="tw:flex tw:justify-end tw:gap-3 tw:bg-transparent">
                <a href="{{ url()->previous() }}"
                   class="tw:px-4 tw:py-2 tw:font-medium tw:rounded-md tw:no-underline tw:border tw:border-gray-300 tw:dark:border-gray-600 tw:bg-white tw:dark:bg-dark-gray-300 tw:text-gray-700 tw:dark:text-gray-300 tw:hover:bg-gray-50 tw:dark:hover:bg-gray-600 tw:transition-colors"
                >
                    {{ __('Cancel') }}
                </a>

                <form action="{{ route('device.destroy', $device) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button
                        type="submit"
                        class="tw:px-4 tw:py-2 tw:font-medium tw:rounded-md tw:border tw:border-transparent tw:bg-red-600 tw:text-white! tw:hover:bg-red-700 tw:transition-colors"
                    >
                        <i class="fa fa-trash"></i>
                        {{ __('Delete') }}
                    </button>
                </form>
            </x-slot:footer>
        </x-panel>
    </div>
@endsection
