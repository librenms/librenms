/*
 * boot.js
 *
 * Initialize javascript for LibreNMS v1
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

// set CSRF for jquery ajax request
$.ajaxSetup({
    headers:
        { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

// toastr style to match php toasts
toastr.options = {
    toastClass: 'tw-border-current tw-relative tw-pl-20 tw-py-4 tw-pr-2 tw-bg-white dark:tw-bg-dark-gray-300 tw-opacity-80 hover:tw-opacity-100 tw-rounded-md tw-shadow-lg hover:tw-shadow-xl tw-border-l-8 tw-border-t-0.5 tw-border-r-0.5 tw-border-b-0.5 tw-mt-2 tw-cursor-pointer',
    titleClass: 'tw-text-xl tw-leading-7 tw-font-semibold tw-capitalize',
    messageClass: 'tw-mt-1 tw-text-base tw-leading-5 tw-text-gray-500 dark:tw-text-white',
    iconClasses: {
        error: 'flasher-error tw-text-red-600 tw-border-red-600',
        info: 'flasher-info tw-text-blue-600 tw-border-blue-600',
        success: 'flasher-success tw-text-green-600 tw-border-green-600',
        warning: 'flasher-warning tw-text-yellow-600 tw-border-yellow-600'
    },
    timeOut: 12000,
    progressBar: true,
    progressClass: 'toast-progress tw-h-1 tw-bg-current tw-absolute tw-bottom-0 tw-left-0 tw-mr-0.5',
    containerId: 'flasher-container-top-right'
};
