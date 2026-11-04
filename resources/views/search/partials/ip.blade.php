@php($ipText = \LibreNMS\Util\IP::parse($ip, true)?->compressed() ?? $ip)
<a href="{{ route('search', ['q' => $ipText, 'view' => 'host']) }}" @class(['tw:font-mono', 'tw:whitespace-nowrap' => ! ($wrap ?? false), 'tw:font-semibold tw:text-gray-900 tw:dark:text-dark-white-100' => $primary ?? false])
   x-html="highlightText(@js($ipText))">{{ $ipText }}</a>
