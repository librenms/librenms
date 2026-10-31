@php($ipText = (\LibreNMS\Util\IP::parse($ip, true)?->compressed() ?? $ip) . (isset($prefix) ? "/$prefix" : ''))
<a href="{{ route('search', ['q' => \LibreNMS\Util\IP::parse($ip, true)?->compressed() ?? $ip]) }}" @class(['tw:font-mono tw:whitespace-nowrap', 'tw:font-semibold tw:text-gray-900 tw:dark:text-dark-white-100' => $primary ?? false])
   x-html="highlightText(@js($ipText))">{{ $ipText }}</a>
