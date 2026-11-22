<a href="{{ route('search', ['q' => \LibreNMS\Util\Mac::parse($mac)->readable(), 'view' => 'host']) }}" @class(['tw:font-mono tw:whitespace-nowrap', 'tw:font-semibold tw:text-gray-900 tw:dark:text-dark-white-100' => $primary ?? false])
   x-html="highlightMac(@js($mac))">{{ \LibreNMS\Util\Mac::parse($mac)->readable() }}</a>@if($copy ?? false)@include('search.partials.copy', ['js' => 'formatMac(' . \Illuminate\Support\Js::from($mac) . ')'])@endif
@if(\App\Facades\LibrenmsConfig::get('mac_oui.enabled') && ($vendor = \LibreNMS\Util\Mac::parse($mac)->vendor()))
    <{{ ($inlineVendor ?? false) ? 'span' : 'div' }} @class(['tw:text-sm tw:text-gray-500 tw:dark:text-dark-white-400 tw:truncate', 'tw:inline-block tw:align-bottom tw:max-w-40 tw:ml-1' => $inlineVendor ?? false, 'tw:max-w-56' => ! ($inlineVendor ?? false)]) title="{{ $vendor }}">{{ $vendor }}</{{ ($inlineVendor ?? false) ? 'span' : 'div' }}>
@endif
