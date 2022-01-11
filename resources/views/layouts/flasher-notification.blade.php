<?php
$title = $envelope->getTitle();
switch ($envelope->getType()) {
    case 'success':
        $color = 'tw-text-green-600';
        $class = 'flasher-success';
        break;
    case 'error':
        $color = 'tw-text-red-600';
        $class = 'flasher-error';
        break;
    case 'warning':
        $color = 'tw-text-yellow-600';
        $class = 'flasher-warning';
        break;
    case 'info':
    default:
        $color = 'tw-text-blue-600';
        $class = 'flasher-info';
        break;
}
?>
<div class="{{ $class }} {{ $color }} tw-border-current tw-flex tw-flex-col tw-justify-between tw-bg-white dark:tw-bg-dark-gray-300 tw-opacity-80 hover:tw-opacity-100 tw-rounded-md tw-shadow-lg hover:tw-shadow-xl tw-border-l-8 tw-border-t-0.5 tw-border-r-0.5 tw-border-b-0.5 tw-mt-2 tw-cursor-pointer">
    <div class="tw-pl-20 tw-py-4 tw-pr-2 tw-overflow-hidden">
        @if($title)
            <div class="tw-text-xl tw-leading-7 tw-font-semibold tw-capitalize">
                {{ $title }}
            </div>
        @endif
        <div class="tw-mt-1 tw-text-base tw-leading-5 tw-text-gray-500 dark:tw-text-white">
            {!! clean(stripslashes($envelope->getMessage()), 'notifications') !!}
        </div>
    </div>
    <div class="tw-h-1 tw-flex tw-mr-0.5">
        <span class="flasher-progress tw-bg-current"></span>
    </div>
</div>
