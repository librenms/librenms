@php($parsedMac = \LibreNMS\Util\Mac::parse($mac))
<a href="{{ route('search', ['q' => $parsedMac->readable(), 'view' => 'host']) }}" @class(['tw:font-mono tw:whitespace-nowrap', 'tw:font-semibold tw:text-gray-900 tw:dark:text-dark-white-100' => $primary ?? false])
   x-html="highlightMac(@js($parsedMac->hex()))">{{ $parsedMac->readable() }}</a>@if($copy ?? false)@include('search.partials.copy', ['js' => 'formatMac(' . \Illuminate\Support\Js::from($parsedMac->hex()) . ')'])@endif
@if(\App\Facades\LibrenmsConfig::get('mac_oui.enabled') && ($vendor = $parsedMac->vendor()))
    <{{ ($inlineVendor ?? false) ? 'span' : 'div' }} @class(['tw:text-sm tw:text-gray-500 tw:dark:text-dark-white-400 tw:truncate', 'tw:inline-block tw:align-bottom tw:max-w-40 tw:ml-1' => $inlineVendor ?? false, 'tw:max-w-56' => ! ($inlineVendor ?? false)]) title="{{ $vendor }}">{{ $vendor }}</{{ ($inlineVendor ?? false) ? 'span' : 'div' }}>
@endif
