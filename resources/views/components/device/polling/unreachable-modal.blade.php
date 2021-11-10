@props([
    'actionClick' => 'saveAnyway()',
    'actionLabel' => null,
    'actionIcon' => 'fa-save',
])

@php
    $actionLabel = $actionLabel ?? __('Save Anyway');
@endphp

<template x-teleport="body">
    <div x-show="unreachableDialog" x-cloak style="display: none;"
         class="tw:fixed tw:inset-0 tw:z-100 tw:flex tw:items-center tw:justify-center tw:p-4 tw:bg-black/60 tw:backdrop-blur-xs"
         @click="unreachableDialog = false"
         @keydown.escape.window="unreachableDialog = false">
        <div x-show="unreachableDialog"
             x-transition:enter="tw:ease-out tw:duration-300"
             x-transition:enter-start="tw:opacity-0 tw:scale-95"
             x-transition:enter-end="tw:opacity-100 tw:scale-100"
             x-transition:leave="tw:ease-in tw:duration-200"
             x-transition:leave-start="tw:opacity-100 tw:scale-100"
             x-transition:leave-end="tw:opacity-0 tw:scale-95"
             @click.stop
             class="tw:w-full tw:max-w-lg tw:bg-white tw:dark:bg-dark-gray-500 tw:border tw:border-gray-200 tw:dark:border-dark-gray-300 tw:rounded-xl tw:shadow-2xl tw:p-6"
             role="dialog" aria-modal="true" aria-labelledby="modal-title">

            <div class="tw:flex tw:items-start tw:gap-4">
                <div class="tw:shrink-0 tw:flex tw:items-center tw:justify-center tw:h-12 tw:w-12 tw:rounded-full tw:bg-amber-100 tw:dark:bg-amber-900/50">
                    <i class="fa fa-exclamation-triangle tw:text-amber-600 tw:dark:text-amber-400 tw:text-xl"></i>
                </div>
                <div class="tw:grow">
                    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-900 tw:dark:text-dark-white-100 tw:m-0" id="modal-title">
                        {{ __('poller.reachability_check_failed') }}
                    </h3>
                    <div class="tw:mt-2">
                        <p class="tw:text-sm tw:text-gray-600 tw:dark:text-dark-white-300" x-text="unreachableMessage"></p>
                        <template x-if="unreachableDetails">
                            <div class="tw:mt-3 tw:p-3 tw:bg-gray-100 tw:dark:bg-dark-gray-600 tw:rounded tw:text-xs tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-200 tw:overflow-x-auto tw:max-h-40" x-text="unreachableDetails"></div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="tw:mt-6 tw:flex tw:flex-col-reverse tw:sm:flex-row tw:justify-end tw:gap-3">
                <button type="button" @click="unreachableDialog = false" class="btn btn-default">
                    {{ __('Edit Settings') }}
                </button>
                <button type="button" @click="{{ $actionClick }}" class="btn btn-warning">
                    <i class="fa {{ $actionIcon }} tw:mr-1"></i> {{ $actionLabel }}
                </button>
            </div>
        </div>
    </div>
</template>
