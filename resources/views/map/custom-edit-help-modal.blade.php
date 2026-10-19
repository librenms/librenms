<div id="showHelpContainer" x-data="customEditData()">
  <x-modal show="showHelp" maxWidth="sm">
    <x-slot name="heading">
      <h4 class="tw:m-0 tw:text-base tw:font-semibold tw:flex tw:items-center tw:gap-2">
        <i class="fa fa-keyboard-o tw:text-blue-500" aria-hidden="true"></i>
        {{ __('Help & Shortcuts') }}
      </h4>
    </x-slot name="heading">
    <div>
      <h5 class="tw:font-semibold tw:uppercase tw:tracking-wider tw:text-gray-500 tw:dark:text-dark-white-400 tw:mb-2">
        {{ __('Map Control') }}
      </h5>
      <div class="tw:grid tw:grid-cols-1 tw:gap-2">
         <div class="tw:flex tw:items-center tw:justify-between tw:p-2 tw:rounded-md tw:bg-gray-50 tw:dark:bg-dark-gray-400">
          <span class="tw:text-gray-600 tw:dark:text-dark-white-300">{{ __('Add Node') }}</span>
          <div class="tw:flex tw:items-center tw:gap-1.5">
            <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">N</kbd>
          </div>
         </div>
         <div class="tw:flex tw:items-center tw:justify-between tw:p-2 tw:rounded-md tw:bg-gray-50 tw:dark:bg-dark-gray-400">
          <span class="tw:text-gray-600 tw:dark:text-dark-white-300">{{ __('Add Edge') }}</span>
          <div class="tw:flex tw:items-center tw:gap-1.5">
            <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">M</kbd>
          </div>
         </div>
         <div class="tw:flex tw:items-center tw:justify-between tw:p-2 tw:rounded-md tw:bg-gray-50 tw:dark:bg-dark-gray-400">
          <span class="tw:text-gray-600 tw:dark:text-dark-white-300">{{ __('Rerender Map') }}</span>
          <div class="tw:flex tw:items-center tw:gap-1.5">
            <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">R</kbd>
          </div>
         </div>
         <div class="tw:flex tw:items-center tw:justify-between tw:p-2 tw:rounded-md tw:bg-gray-50 tw:dark:bg-dark-gray-400">
          <span class="tw:text-gray-600 tw:dark:text-dark-white-300">{{ __('Save Map') }}</span>
          <div class="tw:flex tw:items-center tw:gap-1.5">
            <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">S</kbd>
          </div>
         </div>
       </div>
     </div>
    <div>
      <h5 class="tw:font-semibold tw:uppercase tw:tracking-wider tw:text-gray-500 tw:dark:text-dark-white-400 tw:mb-2">
        {{ __('Selected Items') }}
      </h5>
      <div class="tw:grid tw:grid-cols-1 tw:gap-2">
        <div class="tw:flex tw:items-center tw:justify-between tw:p-2 tw:rounded-md tw:bg-gray-50 tw:dark:bg-dark-gray-400">
          <span class="tw:text-gray-600 tw:dark:text-dark-white-300">{{ __('Move Selected Items') }}</span>
          <div class="tw:flex tw:items-center tw:gap-1.5">
            <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">↑</kbd>
            <span class="tw:text-gray-400 tw:dark:text-dark-white-400">/</span>
            <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">↓</kbd>
            <span class="tw:text-gray-400 tw:dark:text-dark-white-400">/</span>
            <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">←</kbd>
            <span class="tw:text-gray-400 tw:dark:text-dark-white-400">/</span>
            <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">→</kbd>
          </div>
         </div>
         <div class="tw:flex tw:items-center tw:justify-between tw:p-2 tw:rounded-md tw:bg-gray-50 tw:dark:bg-dark-gray-400">
          <span class="tw:text-gray-600 tw:dark:text-dark-white-300">{{ __('Recenter Selected Items') }}</span>
          <div class="tw:flex tw:items-center tw:gap-1.5">
            <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">C</kbd>
          </div>
         </div>
         <div class="tw:flex tw:items-center tw:justify-between tw:p-2 tw:rounded-md tw:bg-gray-50 tw:dark:bg-dark-gray-400">
          <span class="tw:text-gray-600 tw:dark:text-dark-white-300">{{ __('Edit Selected Item') }}</span>
          <div class="tw:flex tw:items-center tw:gap-1.5">
            <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">E</kbd>
          </div>
         </div>
       </div>
     </div>
    <div>
      <h5 class="tw:font-semibold tw:uppercase tw:tracking-wider tw:text-gray-500 tw:dark:text-dark-white-400 tw:mb-2">
        {{ __('General') }}
      </h5>
      <div class="tw:grid tw:grid-cols-1 tw:gap-2">
        <div class="tw:flex tw:items-center tw:justify-between tw:p-2 tw:rounded-md tw:bg-gray-50 tw:dark:bg-dark-gray-400">
          <span class="tw:text-gray-600 tw:dark:text-dark-white-300">{{ __('Multi-select') }}</span>
          <div class="tw:flex tw:items-center tw:gap-0.5">
            <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">Ctrl</kbd>
            <span class="tw:text-gray-400 tw:dark:text-dark-white-400">+</span>
            <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">Click</kbd>
          </div>
        </div>
        <div class="tw:flex tw:items-center tw:justify-between tw:p-2 tw:rounded-md tw:bg-gray-50 tw:dark:bg-dark-gray-400">
          <span class="tw:text-gray-600 tw:dark:text-dark-white-300">{{ __('Open this help') }}</span>
          <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">?</kbd>
        </div>
        <div class="tw:flex tw:items-center tw:justify-between tw:p-2 tw:rounded-md tw:bg-gray-50 tw:dark:bg-dark-gray-400">
          <span class="tw:text-gray-600 tw:dark:text-dark-white-300">{{ __('Close help or other menu') }}</span>
          <kbd class="tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs">Esc</kbd>
        </div>
      </div>
    </div>
  </x-modal>
</div>
