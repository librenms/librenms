@php($parsedMac = \LibreNMS\Util\Mac::parse($mac))
<a href="{{ route('search', ['q' => $parsedMac->readable()]) }}" class="tw:font-mono tw:whitespace-nowrap">{{ $parsedMac->readable() }}</a>
@if(\App\Facades\LibrenmsConfig::get('mac_oui.enabled') && ($vendor = $parsedMac->vendor()))
    <div class="tw:text-xs tw:text-gray-500 tw:dark:text-dark-white-400 tw:truncate tw:max-w-56" title="{{ $vendor }}">{{ $vendor }}</div>
@endif
